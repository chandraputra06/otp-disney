@extends('layouts.app')

@section('content')
    <main class="min-h-screen bg-[linear-gradient(180deg,_#fff7f7_0%,_#f8f4f4_100%)] px-4 py-10">
        <div class="mx-auto max-w-5xl">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 inline-flex items-center gap-2 rounded-full border border-[#7b1e1e]/15 bg-white px-4 py-2 text-xs font-medium text-[#7b1e1e] shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-[#7b1e1e]"></span>
                    Disney OTP Fetcher
                </div>

                <h1 class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                    Cek OTP Disney Orinimo
                </h1>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-6 text-gray-500 md:text-base">
                    Masukkan nomor handphone untuk mengambil OTP terbaru secara otomatis.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                <section class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-[0_20px_60px_rgba(0,0,0,0.08)] backdrop-blur md:p-8">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">Pencarian OTP</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Gunakan nomor yang sudah terdaftar pada sistem.
                        </p>
                    </div>

                    <form id="otp-fetch-form" action="{{ route('otp.fetch') }}" method="POST" class="space-y-5">
                        @csrf

                        <div>
                            <label for="phone_number" class="mb-2 block text-sm font-medium text-gray-700">
                                Nomor Handphone
                            </label>

                            <div class="relative">
                                <input
                                    type="text"
                                    id="phone_number"
                                    name="phone_number"
                                    value="{{ old('phone_number', $result['phone_number'] ?? '') }}"
                                    placeholder="Contoh: 0895637875901"
                                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3.5 text-sm text-gray-900 outline-none ring-0 transition placeholder:text-gray-400 focus:border-[#7b1e1e] focus:shadow-[0_0_0_4px_rgba(123,30,30,0.10)]"
                                >
                            </div>

                            @error('phone_number')
                                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <button
                                id="submit-button"
                                type="submit"
                                class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-[#7b1e1e] px-4 py-3.5 text-sm font-semibold text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-[#651818] hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-70"
                            >
                                <svg id="submit-spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="4"></circle>
                                    <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                                </svg>
                                <span id="submit-button-text">Ambil OTP</span>
                            </button>

                            <a
                                href="{{ route('otp.index') }}"
                                class="inline-flex w-full cursor-pointer items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3.5 text-sm font-semibold text-gray-700 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#7b1e1e]/20 hover:bg-gray-50 hover:text-[#7b1e1e] hover:shadow-md"
                            >
                                Reset
                            </a>
                        </div>
                    </form>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-[#7b1e1e]/10 bg-[#7b1e1e]/[0.03] p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Fast Check</p>
                            <p class="mt-2 text-sm font-semibold text-gray-900">Refresh cepat untuk OTP terbaru</p>
                        </div>

                        <div class="rounded-2xl border border-[#7b1e1e]/10 bg-[#7b1e1e]/[0.03] p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Auto Refresh</p>
                            <p class="mt-2 text-sm font-semibold text-gray-900">Update otomatis tiap 10 detik</p>
                        </div>

                        <div class="rounded-2xl border border-[#7b1e1e]/10 bg-[#7b1e1e]/[0.03] p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Secure Flow</p>
                            <p class="mt-2 text-sm font-semibold text-gray-900">Input hanya berdasarkan nomor HP</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-[0_20px_60px_rgba(0,0,0,0.08)] backdrop-blur md:p-8">
                    <div class="mb-5 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Hasil OTP</h2>
                            <p id="auto-refresh-status" class="mt-1 text-sm text-gray-500">
                                @isset($result)
                                    Auto refresh aktif
                                @else
                                    Belum ada pencarian OTP
                                @endisset
                            </p>
                        </div>

                        @isset($result)
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-semibold text-gray-600">Auto Refresh</span>

                                    <button
                                        type="button"
                                        id="toggle-auto-refresh"
                                        aria-pressed="true"
                                        class="relative inline-flex h-10 w-[90px] cursor-pointer items-center rounded-full px-1 shadow-sm transition-all duration-300"
                                        style="background-color: #7b1e1e;"
                                    >
                                        <span
                                            id="toggle-on-text"
                                            class="absolute left-4 z-10 text-[11px] font-bold tracking-wide text-white transition-all duration-300"
                                        >
                                            ON
                                        </span>

                                        <span
                                            id="toggle-off-text"
                                            class="absolute right-3 z-10 text-[11px] font-bold tracking-wide text-white/70 transition-all duration-300"
                                        >
                                            OFF
                                        </span>

                                        <span
                                            id="toggle-knob"
                                            class="absolute left-1 top-1 z-20 h-8 w-8 rounded-full bg-white shadow-md transition-all duration-300"
                                        ></span>
                                    </button>
                                </div>

                                <form id="manual-refresh-form" action="{{ route('otp.fetch') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="phone_number" value="{{ $result['phone_number'] ?? '' }}">
                                    <button
                                        type="submit"
                                        class="group inline-flex cursor-pointer items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#7b1e1e]/20 hover:bg-[#7b1e1e] hover:text-white hover:shadow-md"
                                    >
                                        <i class="fa-solid fa-rotate-right text-[13px] transition duration-300 group-hover:rotate-180"></i>
                                        Refresh
                                    </button>
                                </form>
                            </div>
                        @endisset
                    </div>

                    @isset($result)
                        <div class="mb-5 flex items-center justify-between gap-3 rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Nomor HP</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">{{ $result['phone_number'] ?? '-' }}</p>
                            </div>

                            <span class="rounded-full px-3 py-1.5 text-xs font-semibold
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

                        <div class="rounded-[28px] border border-[#7b1e1e]/10 bg-white p-7 text-center shadow-[0_12px_40px_rgba(123,30,30,0.08)]">
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-gray-400">Kode OTP</p>
                            <p class="mt-4 text-5xl font-bold tracking-[0.28em] text-[#7b1e1e] md:text-6xl">
                                {{ $result['otp_code'] ?? '—' }}
                            </p>
                        </div>

                        <div class="mt-5 space-y-3 rounded-2xl border border-gray-100 bg-white p-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Status Message</p>
                                <p class="mt-1 text-sm text-gray-800">{{ $result['message'] ?? '-' }}</p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Waktu Diterima</p>
                                <p class="mt-1 text-sm text-gray-800">{{ $result['received_at'] ?? '-' }}</p>
                            </div>
                        </div>

                        @if (!empty($result['last_otp_code']))
                            <div class="mt-5 rounded-2xl border border-blue-200 bg-blue-50 p-4">
                                <p class="text-sm font-semibold text-blue-900">OTP terakhir tersimpan</p>
                                <p class="mt-2 text-3xl font-bold tracking-[0.2em] text-blue-900">
                                    {{ $result['last_otp_code'] }}
                                </p>
                                <p class="mt-2 text-sm text-blue-700">
                                    Waktu: {{ $result['last_received_at'] ?? '-' }}
                                </p>
                            </div>
                        @endif

                        <form
                            id="auto-refresh-form"
                            action="{{ route('otp.fetch') }}"
                            method="POST"
                            class="hidden"
                        >
                            @csrf
                            <input type="hidden" name="phone_number" value="{{ $result['phone_number'] ?? '' }}">
                        </form>
                    @else
                        <div class="flex min-h-[320px] flex-col items-center justify-center rounded-3xl border border-dashed border-[#7b1e1e]/15 bg-white px-6 text-center shadow-[0_12px_30px_rgba(0,0,0,0.03)]">
                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#7b1e1e]/10 text-[#7b1e1e]">
                                <svg viewBox="0 0 24 24" class="h-8 w-8 fill-none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5L12 4l9 6.5v9A1.5 1.5 0 0 1 19.5 21h-15A1.5 1.5 0 0 1 3 19.5v-9Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12.5h8M8 16h5"/>
                                </svg>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900">Belum ada hasil OTP</h3>
                            <p class="mt-2 max-w-sm text-sm leading-6 text-gray-500">
                                Masukkan nomor handphone terlebih dahulu untuk mulai mengambil OTP terbaru.
                            </p>
                        </div>
                    @endisset
                </section>
            </div>
        </div>
    </main>

    <script>
        (() => {
            const mainForm = document.getElementById('otp-fetch-form');
            const submitButton = document.getElementById('submit-button');
            const submitButtonText = document.getElementById('submit-button-text');
            const submitSpinner = document.getElementById('submit-spinner');
            const manualRefreshForm = document.getElementById('manual-refresh-form');

            const setLoadingState = (isLoading = true) => {
                if (!submitButton || !submitButtonText || !submitSpinner) return;

                submitButton.disabled = isLoading;
                submitSpinner.classList.toggle('hidden', !isLoading);
                submitButtonText.textContent = isLoading ? 'Memproses...' : 'Ambil OTP';
            };

            if (mainForm) {
                mainForm.addEventListener('submit', () => {
                    setLoadingState(true);
                });
            }

            if (manualRefreshForm) {
                manualRefreshForm.addEventListener('submit', () => {
                    setLoadingState(true);
                });
            }
        })();
    </script>

    @isset($result)
        @if (!empty($result['phone_number']))
            <script>
                (() => {
                    const form = document.getElementById('auto-refresh-form');
                    const statusText = document.getElementById('auto-refresh-status');
                    const toggleButton = document.getElementById('toggle-auto-refresh');
                    const toggleKnob = document.getElementById('toggle-knob');
                    const toggleOnText = document.getElementById('toggle-on-text');
                    const toggleOffText = document.getElementById('toggle-off-text');

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
                            statusText.textContent = `Auto refresh dalam ${secondsLeft} detik`;
                            toggleButton.setAttribute('aria-pressed', 'true');
                            toggleButton.style.backgroundColor = '#7b1e1e';

                            if (toggleKnob) {
                                toggleKnob.style.transform = 'translateX(0)';
                            }

                            if (toggleOnText) {
                                toggleOnText.style.opacity = '1';
                                toggleOnText.style.color = '#ffffff';
                            }

                            if (toggleOffText) {
                                toggleOffText.style.opacity = '1';
                                toggleOffText.style.color = 'rgba(255,255,255,0.65)';
                            }
                        } else {
                            statusText.textContent = 'Auto refresh dimatikan';
                            toggleButton.setAttribute('aria-pressed', 'false');
                            toggleButton.style.backgroundColor = '#9ca3af';

                            if (toggleKnob) {
                                toggleKnob.style.transform = 'translateX(48px)';
                            }

                            if (toggleOnText) {
                                toggleOnText.style.opacity = '1';
                                toggleOnText.style.color = 'rgba(255,255,255,0.65)';
                            }

                            if (toggleOffText) {
                                toggleOffText.style.opacity = '1';
                                toggleOffText.style.color = '#ffffff';
                            }
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