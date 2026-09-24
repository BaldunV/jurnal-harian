<div class="student-insight-card bg-white dark:bg-slate-800/80 dark:border-slate-700 rounded-3xl p-6 shadow-sm border border-slate-200/80">
    <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-700 pb-3">
        <div>
            <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-base flex items-center gap-2">
                @include('partials.icon', ['name' => 'history', 'class' => 'w-4 h-4 text-emerald-500'])
                <span>Riwayat Pengisian Kebiasaan Selesai</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar pencapaian jurnal harian Anda selama 7 hari terakhir.</p>
        </div>
        <a href="{{ route('history') }}" wire:navigate class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
            <span>Lihat Kalender</span>
            @include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-3 h-3'])
        </a>
    </div>

    <div class="space-y-3">
        @forelse($recentJournals as $rj)
            @php
                $formattedDate = \Carbon\Carbon::parse($rj->date)->translatedFormat('l, d F Y');
                $isTodayItem = ($rj->date->toDateString() === now()->toDateString());
                $qualified = $rj->qualifiedHabits();
                $qualifiedCount = count(array_filter($qualified));
                $isFullyQualified = $qualifiedCount === 7;
            @endphp
            <div wire:key="history-item-{{ $rj->id }}" class="p-4 rounded-2xl border flex items-center justify-between gap-4 transition-all {{ $isFullyQualified ? 'bg-emerald-50/60 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/30' : ($qualifiedCount > 0 ? 'bg-amber-50/60 border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/30' : 'bg-slate-50 border-slate-200/60 dark:bg-slate-700/40 dark:border-slate-600/60') }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-xs {{ $isFullyQualified ? 'bg-emerald-500 text-white' : ($qualifiedCount > 0 ? 'bg-amber-400 text-amber-950' : 'bg-slate-200 dark:bg-slate-600 text-slate-500 dark:text-slate-400') }}">
                        @if($isFullyQualified)
                            @include('partials.icon', ['name' => 'circle-check', 'class' => 'w-5 h-5'])
                        @elseif($qualifiedCount > 0)
                            @include('partials.icon', ['name' => 'clock-3', 'class' => 'w-5 h-5'])
                        @else
                            @include('partials.icon', ['name' => 'circle-x', 'class' => 'w-5 h-5'])
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-xs text-slate-900 dark:text-white">{{ $formattedDate }}</h4>
                            @if($isTodayItem)
                                <span class="text-[9px] font-extrabold bg-emerald-600 text-white px-2 py-0.5 rounded-full uppercase">Hari Ini</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Status: <span class="font-extrabold {{ $isFullyQualified ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300' }}">{{ $qualifiedCount }}/7 Kebiasaan Terisi</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs hidden sm:inline-flex items-center gap-1.5">
                        @if($qualified['bangun_pagi'])@include('partials.icon', ['name' => 'sunrise', 'class' => 'w-4 h-4 text-amber-500'])@endif
                        @if($qualified['beribadah'])@include('partials.icon', ['name' => 'hand-heart', 'class' => 'w-4 h-4 text-emerald-500'])@endif
                        @if($qualified['berolahraga'])@include('partials.icon', ['name' => 'footprints', 'class' => 'w-4 h-4 text-teal-500'])@endif
                        @if($qualified['makan_sehat'])@include('partials.icon', ['name' => 'salad', 'class' => 'w-4 h-4 text-teal-500'])@endif
                        @if($qualified['gemar_belajar'])@include('partials.icon', ['name' => 'book-open', 'class' => 'w-4 h-4 text-emerald-500'])@endif
                        @if($qualified['bermasyarakat'])@include('partials.icon', ['name' => 'handshake', 'class' => 'w-4 h-4 text-teal-500'])@endif
                        @if($qualified['tidur_cepat'])@include('partials.icon', ['name' => 'moon-star', 'class' => 'w-4 h-4 text-emerald-500'])@endif
                    </span>
                    <button type="button" onclick="showDateDetail('{{ $rj->date->toDateString() }}')" class="btn-detail-slide">
                        <span>Rincian</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="text-center py-6 text-slate-400 dark:text-slate-500 text-xs">Belum ada riwayat pengisian.</div>
        @endforelse
    </div>
</div>
