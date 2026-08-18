{{-- Orbit 7 bintang emas = 7 Kebiasaan Baik --}}
<div class="orbit-wrap relative w-32 h-32 mx-auto" role="img" aria-label="7 Kebiasaan Baik">
    <span class="absolute inset-3 rounded-full border border-dashed border-amber-400/25" aria-hidden="true"></span>
    <div class="orbit-ring absolute inset-0" aria-hidden="true">
        @for($i = 0; $i < 7; $i++)
            <span class="absolute top-1/2 left-1/2 block" style="transform: rotate({{ $i * (360 / 7) }}deg) translateX(58px);">
                @include('partials.icon', ['name' => 'star', 'class' => 'w-2.5 h-2.5 text-amber-400 orbit-star absolute -top-2 -left-2'])
            </span>
        @endfor
    </div>
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="w-[76px] h-[76px] rounded-[22px] bg-white dark:bg-slate-800 shadow-xl shadow-emerald-900/10 dark:shadow-black/50 border border-slate-100 dark:border-slate-700 p-1.5 flex items-center justify-center bintang-float">
            <img src="{{ asset('images/logo-login.png') }}" alt="Logo SMK BPPI" class="w-full h-full object-contain">
        </div>
    </div>
</div>