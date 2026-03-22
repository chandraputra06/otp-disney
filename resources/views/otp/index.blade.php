@extends('layouts.app')

@section('content')
    <main class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-xl rounded-2xl bg-white p-8 shadow-sm">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-900">Disney OTP Fetcher</h1>
                <p class="mt-2 text-sm text-gray-500">
                    Masukkan nomor handphone untuk mengambil OTP terbaru.
                </p>
            </div>

            <form action="{{ route('otp.fetch') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="phone_number" class="mb-2 block text-sm font-medium text-gray-700">
                        Nomor Handphone
                    </label>
                    <input
                        type="text"
                        id="phone_number"
                        name="phone_number"
                        value="{{ old('phone_number', $result['phone_number'] ?? '') }}"
                        placeholder="Contoh: 0895637875901"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm outline-none transition focus:border-gray-900"
                    >

                    @error('phone_number')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-gray-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-gray-800"
                    >
                        Ambil OTP
                    </button>

                    <a
                        href="{{ route('otp.index') }}"
                        class="inline-flex w-full items-center justify-center rounded-xl border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        Reset
                    </a>
                </div>
            </form>

            @isset($result)
                <div class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Hasil Pengecekan</h2>
                            <p id="auto-refresh-status" class="mt-1 text-xs text-gray-500">
                                Auto refresh dalam 10 detik
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                id="toggle-auto-refresh"
                                class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-white"
                            >
                                Auto Refresh: ON
                            </button>

                            <form action="{{ route('otp.fetch') }}" method="POST">
                                @csrf
                                <input type="hidden" name="phone_number" value="{{ $result['phone_number'] ?? '' }}">
                                <button
                                    type="submit"
                                    class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-white"
                                >
                                    Refresh
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm text-gray-500">Nomor HP</p>
                                <p class="font-medium text-gray-900">{{ $result['phone_number'] ?? '-' }}</p>
                            </div>

                            <span class="rounded-full px-3 py-1 text-xs font-semibold
                                @if (($result['status'] ?? '') === 'success')
                                    bg-green-100 text-green-700
                                @elseif (($result['status'] ?? '') === 'not_found')
                                    bg-yellow-100 text-yellow-700
                                @elseif (($result['status'] ?? '') === 'parse_failed')
                                    bg-orange-100 text-orange-700
                                @elseif (($result['status'] ?? '') === 'cooldown')
                                    bg-blue-100 text-blue-700
                                @else
                                    bg-red-100 text-red-700
                                @endif
                            ">
                                {{ $result['status_label'] ?? '-' }}
                            </span>
                        </div>

                        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-center">
                            <p class="text-sm text-gray-500">Kode OTP</p>
                            <p class="mt-2 text-4xl font-bold tracking-widest text-gray-900">
                                {{ $result['otp_code'] ?? '—' }}
                            </p>
                        </div>

                        <div class="mt-4 space-y-2 text-sm text-gray-700">
                            <p>
                                <span class="font-medium">Pesan:</span>
                                {{ $result['message'] ?? '-' }}
                            </p>

                            <p>
                                <span class="font-medium">Waktu diterima:</span>
                                {{ $result['received_at'] ?? '-' }}
                            </p>
                        </div>

                        @if (!empty($result['last_otp_code']))
                            <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4">
                                <p class="text-sm font-medium text-blue-900">OTP terakhir tersimpan</p>
                                <p class="mt-2 text-2xl font-bold tracking-widest text-blue-900">
                                    {{ $result['last_otp_code'] }}
                                </p>
                                <p class="mt-1 text-sm text-blue-700">
                                    Waktu: {{ $result['last_received_at'] ?? '-' }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <form
                    id="auto-refresh-form"
                    action="{{ route('otp.fetch') }}"
                    method="POST"
                    class="hidden"
                >
                    @csrf
                    <input type="hidden" name="phone_number" value="{{ $result['phone_number'] ?? '' }}">
                </form>
            @endisset
        </div>
    </main>

    @isset($result)
        @if (!empty($result['phone_number']))
            <script>
                (() => {
                    const form = document.getElementById('auto-refresh-form');
                    const statusText = document.getElementById('auto-refresh-status');
                    const toggleButton = document.getElementById('toggle-auto-refresh');

                    if (!form || !statusText || !toggleButton) {
                        return;
                    }

                    const STORAGE_KEY = 'otp_auto_refresh_enabled';
                    let secondsLeft = 10;
                    let autoRefreshEnabled = localStorage.getItem(STORAGE_KEY);

                    if (autoRefreshEnabled === null) {
                        autoRefreshEnabled = true;
                        localStorage.setItem(STORAGE_KEY, 'true');
                    } else {
                        autoRefreshEnabled = autoRefreshEnabled === 'true';
                    }

                    const updateUI = () => {
                        if (autoRefreshEnabled) {
                            toggleButton.textContent = 'Auto Refresh: ON';
                            statusText.textContent = `Auto refresh dalam ${secondsLeft} detik`;
                        } else {
                            toggleButton.textContent = 'Auto Refresh: OFF';
                            statusText.textContent = 'Auto refresh dimatikan';
                        }
                    };

                    toggleButton.addEventListener('click', () => {
                        autoRefreshEnabled = !autoRefreshEnabled;
                        localStorage.setItem(STORAGE_KEY, autoRefreshEnabled ? 'true' : 'false');

                        if (autoRefreshEnabled) {
                            secondsLeft = 10;
                        }

                        updateUI();
                    });

                    updateUI();

                    const interval = setInterval(() => {
                        if (!autoRefreshEnabled) {
                            return;
                        }

                        secondsLeft -= 1;
                        updateUI();

                        if (secondsLeft <= 0) {
                            clearInterval(interval);
                            form.submit();
                        }
                    }, 1000);
                })();
            </script>
        @endif
    @endisset
@endsection