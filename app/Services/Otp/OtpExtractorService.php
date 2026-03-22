<?php

namespace App\Services\Otp;

class OtpExtractorService
{
    public function extractFromCandidates(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $otp = $this->extractFromText($candidate);

            if ($otp) {
                return $otp;
            }
        }

        return null;
    }

    public function extractFromText(?string $text): ?string
    {
        if (blank($text)) {
            return null;
        }

        $normalizedText = trim((string) $text);

        preg_match('/\b\d{4,6}\b/', $normalizedText, $matches);

        return $matches[0] ?? null;
    }
}