<!-- Permanent Lock Status Banner Modern -->
@if($journal->is_submitted)
    <div id="lock-banner" class="modern-card bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white p-5 flex items-center justify-between gap-4 relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 w-40 h-40 bg-emerald-500/10 rounded-bl-full pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-amber-500/10 rounded-tr-full pointer-events-none"></div>

        <div class="flex items-center gap-4 relative z-10">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500/30 to-amber-500/15 border border-amber-500/40 flex items-center justify-center shrink-0">
                @include('partials.icon', ['name' => 'lock', 'class' => 'w-6 h-6 text-amber-300'])
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h4 class="font-extrabold text-sm text-white">Jurnal Hari Ini Telah Terkunci Permanen</h4>
                    <span class="px-2.5 py-1 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[9px] font-black rounded-full uppercase shadow-lg shadow-emerald-500/30">
                        Sudah Disimpan
                    </span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Anda telah mengklik <strong class="text-emerald-400">"Simpan Jurnal Hari Ini"</strong>. Sesuai ketentuan, jurnal yang telah disimpan <strong class="text-amber-400">tidak dapat diubah kembali selamanya</strong> demi menjaga kejujuran dan kedisiplinan.
                </p>
            </div>
        </div>
    </div>
@endif

<!-- Form Checklist 7 Kebiasaan Baik -->
<form id="journal-form" class="space-y-4">
    <input type="hidden" name="date" value="{{ $journal->date->toDateString() }}">

    <fieldset {{ $journal->is_submitted ? 'disabled' : '' }} id="journal-fieldset" class="space-y-4">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- 1. BANGUN PAGI -->
        <div class="journal-habit-card bg-white dark:bg-slate-800/80 dark:border-slate-700 rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 dark:hover:border-emerald-500/50 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            @include('partials.icon', ['name' => 'sunrise', 'class' => 'w-5 h-5'])
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">1. Bangun Pagi</h4>
                            <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/15 px-2 py-0.5 rounded-md">Kedisiplinan</span>
                            @php
                                $bangunStatus = $journal->bangun_pagi && $journal->isBangunPagiTimeValid() ? 'ok' : ($journal->bangun_pagi ? 'bad' : 'none');
                                $bangunStatusClasses = [
                                    'ok' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
                                    'bad' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
                                    'none' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                                ];
                                $bangunStatusText = ['ok' => 'Sesuai', 'bad' => 'Tidak Sesuai', 'none' => 'Belum'][$bangunStatus];
                            @endphp
                            <span id="bangun-status-badge" class="text-[11px] font-bold px-2 py-0.5 rounded-md mt-0.5 inline-block {{ $bangunStatusClasses[$bangunStatus] }}">{{ $bangunStatusText }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="checkbox-bangun-pagi" name="bangun_pagi" value="1" onchange="playPressSound(this); onBangunPagiChange()" class="sr-only" {{ $journal->bangun_pagi ? 'checked' : '' }} />
                        <canvas
                            id="duo-lingo-canvas"
                            class="block w-24 h-24 sm:w-28 sm:h-28 select-none"
                            style="cursor: pointer; touch-action: none; -webkit-tap-highlight-color: transparent;"
                            aria-label="Maskot duo Lingo"
                        ></canvas>
                    </div>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 leading-relaxed">
                    Memulai hari lebih awal untuk melatih kedisiplinan dan kesiapan mental.
                </p>
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <label for="bangun-pagi-time" class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">
                        @include('partials.icon', ['name' => 'clock', 'class' => 'w-3 h-3 inline-block align-[-1px] text-amber-500 mr-1'])
                        Jam bangun
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="time" id="bangun-pagi-time" name="bangun_pagi_time" value="{{ $journal->bangun_pagi_time ? substr($journal->bangun_pagi_time, 0, 5) : '' }}" onchange="updateTimeStatusBadges(); autoSaveJournal()"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/60 dark:border-slate-600 dark:text-slate-100 border border-slate-200 rounded-lg text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach(['4:00 AM','4:30 AM','5:00 AM','5:30 AM','6:00 AM','6:30 AM','7:00 AM'] as $presetJam)
                            <button type="button" onclick="playPress(); setPresetJam('bangun', '{{ $presetJam }}')" class="slice-btn slice-btn-amber"><span class="text">{{ $presetJam }}</span></button>
                        @endforeach
                    </div>
                    <p class="mt-1.5 text-[10px] text-slate-400 dark:text-slate-500 font-medium">Contoh format: 5:30 AM atau 05:30</p>
                </div>
            </div>
        </div>

        <!-- 2. BERIBADAH (KHUSUS SUBUH, DZUHUR, ASHAR, MAGHRIB, ISYA) -->
        <div class="journal-habit-card bg-white dark:bg-slate-800/80 dark:border-slate-700 rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 dark:hover:border-emerald-500/50 transition-all md:col-span-2">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        @include('partials.icon', ['name' => 'hand-heart', 'class' => 'w-5 h-5'])
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-slate-900 dark:text-white">2. Beribadah</h4>
                            <span id="ibadah-status-badge" class="text-[11px] font-bold px-2 py-0.5 rounded-md {{ $journal->beribadah ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' }}">
                                {{ $journal->beribadah ? 'Sudah Lengkap' : 'Belum Lengkap' }}
                            </span>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Membentuk fondasi spiritual, kejujuran, serta rasa syukur kepada Tuhan.</span>
                    </div>
                </div>

                <!-- Master Checkbox (Read-only status) -->
                <div class="flex flex-col items-end">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-sm shadow-sm transition-all {{ $journal->beribadah ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-400' }}" id="master-ibadah-icon">
                        @include('partials.icon', ['name' => 'check', 'class' => 'w-4 h-4'])
                    </div>
                </div>
            </div>

            @if($user->worship_type === 'muslim')
                <!-- Breakdown 5 Sholat Wajib -->
                @php
                    $details = $journal->ibadah_details ?? ['subuh'=>false,'dzuhur'=>false,'ashar'=>false,'maghrib'=>false,'isya'=>false];
                    $prayersCount = collect($details)->filter()->count();
                @endphp
                <div class="bg-slate-50 dark:bg-slate-900/40 rounded-xl p-4 border border-slate-200/60 dark:border-slate-700 mt-2">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                        <span>Checklist 5 Sholat Wajib:</span>
                        <span class="text-emerald-600 dark:text-emerald-400" id="sholat-progress-text">{{ $prayersCount }}/5 Sholat</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                        @foreach(['subuh' => ['Subuh', 'sunrise'], 'dzuhur' => ['Dzuhur', 'sun'], 'ashar' => ['Ashar', 'cloud-sun'], 'maghrib' => ['Maghrib', 'sunset'], 'isya' => ['Isya', 'moon-star']] as $key => [$label, $icon])
                            <label class="flex items-center gap-2.5 p-2.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 cursor-pointer hover:border-emerald-400 dark:hover:border-emerald-500/50 transition-all shadow-xs group">
                                <div class="neon-checkbox shrink-0">
                                        <input
                                            type="checkbox"
                                            name="ibadah_{{ $key }}"
                                            value="1"
                                            onchange="playSholatSound(this); updatePrayerLogic()"
                                            class="prayer-checkbox"
                                            title="Tandai sholat {{ $label }}"
                                            {{ !empty($details[$key]) ? 'checked' : '' }}
                                        >
                                    <div class="neon-checkbox__frame">
                                        <div class="neon-checkbox__box">
                                            <div class="neon-checkbox__check-container">
                                                <svg viewBox="0 0 24 24" class="neon-checkbox__check">
                                                    <path d="M3,12.5l7,7L21,5" />
                                                </svg>
                                            </div>
                                            <div class="neon-checkbox__borders">
                                                <span></span><span></span><span></span><span></span>
                                            </div>
                                        </div>
                                        <div class="neon-checkbox__effects">
                                            <div class="neon-checkbox__particles">
                                                <span></span><span></span><span></span><span></span>
                                                <span></span><span></span><span></span><span></span>
                                                <span></span><span></span><span></span><span></span>
                                            </div>
                                            <div class="neon-checkbox__rings">
                                                <div class="ring"></div>
                                                <div class="ring"></div>
                                                <div class="ring"></div>
                                            </div>
                                            <div class="neon-checkbox__sparks">
                                                <span></span><span></span><span></span><span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <span class="select-none leading-none group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors inline-flex items-center gap-1.5">
                                    @include('partials.icon', ['name' => $icon, 'class' => 'w-3.5 h-3.5 opacity-70'])
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Breakdown Non-Muslim -->
                @php
                    $details = $journal->ibadah_details ?? ['doa_pagi'=>false,'kitab_meditasi'=>false,'doa_malam'=>false];
                    $prayersCount = collect($details)->filter()->count();
                @endphp
                <div class="bg-slate-50 dark:bg-slate-900/40 rounded-xl p-4 border border-slate-200/60 dark:border-slate-700 mt-2">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                        <span>Checklist Ibadah Rutin Harian:</span>
                        <span class="text-emerald-600 dark:text-emerald-400" id="sholat-progress-text">{{ $prayersCount }}/3 Selesai</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        @foreach(['doa_pagi' => ['Doa Pagi / Saat Teduh', 'sunrise'], 'kitab_meditasi' => ['Membaca Kitab / Meditasi', 'book-open'], 'doa_malam' => ['Doa Malam', 'moon-star']] as $key => [$label, $icon])
                            <label class="flex items-center gap-2.5 p-2.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 cursor-pointer hover:border-emerald-400 dark:hover:border-emerald-500/50 transition-all shadow-xs group">
                                <div class="neon-checkbox shrink-0">
                                    <input
                                        type="checkbox"
                                        name="ibadah_{{ $key }}"
                                        value="1"
                                        onchange="updatePrayerLogic()"
                                        class="prayer-checkbox"
                                        title="Tandai {{ $label }}"
                                        {{ !empty($details[$key]) ? 'checked' : '' }}
                                    >
                                    <div class="neon-checkbox__frame">
                                        <div class="neon-checkbox__box">
                                            <div class="neon-checkbox__check-container">
                                                <svg viewBox="0 0 24 24" class="neon-checkbox__check">
                                                    <path d="M3,12.5l7,7L21,5" />
                                                </svg>
                                            </div>
                                            <div class="neon-checkbox__borders">
                                                <span></span><span></span><span></span><span></span>
                                            </div>
                                        </div>
                                        <div class="neon-checkbox__effects">
                                            <div class="neon-checkbox__particles">
                                                <span></span><span></span><span></span><span></span>
                                                <span></span><span></span><span></span><span></span>
                                            </div>
                                            <div class="neon-checkbox__rings">
                                                <div class="ring"></div>
                                                <div class="ring"></div>
                                                <div class="ring"></div>
                                            </div>
                                            <div class="neon-checkbox__sparks">
                                                <span></span><span></span><span></span><span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <span class="select-none leading-none group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors inline-flex items-center gap-1.5">
                                    @include('partials.icon', ['name' => $icon, 'class' => 'w-3.5 h-3.5 opacity-70'])
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- 3. BEROLAHRAGA -->
        <div class="journal-habit-card bg-white dark:bg-slate-800/80 dark:border-slate-700 rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 dark:hover:border-emerald-500/50 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-teal-100 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                            @include('partials.icon', ['name' => 'footprints', 'class' => 'w-5 h-5'])
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">3. Berolahraga</h4>
                            <span class="text-[11px] font-semibold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-500/15 px-2 py-0.5 rounded-md">Kesehatan Fisik</span>
                        </div>
                    </div>

                    <div class="toy-camera-container">
                        <label class="toy-camera-wrapper">
                            <input type="checkbox" id="checkbox-berolahraga" name="berolahraga" value="1" onchange="playCameraSound(this); autoSaveJournal()" class="toy-camera-input" {{ $journal->berolahraga ? 'checked' : '' }} />
                            <div class="toy-camera-body">
                                <div class="toy-camera-button"></div>
                                <div class="toy-camera-lens"></div>
                                <div class="toy-camera-photo">
                                    <div class="photo-image"></div>
                                    <div class="photo-text"></div>
                                    <div class="photo-text" style="width: 40%;"></div>
                                </div>
                            </div>
                            <div class="toy-camera-shadow"></div>
                        </label>
                    </div>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 leading-relaxed">
                    Menjaga kebugaran tubuh dan kesehatan mental agar lebih fokus belajar.
                </p>
            </div>

            <!-- Optional Note -->
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                <input type="text" name="olahraga_note" value="{{ $journal->olahraga_note }}" onblur="autoSaveJournal()"
                    placeholder="Opsional: Jenis olahraga (misal: Push up 20x / Jogging)"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/60 dark:border-slate-600 dark:text-slate-100 border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
        </div>

        <!-- 4. MAKAN SEHAT DAN BERGIZI -->
        <div class="journal-habit-card bg-white dark:bg-slate-800/80 dark:border-slate-700 rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 dark:hover:border-emerald-500/50 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-teal-100 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                            @include('partials.icon', ['name' => 'salad', 'class' => 'w-5 h-5'])
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">4. Makan Sehat & Bergizi</h4>
                            <span class="text-[11px] font-semibold text-teal-600 dark:text-teal-300 bg-teal-50 dark:bg-teal-500/15 px-2 py-0.5 rounded-md">Nutrisi Organik</span>
                        </div>
                    </div>

                    <div class="toy-camera-container">
                        <label class="toy-camera-wrapper">
                            <input type="checkbox" id="checkbox-makan-sehat" name="makan_sehat" value="1" onchange="playCameraSound(this); autoSaveJournal()" class="toy-camera-input" {{ $journal->makan_sehat ? 'checked' : '' }} />
                            <div class="toy-camera-body">
                                <div class="toy-camera-button"></div>
                                <div class="toy-camera-lens"></div>
                                <div class="toy-camera-photo">
                                    <div class="photo-image"></div>
                                    <div class="photo-text"></div>
                                    <div class="photo-text" style="width: 40%;"></div>
                                </div>
                            </div>
                            <div class="toy-camera-shadow"></div>
                        </label>
                    </div>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 leading-relaxed">
                    Memenuhi kebutuhan nutrisi seimbang untuk mendukung pertumbuhan otak dan tubuh.
                </p>
            </div>

            <!-- Optional Note -->
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                <input type="text" name="makan_note" value="{{ $journal->makan_note }}" onblur="autoSaveJournal()"
                    placeholder="Opsional: Menu makan (misal: Nasi, Sayur bayam, Telur, Buah)"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/60 dark:border-slate-600 dark:text-slate-100 border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
        </div>

        <!-- 5. GEMAR BELAJAR -->
        <div class="journal-habit-card bg-white dark:bg-slate-800/80 dark:border-slate-700 rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 dark:hover:border-emerald-500/50 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            @include('partials.icon', ['name' => 'book-open', 'class' => 'w-5 h-5'])
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">5. Gemar Belajar</h4>
                            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/15 px-2 py-0.5 rounded-md">Literasi & Wawasan</span>
                        </div>
                    </div>

                    <div class="toy-camera-container">
                        <label class="toy-camera-wrapper">
                            <input type="checkbox" id="checkbox-gemar-belajar" name="gemar_belajar" value="1" onchange="playCameraSound(this); autoSaveJournal()" class="toy-camera-input" {{ $journal->gemar_belajar ? 'checked' : '' }} />
                            <div class="toy-camera-body">
                                <div class="toy-camera-button"></div>
                                <div class="toy-camera-lens"></div>
                                <div class="toy-camera-photo">
                                    <div class="photo-image"></div>
                                    <div class="photo-text"></div>
                                    <div class="photo-text" style="width: 40%;"></div>
                                </div>
                            </div>
                            <div class="toy-camera-shadow"></div>
                        </label>
                    </div>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 leading-relaxed">
                    Menumbuhkan rasa ingin tahu serta semangat membaca sepanjang hayat.
                </p>
            </div>

            <!-- Optional Note -->
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                <input type="text" name="belajar_note" value="{{ $journal->belajar_note }}" onblur="autoSaveJournal()"
                    placeholder="Opsional: Materi / Buku yang dipelajari"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/60 dark:border-slate-600 dark:text-slate-100 border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
        </div>

        <!-- 6. BERMASYARAKAT -->
        <div class="journal-habit-card bg-white dark:bg-slate-800/80 dark:border-slate-700 rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 dark:hover:border-emerald-500/50 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-teal-100 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                            @include('partials.icon', ['name' => 'handshake', 'class' => 'w-5 h-5'])
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">6. Bermasyarakat</h4>
                            <span class="text-[11px] font-semibold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-500/15 px-2 py-0.5 rounded-md">Empati & Kerjasama</span>
                        </div>
                    </div>

                    <div class="toy-camera-container">
                        <label class="toy-camera-wrapper">
                            <input type="checkbox" id="checkbox-bermasyarakat" name="bermasyarakat" value="1" onchange="playCameraSound(this); autoSaveJournal()" class="toy-camera-input" {{ $journal->bermasyarakat ? 'checked' : '' }} />
                            <div class="toy-camera-body">
                                <div class="toy-camera-button"></div>
                                <div class="toy-camera-lens"></div>
                                <div class="toy-camera-photo">
                                    <div class="photo-image"></div>
                                    <div class="photo-text"></div>
                                    <div class="photo-text" style="width: 40%;"></div>
                                </div>
                            </div>
                            <div class="toy-camera-shadow"></div>
                        </label>
                    </div>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 leading-relaxed">
                    Mengasah rasa empati, toleransi, dan kemampuan kerja sama dengan lingkungan sekitar.
                </p>
            </div>

            <!-- Optional Note -->
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                <input type="text" name="masyarakat_note" value="{{ $journal->masyarakat_note }}" onblur="autoSaveJournal()"
                    placeholder="Opsional: Kegiatan sosial (misal: Kerja bakti / Bantu teman)"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/60 dark:border-slate-600 dark:text-slate-100 border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
        </div>

        <!-- 7. TIDUR CEPAT -->
        <div class="journal-habit-card bg-white dark:bg-slate-800/80 dark:border-slate-700 rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 dark:hover:border-emerald-500/50 transition-all md:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            @include('partials.icon', ['name' => 'moon-star', 'class' => 'w-5 h-5'])
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">7. Tidur Cepat</h4>
                            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/15 px-2 py-0.5 rounded-md mt-0.5 inline-block">Istirahat Cukup</span>
                            @php
                                $tidurStatus = $journal->tidur_cepat && $journal->isTidurCepatTimeValid() ? 'ok' : ($journal->tidur_cepat ? 'bad' : 'none');
                                $tidurStatusClasses = [
                                    'ok' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
                                    'bad' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
                                    'none' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                                ];
                                $tidurStatusText = ['ok' => 'Sesuai', 'bad' => 'Tidak Sesuai', 'none' => 'Belum'][$tidurStatus];
                            @endphp
                            <span id="tidur-status-badge" class="text-[11px] font-bold px-2 py-0.5 rounded-md mt-0.5 inline-block {{ $tidurStatusClasses[$tidurStatus] }}">{{ $tidurStatusText }}</span>
                        </div>
                    </div>

                    <input type="checkbox" id="checkbox-tidur-cepat" name="tidur_cepat" value="1" onchange="playPressSound(this); onTidurCepatChange()" class="sr-only" {{ $journal->tidur_cepat ? 'checked' : '' }} />
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 leading-relaxed">
                    Memastikan istirahat yang cukup guna memulihkan tenaga untuk esok hari (Disarankan tidur sebelum pukul 22.00).
                </p>
            </div>

            <!-- Optional Note -->
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                <label for="input-tidur-note" class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">
                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-3 h-3 inline-block align-[-1px] text-emerald-500 mr-1'])
                    Jam tidur
                </label>
                <div class="flex items-center gap-2">
                <input type="text" id="input-tidur-note" name="tidur_note" value="{{ $journal->tidur_note }}" onchange="updateTimeStatusBadges(); autoSaveJournal()" onblur="updateTimeStatusBadges(); autoSaveJournal()"
                    placeholder="Jam tidur"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800/60 dark:border-slate-600 dark:text-slate-100 border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
            <div class="mt-2 flex flex-wrap gap-1.5">
                @foreach(['8:00 PM','8:30 PM','9:00 PM','9:30 PM','10:00 PM'] as $presetJam)
                    <button type="button" onclick="playPress(); setPresetJam('tidur', '{{ $presetJam }}')" class="slice-btn slice-btn-emerald"><span class="text">{{ $presetJam }}</span></button>
                @endforeach
            </div>
            <p class="mt-1.5 text-[10px] text-slate-400 dark:text-slate-500 font-medium">Contoh format: 9:10 PM atau 21:30</p>
    </div>
    </fieldset>

    <!-- Manual Save Button -->
    <div class="flex justify-end pt-2">
        @if($journal->is_submitted)
            <button type="button" disabled class="w-full sm:w-auto px-8 py-3.5 bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded-full font-bold text-sm cursor-not-allowed flex items-center justify-center gap-2 border border-slate-300 dark:border-slate-600">
                @include('partials.icon', ['name' => 'lock', 'class' => 'w-4 h-4'])
                <span>Jurnal Selesai & Terkunci Permanen</span>
            </button>
        @else
            <button type="button" onclick="submitJournalForm()" class="btn-save-glow w-full sm:w-auto flex items-center justify-center gap-2">
                <span class="btn-content flex items-center gap-2">
                    @include('partials.icon', ['name' => 'save', 'class' => 'w-4 h-4'])
                    <span>Simpan Jurnal Hari Ini</span>
                </span>
            </button>
        @endif
    </div>
</form>
