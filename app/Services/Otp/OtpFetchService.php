<?php

namespace App\Services\Otp;

use App\Models\OtpAccount;
use App\Models\OtpMessage;
use App\Services\Gmail\GmailService;
use Illuminate\Support\Carbon;
use Throwable;

class OtpFetchService
{
    public function __construct(protected OtpExtractorService $otpExtractorService, protected GmailService $gmailService) {}

    protected function getLastSuccessfulOtp(OtpAccount $account): array
    {
        $lastMessage = $account->messages()->whereNotNull('otp_code')->where('fetched_status', 'success')->latest('received_at')->latest('id')->first();

        return [
            'last_otp_code' => $lastMessage?->otp_code,
            'last_received_at' => $lastMessage?->received_at?->format('d M Y H:i:s'),
        ];
    }

    public function fetchByPhoneNumber(string $phoneNumber): array
    {
        $account = OtpAccount::query()->where('phone_number', $phoneNumber)->where('is_active', true)->first();

        if (!$account) {
            return [
                'status' => 'not_found',
                'status_label' => 'Tidak Ditemukan',
                'phone_number' => $phoneNumber,
                'message' => 'Nomor handphone tidak terdaftar atau tidak aktif.',
                'otp_code' => null,
                'received_at' => null,
                'last_otp_code' => null,
                'last_received_at' => null,
            ];
        }

        if ($account->last_checked_at && $account->last_checked_at->diffInSeconds(now()) < 5) {
            $remaining = 5 - $account->last_checked_at->diffInSeconds(now());
            $fallback = $this->getLastSuccessfulOtp($account);

            return [
                'status' => 'cooldown',
                'status_label' => 'Tunggu Sebentar',
                'phone_number' => $phoneNumber,
                'message' => "Silakan tunggu {$remaining} detik sebelum refresh lagi.",
                'otp_code' => null,
                'received_at' => null,
                'last_otp_code' => $fallback['last_otp_code'],
                'last_received_at' => $fallback['last_received_at'],
            ];
        }

        $account->update([
            'last_checked_at' => now(),
        ]);

        try {
            $message = $this->gmailService->fetchLatestDisneyMessage($account);

            if (!$message) {
                OtpMessage::create([
                    'otp_account_id' => $account->id,
                    'message_id' => null,
                    'sender_email' => null,
                    'subject' => null,
                    'email_snippet' => null,
                    'otp_code' => null,
                    'fetched_status' => 'not_found',
                    'received_at' => null,
                    'raw_payload' => null,
                ]);

                $fallback = $this->getLastSuccessfulOtp($account);

                return [
                    'status' => 'not_found',
                    'status_label' => 'Email Tidak Ditemukan',
                    'phone_number' => $phoneNumber,
                    'message' => 'Email Disney terbaru belum ditemukan.',
                    'otp_code' => null,
                    'received_at' => null,
                    'last_otp_code' => $fallback['last_otp_code'],
                    'last_received_at' => $fallback['last_received_at'],
                ];
            }

            $otpCode = $this->otpExtractorService->extractFromCandidates([$message['subject'] ?? null, $message['snippet'] ?? null, $message['text_body'] ?? null, isset($message['html_body']) ? strip_tags((string) $message['html_body']) : null]);

            $receivedAtCarbon = !empty($message['internal_date']) ? Carbon::createFromTimestampMs((int) $message['internal_date']) : null;

            OtpMessage::create([
                'otp_account_id' => $account->id,
                'message_id' => $message['message_id'] ?? null,
                'sender_email' => $message['sender_email'] ?? null,
                'subject' => $message['subject'] ?? null,
                'email_snippet' => $message['snippet'] ?? null,
                'otp_code' => $otpCode,
                'fetched_status' => $otpCode ? 'success' : 'parse_failed',
                'received_at' => $receivedAtCarbon,
                'raw_payload' => json_encode($message['payload'] ?? null),
            ]);

            if (!$otpCode) {
                $fallback = $this->getLastSuccessfulOtp($account);

                return [
                    'status' => 'parse_failed',
                    'status_label' => 'OTP Tidak Ditemukan',
                    'phone_number' => $phoneNumber,
                    'message' => 'Email ditemukan, tetapi kode OTP belum berhasil diekstrak.',
                    'otp_code' => null,
                    'received_at' => $receivedAtCarbon?->format('d M Y H:i:s'),
                    'last_otp_code' => $fallback['last_otp_code'],
                    'last_received_at' => $fallback['last_received_at'],
                ];
            }

            return [
                'status' => 'success',
                'status_label' => 'Berhasil',
                'phone_number' => $phoneNumber,
                'message' => 'OTP berhasil ditemukan.',
                'otp_code' => $otpCode,
                'received_at' => $receivedAtCarbon?->format('d M Y H:i:s'),
                'last_otp_code' => null,
                'last_received_at' => null,
            ];
        } catch (Throwable $e) {
            OtpMessage::create([
                'otp_account_id' => $account->id,
                'message_id' => null,
                'sender_email' => null,
                'subject' => null,
                'email_snippet' => null,
                'otp_code' => null,
                'fetched_status' => 'fetch_error',
                'received_at' => null,
                'raw_payload' => null,
            ]);

            $fallback = $this->getLastSuccessfulOtp($account);

            return [
                'status' => 'fetch_error',
                'status_label' => 'Terjadi Kesalahan',
                'phone_number' => $phoneNumber,
                'message' => 'Gagal mengambil email OTP.',
                'otp_code' => null,
                'received_at' => null,
                'last_otp_code' => $fallback['last_otp_code'],
                'last_received_at' => $fallback['last_received_at'],
            ];
        }
    }
}
