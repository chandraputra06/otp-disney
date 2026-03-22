<?php

namespace App\Http\Controllers;

use App\Models\OtpAccount;
use App\Services\Gmail\GmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GoogleAuthController extends Controller
{
    public function __construct(
        protected GmailService $gmailService
    ) {
    }

    public function redirect(OtpAccount $account): RedirectResponse
    {
        Session::put('google_oauth_account_id', $account->id);

        return redirect()->away($this->gmailService->getAuthUrl());
    }

    public function callback(Request $request): RedirectResponse
    {
        $accountId = Session::pull('google_oauth_account_id');
        $account = OtpAccount::findOrFail($accountId);

        $this->gmailService->storeTokenFromAuthCode(
            $account,
            $request->string('code')->toString()
        );

        return redirect()
            ->route('otp.index')
            ->with('success', 'Akun Gmail berhasil dihubungkan.');
    }
}