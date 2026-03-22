<?php

namespace App\Services\Gmail;

use App\Models\GmailToken;
use App\Models\OtpAccount;
use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Illuminate\Support\Carbon;
use RuntimeException;

class GmailService
{
    protected function makeClient(): GoogleClient
    {
        $client = new GoogleClient();

        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([
            Gmail::GMAIL_READONLY,
        ]);

        return $client;
    }

    public function getAuthUrl(): string
    {
        return $this->makeClient()->createAuthUrl();
    }

    public function storeTokenFromAuthCode(OtpAccount $account, string $authCode): void
    {
        $client = $this->makeClient();
        $token = $client->fetchAccessTokenWithAuthCode($authCode);

        if (isset($token['error'])) {
            throw new RuntimeException('Gagal mengambil access token Google.');
        }

        GmailToken::updateOrCreate(
            ['otp_account_id' => $account->id],
            [
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'expires_at' => isset($token['expires_in'])
                    ? Carbon::now()->addSeconds((int) $token['expires_in'])
                    : null,
            ]
        );
    }

    protected function extractHeaderValue($payload, string $targetName): ?string
    {
        $headers = $payload?->getHeaders() ?? [];

        foreach ($headers as $header) {
            if (strtolower($header->getName()) === strtolower($targetName)) {
                return $header->getValue();
            }
        }

        return null;
    }

    protected function decodeBase64Url(?string $data): ?string
    {
        if (blank($data)) {
            return null;
        }

        $decoded = base64_decode(strtr($data, '-_', '+/'));

        return $decoded === false ? null : $decoded;
    }

    protected function extractBodiesFromPayload($payload): array
    {
        $result = [
            'text_body' => null,
            'html_body' => null,
        ];

        if (! $payload) {
            return $result;
        }

        $mimeType = $payload->getMimeType();
        $bodyData = $payload->getBody()?->getData();

        if ($mimeType === 'text/plain' && $bodyData) {
            $result['text_body'] = $this->decodeBase64Url($bodyData);
        }

        if ($mimeType === 'text/html' && $bodyData) {
            $result['html_body'] = $this->decodeBase64Url($bodyData);
        }

        $parts = $payload->getParts() ?? [];

        foreach ($parts as $part) {
            $partMimeType = $part->getMimeType();
            $partBodyData = $part->getBody()?->getData();

            if ($partMimeType === 'text/plain' && $partBodyData && ! $result['text_body']) {
                $result['text_body'] = $this->decodeBase64Url($partBodyData);
            }

            if ($partMimeType === 'text/html' && $partBodyData && ! $result['html_body']) {
                $result['html_body'] = $this->decodeBase64Url($partBodyData);
            }

            if ($part->getParts()) {
                $nested = $this->extractBodiesFromPayload($part);

                $result['text_body'] ??= $nested['text_body'];
                $result['html_body'] ??= $nested['html_body'];
            }
        }

        return $result;
    }

    public function fetchLatestDisneyMessage(OtpAccount $account): ?array
    {
        $gmailToken = $account->gmailToken;

        if (! $gmailToken) {
            throw new RuntimeException('Token Gmail belum tersedia untuk akun ini.');
        }

        $client = $this->makeClient();

        $client->setAccessToken([
            'access_token' => $gmailToken->access_token,
            'refresh_token' => $gmailToken->refresh_token,
            'expires_in' => $gmailToken->expires_at
                ? now()->diffInSeconds($gmailToken->expires_at, false)
                : 0,
            'created' => now()->timestamp,
        ]);

        if ($client->isAccessTokenExpired()) {
            if (! $gmailToken->refresh_token) {
                throw new RuntimeException('Refresh token tidak tersedia.');
            }

            $newToken = $client->fetchAccessTokenWithRefreshToken($gmailToken->refresh_token);

            if (isset($newToken['error'])) {
                throw new RuntimeException('Gagal me-refresh access token.');
            }

            $gmailToken->update([
                'access_token' => $newToken['access_token'],
                'expires_at' => isset($newToken['expires_in'])
                    ? now()->addSeconds((int) $newToken['expires_in'])
                    : null,
            ]);

            $client->setAccessToken([
                'access_token' => $gmailToken->fresh()->access_token,
                'refresh_token' => $gmailToken->refresh_token,
            ]);
        }

        $service = new Gmail($client);

        $query = 'newer_than:2d (Disney OR "Disney+" OR kode OR verifikasi OR OTP)';

        $messagesResponse = $service->users_messages->listUsersMessages('me', [
            'maxResults' => 10,
            'q' => $query,
        ]);

        $messages = $messagesResponse->getMessages();

        if (empty($messages)) {
            return null;
        }

        foreach ($messages as $messageItem) {
            $message = $service->users_messages->get('me', $messageItem->getId(), [
                'format' => 'full',
            ]);

            $payload = $message->getPayload();
            $subject = $this->extractHeaderValue($payload, 'Subject');
            $senderEmail = $this->extractHeaderValue($payload, 'From');
            $bodies = $this->extractBodiesFromPayload($payload);

            $combinedText = implode("\n", array_filter([
                $subject,
                $message->getSnippet(),
                $bodies['text_body'],
                strip_tags((string) $bodies['html_body']),
            ]));

            if (preg_match('/\b\d{4,6}\b/', $combinedText)) {
                return [
                    'message_id' => $message->getId(),
                    'snippet' => $message->getSnippet(),
                    'subject' => $subject,
                    'sender_email' => $senderEmail,
                    'text_body' => $bodies['text_body'],
                    'html_body' => $bodies['html_body'],
                    'payload' => $message->toSimpleObject(),
                    'internal_date' => $message->getInternalDate(),
                ];
            }
        }

        $message = $service->users_messages->get('me', $messages[0]->getId(), [
            'format' => 'full',
        ]);

        $payload = $message->getPayload();
        $bodies = $this->extractBodiesFromPayload($payload);

        return [
            'message_id' => $message->getId(),
            'snippet' => $message->getSnippet(),
            'subject' => $this->extractHeaderValue($payload, 'Subject'),
            'sender_email' => $this->extractHeaderValue($payload, 'From'),
            'text_body' => $bodies['text_body'],
            'html_body' => $bodies['html_body'],
            'payload' => $message->toSimpleObject(),
            'internal_date' => $message->getInternalDate(),
        ];
    }
}