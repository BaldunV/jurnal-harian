@extends('layouts.guest')

@section('title', 'Verifikasi OTP')

@section('auth-panel')
<div class="min-h-screen w-full flex items-center justify-center px-4 py-8 sm:px-6 relative overflow-hidden">
    <div class="absolute -top-32 -right-24 w-80 h-80 rounded-full bg-emerald-300/40 dark:bg-emerald-500/10 blur-3xl" aria-hidden="true"></div>
    <div class="absolute -bottom-40 -left-24 w-96 h-96 rounded-full bg-teal-300/40 dark:bg-teal-500/10 blur-3xl" aria-hidden="true"></div>

    <main class="relative w-full max-w-md">
        <div class="overflow-hidden rounded-[2rem] border border-white/80 bg-white/90 p-6 shadow-2xl shadow-emerald-950/10 backdrop-blur-xl dark:border-slate-700 dark:bg-slate-900/90 sm:p-8">
            <div class="mb-7 text-center">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-500 shadow-xl shadow-emerald-500/25">
                    @include('partials.icon', ['name' => 'shield-check', 'class' => 'h-9 w-9 text-white'])
                </div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.16em] text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                    Verifikasi dua langkah
                </div>
                <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Masukkan kode OTP</h1>
                <p class="mx-auto mt-2 max-w-sm text-sm font-medium leading-relaxed text-slate-500 dark:text-slate-400">
                    Kode 6 angka dikirim melalui <strong class="text-emerald-700 dark:text-emerald-300">{{ $channelLabel }}</strong> ke <strong class="text-slate-700 dark:text-slate-200">{{ $maskedPhone }}</strong>.
                </p>
            </div>

            @if(session('otp_status'))
                <div class="mb-4 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                    @include('partials.icon', ['name' => 'circle-check', 'class' => 'h-4 w-4 shrink-0'])
                    <span>{{ session('otp_status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-3.5 text-xs font-semibold text-rose-700 animate-shake-soft dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                    @include('partials.icon', ['name' => 'circle-alert', 'class' => 'h-4 w-4 shrink-0'])
                    <ul class="space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.otp.verify') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="otp-code" class="mb-2 block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">Kode keamanan</label>
                    <input id="otp-code" name="code" type="text" required maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code" autofocus
                        class="w-full rounded-2xl border-2 border-emerald-200 bg-emerald-50/60 px-4 py-4 text-center font-mono text-3xl font-black tracking-[0.45em] text-emerald-800 outline-none transition-all placeholder:text-emerald-300 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200 dark:placeholder:text-emerald-700 dark:focus:bg-slate-800"
                        placeholder="000000" aria-describedby="otp-help">
                    <p id="otp-help" class="mt-2 text-center text-[11px] font-medium text-slate-400 dark:text-slate-500">Kode berlaku sampai <span id="otp-countdown" class="font-extrabold text-emerald-600 dark:text-emerald-400">--:--</span>.</p>
                </div>

                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-500 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-emerald-600/25 transition-all hover:-translate-y-0.5 hover:shadow-xl hover:shadow-emerald-600/30 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500/30 active:translate-y-0">
                    @include('partials.icon', ['name' => 'shield-check', 'class' => 'h-4 w-4'])
                    Verifikasi dan Masuk
                </button>
            </form>

            <div class="mt-5 flex flex-col items-center gap-3 border-t border-slate-100 pt-5 dark:border-slate-700">
                <form action="{{ route('login.otp.resend') }}" method="POST">
                    @csrf
                    <button id="otp-resend-button" type="submit" disabled class="inline-flex items-center gap-1.5 text-xs font-extrabold text-slate-400 transition-colors disabled:cursor-not-allowed enabled:text-emerald-600 enabled:hover:text-emerald-700 dark:enabled:text-emerald-400">
                        @include('partials.icon', ['name' => 'refresh-cw', 'class' => 'h-3.5 w-3.5'])
                        Kirim ulang kode
                        <span id="otp-resend-countdown" class="font-mono"></span>
                    </button>
                </form>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 transition-colors hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200">
                    @include('partials.icon', ['name' => 'arrow-right', 'class' => 'h-3.5 w-3.5 rotate-180'])
                    Kembali ke login
                </a>
            </div>
        </div>

        <p class="mt-5 text-center text-[11px] font-medium text-slate-400 dark:text-slate-500">Jangan bagikan kode OTP kepada siapa pun.</p>
    </main>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const codeInput = document.getElementById('otp-code');
        const countdown = document.getElementById('otp-countdown');
        const resendButton = document.getElementById('otp-resend-button');
        const resendCountdown = document.getElementById('otp-resend-countdown');
        const expiresAt = {{ (int) $expiresAt }} * 1000;
        const resendAt = {{ (int) $resendAt }} * 1000;

        if (codeInput) {
            codeInput.addEventListener('input', () => {
                codeInput.value = codeInput.value.replace(/\D/g, '').slice(0, 6);
            });
        }

        function formatSeconds(seconds) {
            const minutes = Math.floor(seconds / 60).toString().padStart(2, '0');
            const remainder = (seconds % 60).toString().padStart(2, '0');
            return `${minutes}:${remainder}`;
        }

        function tick() {
            const now = Date.now();
            const expiresIn = Math.max(0, Math.ceil((expiresAt - now) / 1000));
            const resendIn = Math.max(0, Math.ceil((resendAt - now) / 1000));

            if (countdown) countdown.textContent = formatSeconds(expiresIn);
            if (resendButton) resendButton.disabled = resendIn > 0 || expiresIn === 0;
            if (resendCountdown) resendCountdown.textContent = resendIn > 0 ? `(${formatSeconds(resendIn)})` : '';

            if (expiresIn > 0) window.setTimeout(tick, 1000);
        }

        tick();
    })();
</script>
@endpush
