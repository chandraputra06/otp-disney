@extends('layouts.app')

@section('content')
<style>
    #otp-app { min-height: 100vh; background: linear-gradient(180deg,#fff7f7 0%,#f8f4f4 100%); padding: 32px 16px; font-family: ui-sans-serif,system-ui,sans-serif; }
    .otp-container { max-width: 460px; margin: 0 auto; }
    .otp-head { text-align: center; margin-bottom: 22px; }
    .otp-pill { display:inline-flex; align-items:center; gap:6px; background:#fff; color:#7b1e1e; font-size:12px; font-weight:600; padding:6px 12px; border-radius:999px; box-shadow:0 1px 3px rgba(0,0,0,.06); }
    .otp-pill span { width:7px; height:7px; border-radius:50%; background:#7b1e1e; }
    .otp-head h1 { font-size:24px; font-weight:700; color:#1f2937; margin:14px 0 4px; }
    .otp-head p { font-size:13px; color:#6b7280; margin:0; }
    .otp-card { background:#fff; border-radius:20px; padding:18px; box-shadow:0 10px 40px rgba(0,0,0,.06); }
    .otp-label { font-size:12px; color:#9ca3af; margin:0 0 6px; }
    .otp-inrow { display:flex; gap:8px; }
    .otp-input { flex:1; border:1px solid #e5e7eb; border-radius:12px; padding:12px 14px; font-size:15px; color:#111827; outline:none; transition:.15s; }
    .otp-input:focus { border-color:#7b1e1e; box-shadow:0 0 0 4px rgba(123,30,30,.1); }
    .otp-btn { background:#7b1e1e; color:#fff; border:none; font-size:14px; font-weight:600; padding:0 18px; border-radius:12px; cursor:pointer; white-space:nowrap; transition:.15s; display:inline-flex; align-items:center; gap:8px; }
    .otp-btn:hover { background:#651818; }
    .otp-btn:disabled { opacity:.65; cursor:not-allowed; }
    .otp-err { color:#dc2626; font-size:13px; margin:10px 0 0; display:none; }
    .otp-result { margin-top:16px; }
    .otp-panel { background:#fff; border-radius:20px; padding:18px; box-shadow:0 10px 40px rgba(0,0,0,.06); }
    .otp-toprow { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
    .otp-chip { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; padding:5px 12px; border-radius:999px; }
    .otp-fresh { display:inline-flex; align-items:center; gap:6px; font-size:12px; color:#6b7280; }
    .otp-fresh i { color:#1d9e75; font-size:9px; }
    .otp-codebox { width:100%; background:#fbf4f3; border:1px solid #e7cfca; border-radius:16px; padding:22px 16px; text-align:center; cursor:pointer; transition:transform .1s; }
    .otp-codebox:active { transform:scale(.98); }
    .otp-codebox .cap { font-size:11px; letter-spacing:2px; color:#a98a85; margin:0 0 8px; }
    .otp-codebox .code { font-size:46px; font-weight:700; letter-spacing:8px; color:#7b1e1e; margin:0; font-variant-numeric:tabular-nums; }
    .otp-codebox .hint { display:inline-flex; align-items:center; gap:5px; font-size:12px; color:#7b1e1e; margin-top:10px; }
    .otp-timerow { display:flex; align-items:center; justify-content:space-between; margin-top:14px; padding:11px 13px; background:#f9fafb; border-radius:12px; }
    .otp-timerow .lbl { font-size:12px; color:#6b7280; }
    .otp-timerow .val { font-size:13px; font-weight:600; color:#111827; }
    .otp-msg { font-size:13px; color:#4b5563; margin:12px 2px 0; }
    .otp-refreshrow { display:flex; align-items:center; gap:10px; margin-top:14px; }
    .otp-bar-track { flex:1; height:6px; background:#f1e7e5; border-radius:999px; overflow:hidden; }
    .otp-bar { height:100%; width:100%; background:#7b1e1e; border-radius:999px; transition:width 1s linear; }
    .otp-count { font-size:12px; color:#6b7280; min-width:82px; text-align:right; }
    .otp-toggle { width:36px; height:36px; border-radius:50%; border:1px solid #e5e7eb; background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#374151; }
    .otp-empty { text-align:center; padding:44px 20px; background:#fff; border:1px dashed rgba(123,30,30,.2); border-radius:20px; }
    .otp-empty .ic { width:56px; height:56px; border-radius:16px; background:rgba(123,30,30,.08); color:#7b1e1e; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; font-size:22px; }
    .otp-empty h3 { font-size:16px; font-weight:600; color:#1f2937; margin:0 0 6px; }
    .otp-empty p { font-size:13px; color:#6b7280; margin:0; }
    .otp-toast { position:fixed; left:50%; bottom:28px; transform:translateX(-50%) translateY(10px); background:#111827; color:#fff; font-size:13px; font-weight:600; padding:10px 18px; border-radius:999px; opacity:0; pointer-events:none; transition:.2s; display:inline-flex; align-items:center; gap:7px; }
    .otp-toast.show { opacity:1; transform:translateX(-50%) translateY(0); }
    .spin { animation:otpspin 1s linear infinite; }
    @keyframes otpspin { to { transform:rotate(360deg); } }
</style>

<main id="otp-app">
    <div class="otp-container">
        <div class="otp-head">
            <span class="otp-pill"><span></span>Disney OTP · Orinimo</span>
            <h1>Cek OTP Disney</h1>
            <p>Masukkan nomor handphone untuk mengambil OTP terbaru.</p>
        </div>

        <div class="otp-card">
            <p class="otp-label">Nomor handphone</p>
            <div class="otp-inrow">
                <input id="otp-phone" class="otp-input" type="text" inputmode="numeric"
                       value="{{ $result['phone_number'] ?? '' }}" placeholder="Contoh: 886529367891">
                <button id="otp-submit" class="otp-btn">
                    <i id="otp-submit-ic" class="fa-solid fa-magnifying-glass"></i>
                    <span id="otp-submit-tx">Ambil OTP</span>
                </button>
            </div>
            <p id="otp-error" class="otp-err"></p>
        </div>

        <div id="otp-result" class="otp-result">
            <div class="otp-empty">
                <div class="ic"><i class="fa-solid fa-shield-halved"></i></div>
                <h3>Belum ada hasil OTP</h3>
                <p>Masukkan nomor handphone lalu tekan Ambil OTP.</p>
            </div>
        </div>
    </div>
    <div id="otp-toast" class="otp-toast"><i class="fa-solid fa-check"></i> Kode tersalin</div>
</main>

<script>window.__OTP__ = { url: "{{ route('otp.fetch.json') }}", initial: @json($result ?? null) };</script>
<script>
(() => {
    const cfg = window.__OTP__;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const phoneEl = document.getElementById('otp-phone');
    const submit = document.getElementById('otp-submit');
    const submitIc = document.getElementById('otp-submit-ic');
    const submitTx = document.getElementById('otp-submit-tx');
    const errEl = document.getElementById('otp-error');
    const resultEl = document.getElementById('otp-result');
    const toast = document.getElementById('otp-toast');

    const REFRESH_SECS = 10;
    let timer = null, secs = REFRESH_SECS, running = true, lastPhone = '';

    const chipStyle = (status) => {
        if (status === 'success')  return ['#dcfce7', '#15803d', 'fa-circle-check', 'Berhasil'];
        if (status === 'cooldown') return ['#dbeafe', '#1d4ed8', 'fa-clock', 'Tunggu Sebentar'];
        if (status === 'not_found')return ['#fef9c3', '#a16207', 'fa-envelope', 'Tidak Ditemukan'];
        if (status === 'parse_failed') return ['#ffedd5', '#c2410c', 'fa-triangle-exclamation', 'OTP Tidak Terbaca'];
        return ['#fee2e2', '#b91c1c', 'fa-circle-xmark', 'Terjadi Kesalahan'];
    };

    const showToast = () => { toast.classList.add('show'); setTimeout(() => toast.classList.remove('show'), 1300); };

    const copyCode = (code) => {
        if (!code || code === '—') return;
        const done = () => showToast();
        if (navigator.clipboard) navigator.clipboard.writeText(code).then(done).catch(() => fallbackCopy(code, done));
        else fallbackCopy(code, done);
    };
    const fallbackCopy = (t, cb) => {
        const ta = document.createElement('textarea'); ta.value = t; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta); cb();
    };

    const setLoading = (on) => {
        submit.disabled = on;
        submitIc.className = on ? 'fa-solid fa-spinner spin' : 'fa-solid fa-magnifying-glass';
        submitTx.textContent = on ? 'Memproses…' : 'Ambil OTP';
    };

    const stopTimer = () => { if (timer) { clearInterval(timer); timer = null; } };

    const startTimer = () => {
        stopTimer(); secs = REFRESH_SECS; running = true;
        timer = setInterval(() => {
            if (!running) return;
            secs--;
            const bar = document.getElementById('otp-bar');
            const count = document.getElementById('otp-count');
            if (bar) bar.style.width = (secs / REFRESH_SECS * 100) + '%';
            if (count) count.textContent = 'Segarkan ' + secs + ' dtk';
            if (secs <= 0) { fetchOtp(lastPhone, true); }
        }, 1000);
    };

    const render = (d) => {
        const [bg, fg, icon, label] = chipStyle(d.status);
        const code = d.otp_code || d.last_otp_code || '—';
        const isFallback = !d.otp_code && d.last_otp_code;
        const time = d.received_at || d.last_received_at || null;

        resultEl.innerHTML = `
            <div class="otp-panel">
                <div class="otp-toprow">
                    <span class="otp-chip" style="background:${bg};color:${fg}"><i class="fa-solid ${icon}"></i>${label}</span>
                    ${time ? `<span class="otp-fresh"><i class="fa-solid fa-circle"></i>Diterima ${time} WITA</span>` : ''}
                </div>
                <div class="otp-codebox" id="otp-codebox">
                    <p class="cap">${isFallback ? 'OTP TERAKHIR · KETUK UNTUK SALIN' : 'KODE OTP · KETUK UNTUK SALIN'}</p>
                    <p class="code">${code}</p>
                    <span class="hint"><i class="fa-solid fa-copy"></i>Salin kode</span>
                </div>
                <div class="otp-timerow">
                    <span class="lbl"><i class="fa-solid fa-phone" style="margin-right:6px"></i>${d.phone_number || '-'}</span>
                    <span class="val">${time ? time + ' WITA' : '—'}</span>
                </div>
                ${d.message ? `<p class="otp-msg">${d.message}</p>` : ''}
                <div class="otp-refreshrow">
                    <div class="otp-bar-track"><div id="otp-bar" class="otp-bar"></div></div>
                    <span id="otp-count" class="otp-count">Segarkan ${REFRESH_SECS} dtk</span>
                    <button id="otp-toggle" class="otp-toggle" aria-label="Jeda auto-refresh"><i class="fa-solid fa-pause"></i></button>
                </div>
            </div>`;

        document.getElementById('otp-codebox').addEventListener('click', () => copyCode(code));
        document.getElementById('otp-toggle').addEventListener('click', (e) => {
            running = !running;
            e.currentTarget.querySelector('i').className = 'fa-solid ' + (running ? 'fa-pause' : 'fa-play');
            const count = document.getElementById('otp-count');
            if (!running) count.textContent = 'Dijeda'; else secs = REFRESH_SECS;
        });
    };

    const fetchOtp = async (phone, silent = false) => {
        if (!phone) return;
        lastPhone = phone;
        errEl.style.display = 'none';
        if (!silent) setLoading(true);
        try {
            const res = await fetch(cfg.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ phone_number: phone }),
            });
            if (res.status === 422) {
                const j = await res.json();
                const msg = j.errors?.phone_number?.[0] || j.message || 'Nomor tidak valid.';
                errEl.textContent = msg; errEl.style.display = 'block';
                stopTimer(); return;
            }
            if (res.status === 429) { errEl.textContent = 'Terlalu sering. Tunggu sebentar lalu coba lagi.'; errEl.style.display = 'block'; return; }
            if (!res.ok) throw new Error('http ' + res.status);
            const data = await res.json();
            render(data);
            startTimer();
        } catch (e) {
            errEl.textContent = 'Gagal terhubung ke server. Coba lagi.'; errEl.style.display = 'block';
        } finally {
            if (!silent) setLoading(false);
        }
    };

    submit.addEventListener('click', () => fetchOtp(phoneEl.value.trim()));
    phoneEl.addEventListener('keydown', (e) => { if (e.key === 'Enter') fetchOtp(phoneEl.value.trim()); });

    if (cfg.initial && cfg.initial.phone_number) { render(cfg.initial); lastPhone = cfg.initial.phone_number; startTimer(); }
})();
</script>
@endsection