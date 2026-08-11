<?php

namespace App\Http\Controllers;

use App\Http\Requests\FetchOtpRequest;
use App\Services\Otp\OtpFetchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    public function __construct(
        protected OtpFetchService $otpFetchService
    ) {
    }

    public function index(): View
    {
        return view('otp.index');
    }

    public function fetch(FetchOtpRequest $request): View
    {
        $result = $this->otpFetchService->fetchByPhoneNumber(
            $request->validated('phone_number')
        );

        return view('otp.index', compact('result'));
    }

    // === BARU: kembalikan JSON untuk dikonsumsi AJAX ===
    public function fetchJson(FetchOtpRequest $request): JsonResponse
    {
        $result = $this->otpFetchService->fetchByPhoneNumber(
            $request->validated('phone_number')
        );

        return response()->json($result);
    }
}