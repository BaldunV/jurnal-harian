@extends('layouts.app', ['hidePageLoader' => true])

@section('title', 'Dokumentasi Foto Siswa')

@section('content')

{{-- HERO --}}
<div class="admin-hero rounded-3xl p-5 sm:p-7 text-white shadow-card">
    <div class="flex flex-col gap-5">

        <div class="flex flex-col lg:flex-row gap-4 lg:items-end lg:justify-between">
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-emerald-100 mb-2">
                    @include('partials.icon', [
                        'name' => 'images',
                        'class' => 'w-3.5 h-3.5 inline-block mr-1'
                    ])
                    Panel Admin
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Dokumentasi Foto Siswa
                </h1>

                <p class="text-sm text-emerald-50/80 mt-1 max-w-2xl">
                    Pantau dokumentasi kegiatan siswa berdasarkan kelas,
                    tanggal, dan jenis kebiasaan.
                </p>
            </div>

            <nav class="admin-view-switcher self-start lg:self-auto"
                 aria-label="Tampilan admin">

                <a href="{{ route('admin.dashboard') }}" wire:navigate>
                    @include('partials.icon', [
                        'name' => 'chart-column',
                        'class' => 'w-3.5 h-3.5'
                    ])
                    Rekap
                </a>

                <a href="{{ route('admin.dashboard', ['view' => 'registered']) }}#daftar-siswa-terdaftar"
                   wire:navigate>
                    @include('partials.icon', [
                        'name' => 'user-check',
                        'class' => 'w-3.5 h-3.5'
                    ])
                    Siswa terdaftar
                </a>

                <a href="{{ route('admin.documentation') }}"
                   wire:navigate
                   aria-current="page">
                    @include('partials.icon', [
                        'name' => 'images',
                        'class' => 'w-3.5 h-3.5'
                    ])
                    Dokumentasi
                </a>
            </nav>
        </div>

        {{-- FILTER --}}
        <form method="GET"
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2
                     bg-white/10 p-3 rounded-2xl border border-white/15">

            <div>
                <label for="kelas"
                       class="block text-[10px] font-extrabold uppercase
                              tracking-wider text-emerald-50 mb-1.5">
                    Kelas
                </label>

                <select id="kelas"
                        name="kelas"
                        class="w-full rounded-xl px-3 py-2.5 text-xs font-bold
                               text-slate-700 bg-white dark:bg-slate-800
                               dark:text-slate-100">
                    <option value="">Semua kelas</option>

                    @foreach($classList as $kelas)
                        <option value="{{ $kelas }}"
                            @selected($selectedClass === $kelas)>
                            {{ $kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date"
                       class="block text-[10px] font-extrabold uppercase
                              tracking-wider text-emerald-50 mb-1.5">
                    Tanggal
                </label>

                <input type="date"
                       id="date"
                       name="date"
                       value="{{ $selectedDate }}"
                       class="w-full rounded-xl px-3 py-2.5 text-xs font-bold
                              text-slate-700 bg-white dark:bg-slate-800
                              dark:text-slate-100">
            </div>

            <div>
                <label for="habit"
                       class="block text-[10px] font-extrabold uppercase
                              tracking-wider text-emerald-50 mb-1.5">
                    Kegiatan
                </label>

                <select id="habit"
                        name="habit"
                        class="w-full rounded-xl px-3 py-2.5 text-xs font-bold
                               text-slate-700 bg-white dark:bg-slate-800
                               dark:text-slate-100">

                    <option value="">Semua dokumentasi</option>

                    <option value="olahraga"
                        @selected($selectedHabit === 'olahraga')>
                        Berolahraga
                    </option>

                    <option value="makan"
                        @selected($selectedHabit === 'makan')>
                        Makan Sehat
                    </option>

                    <option value="belajar"
                        @selected($selectedHabit === 'belajar')>
                        Gemar Belajar
                    </option>

                    <option value="masyarakat"
                        @selected($selectedHabit === 'masyarakat')>
                        Bermasyarakat
                    </option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-white
                               text-emerald-700 text-xs font-extrabold
                               hover:bg-emerald-50 transition-colors">
                    @include('partials.icon', [
                        'name' => 'search',
                        'class' => 'w-3.5 h-3.5 inline-block mr-1'
                    ])
                    Terapkan
                </button>

                <a href="{{ route('admin.documentation') }}"
                   class="px-4 py-2.5 rounded-xl bg-white/10
                          border border-white/20 text-white
                          text-xs font-extrabold hover:bg-white/20
                          transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>


{{-- HEADER GALERI --}}
<div class="mt-6 mb-4 flex flex-col sm:flex-row
            sm:items-center sm:justify-between gap-3">

    <div>
        <div class="text-[10px] font-extrabold uppercase
                    tracking-wider text-emerald-600
                    dark:text-emerald-400">
            Galeri siswa
        </div>

        <h2 class="text-lg font-extrabold
                   text-slate-800 dark:text-slate-100">
            Dokumentasi Kegiatan
        </h2>

        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            {{ $journals->total() }} jurnal memiliki dokumentasi foto.
        </p>
    </div>
</div>


{{-- GALERI DOKUMENTASI PER SISWA --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

    @forelse($journals as $journal)

        @php
            $photoItems = collect([
                'olahraga' => [
                    'label' => 'Berolahraga',
                    'icon' => 'footprints',
                    'photo' => $journal->olahraga_photo
                        ? route('admin.documentation.photo', [
                            $journal->id,
                            'olahraga'
                        ])
                        : null,
                    'note' => $journal->olahraga_note,
                ],

                'makan' => [
                    'label' => 'Makan Sehat',
                    'icon' => 'salad',
                    'photo' => $journal->makan_photo
                        ? route('admin.documentation.photo', [
                            $journal->id,
                            'makan'
                        ])
                        : null,
                    'note' => $journal->makan_note,
                ],

                'belajar' => [
                    'label' => 'Gemar Belajar',
                    'icon' => 'book-open',
                    'photo' => $journal->belajar_photo
                        ? route('admin.documentation.photo', [
                            $journal->id,
                            'belajar'
                        ])
                        : null,
                    'note' => $journal->belajar_note,
                ],

                'masyarakat' => [
                    'label' => 'Bermasyarakat',
                    'icon' => 'handshake',
                    'photo' => $journal->masyarakat_photo
                        ? route('admin.documentation.photo', [
                            $journal->id,
                            'masyarakat'
                        ])
                        : null,
                    'note' => $journal->masyarakat_note,
                ],
            ]);

            if ($selectedHabit !== '') {
                $photoItems = collect([
                    $selectedHabit => $photoItems[$selectedHabit]
                ]);
            }

            $photoItems = $photoItems
                ->filter(fn ($item) => !empty($item['photo']))
                ->values();

            $frontPhoto = $photoItems->get(0);
            $secondPhoto = $photoItems->get(1);
            $thirdPhoto = $photoItems->get(2);

            $galleryPayload = $photoItems->map(fn ($item) => [
                'label' => $item['label'],
                'photo' => $item['photo'],
                'note' => $item['note'],
            ])->values();
        @endphp


        @if($photoItems->isNotEmpty())

            <article
                class="group bg-white dark:bg-slate-800/80
                       border border-slate-200/80 dark:border-slate-700
                       rounded-3xl shadow-sm hover:shadow-lg
                       transition-all duration-300 overflow-hidden">

                {{-- HEADER --}}
                <div class="p-5 pb-2">

                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">
                            <h3 class="font-extrabold text-slate-900
                                       dark:text-white truncate">
                                {{ $journal->user->name }}
                            </h3>

                            <p class="mt-1 text-[11px] font-semibold
                                      text-slate-500 dark:text-slate-400">

                                NIS {{ $journal->user->nis }}

                                <span class="mx-1">&bull;</span>

                                {{ $journal->user->kelas }}
                            </p>
                        </div>

                        <div class="text-right shrink-0">

                            <div class="text-[10px] font-bold
                                        text-slate-400 dark:text-slate-500">
                                {{ $journal->date->translatedFormat('d M Y') }}
                            </div>

                            <div class="mt-1 inline-flex items-center gap-1
                                        px-2 py-1 rounded-lg
                                        bg-emerald-50 dark:bg-emerald-500/15
                                        text-emerald-700 dark:text-emerald-300
                                        text-[10px] font-extrabold">

                                @include('partials.icon', [
                                    'name' => 'images',
                                    'class' => 'w-3 h-3'
                                ])

                                {{ $photoItems->count() }} Foto
                            </div>

                        </div>

                    </div>
                </div>


                {{-- STACKED PHOTOS --}}
                <button
                    type="button"
                    onclick="openDocumentationGallery({{ $journal->id }})"
                    class="w-full px-6 py-5">

                    <div class="relative mx-auto
                                w-full max-w-[250px]
                                aspect-[4/3]">

                        {{-- FOTO PALING BELAKANG --}}
                        @if($thirdPhoto)
                            <img
                                src="{{ $thirdPhoto['photo'] }}"
                                alt=""
                                loading="lazy"
                                class="absolute inset-0
                                       w-full h-full object-cover
                                       rounded-2xl border-4
                                       border-white dark:border-slate-700
                                       shadow-md
                                       rotate-[-7deg]
                                       scale-[0.90]
                                       translate-y-1
                                       transition-all duration-300
                                       group-hover:-translate-x-7
                                       group-hover:rotate-[-11deg]
                                       group-hover:scale-[0.92]">
                        @endif


                        {{-- FOTO TENGAH --}}
                        @if($secondPhoto)
                            <img
                                src="{{ $secondPhoto['photo'] }}"
                                alt=""
                                loading="lazy"
                                class="absolute inset-0
                                       w-full h-full object-cover
                                       rounded-2xl border-4
                                       border-white dark:border-slate-700
                                       shadow-md
                                       rotate-[6deg]
                                       scale-[0.95]
                                       transition-all duration-300
                                       group-hover:translate-x-7
                                       group-hover:rotate-[10deg]
                                       group-hover:scale-[0.96]">
                        @endif


                        {{-- FOTO DEPAN --}}
                        <img
                            src="{{ $frontPhoto['photo'] }}"
                            alt="Dokumentasi {{ $journal->user->name }}"
                            loading="lazy"
                            class="absolute inset-0 z-20
                                   w-full h-full object-cover
                                   rounded-2xl border-4
                                   border-white dark:border-slate-700
                                   shadow-lg
                                   transition-all duration-300
                                   group-hover:scale-[1.02]">


                        {{-- JUMLAH FOTO LAIN --}}
                        @if($photoItems->count() > 1)

                            <div class="absolute z-30
                                        right-3 bottom-3
                                        min-w-9 h-9 px-2
                                        rounded-full
                                        bg-slate-950/75
                                        backdrop-blur-md
                                        text-white
                                        flex items-center justify-center
                                        text-xs font-black
                                        border border-white/20">

                                +{{ $photoItems->count() - 1 }}

                            </div>

                        @endif

                    </div>

                </button>


                {{-- JENIS DOKUMENTASI --}}
                <div class="px-5 pb-4">

                    <div class="flex flex-wrap gap-1.5">

                        @foreach($photoItems as $item)

                            <span
                                class="inline-flex items-center gap-1
                                       px-2.5 py-1 rounded-full
                                       bg-slate-100 dark:bg-slate-700/70
                                       text-slate-600 dark:text-slate-300
                                       text-[10px] font-bold">

                                @include('partials.icon', [
                                    'name' => $item['icon'],
                                    'class' => 'w-3 h-3'
                                ])

                                {{ $item['label'] }}

                            </span>

                        @endforeach

                    </div>

                </div>


                {{-- FOOTER --}}
                <button
                    type="button"
                    onclick="openDocumentationGallery({{ $journal->id }})"
                    class="w-full border-t
                           border-slate-100 dark:border-slate-700
                           px-5 py-3.5
                           flex items-center justify-between
                           text-xs font-extrabold
                           text-emerald-600 dark:text-emerald-400
                           hover:bg-emerald-50/60
                           dark:hover:bg-emerald-500/5
                           transition-colors">

                    <span class="inline-flex items-center gap-1.5">

                        @include('partials.icon', [
                            'name' => 'images',
                            'class' => 'w-3.5 h-3.5'
                        ])

                        Lihat dokumentasi

                    </span>

                    @include('partials.icon', [
                        'name' => 'chevron-right',
                        'class' => 'w-4 h-4'
                    ])

                </button>


                {{-- DATA UNTUK CAROUSEL --}}
                <script
                    type="application/json"
                    id="documentation-gallery-{{ $journal->id }}">{!! $galleryPayload->toJson(
                        JSON_HEX_TAG |
                        JSON_HEX_APOS |
                        JSON_HEX_AMP |
                        JSON_HEX_QUOT
                    ) !!}</script>

            </article>

        @endif


    @empty

        <div class="md:col-span-2 xl:col-span-3
                    bg-white dark:bg-slate-800/80
                    border border-slate-200/80 dark:border-slate-700
                    rounded-3xl p-10 text-center">

            @include('partials.icon', [
                'name' => 'image-off',
                'class' => 'w-8 h-8 mx-auto text-slate-400'
            ])

            <h3 class="mt-3 font-extrabold
                       text-slate-800 dark:text-slate-100">
                Belum ada dokumentasi
            </h3>

            <p class="mt-1 text-xs
                      text-slate-500 dark:text-slate-400">
                Belum ditemukan foto untuk filter yang dipilih.
            </p>

        </div>

    @endforelse

</div>


{{-- MODAL DOKUMENTASI RESPONSIVE --}}
<div
    id="documentation-modal"
    class="fixed inset-0 z-[100]
           hidden items-center justify-center
           bg-slate-950/75 backdrop-blur-sm
           p-2 sm:p-4">

    <div
        class="w-[min(96vw,900px)]
               max-h-[94dvh]
               bg-white dark:bg-slate-900
               rounded-2xl sm:rounded-3xl
               shadow-2xl
               overflow-hidden
               flex flex-col">

        {{-- HEADER --}}
        <div
            class="shrink-0
                   px-4 sm:px-5
                   py-3 sm:py-4
                   border-b border-slate-200
                   dark:border-slate-800
                   flex items-center
                   justify-between gap-3">

            <div class="min-w-0">

                <p class="text-[10px] sm:text-xs
                          font-bold text-emerald-600">
                    Dokumentasi siswa
                </p>

                <h2
                    id="documentation-modal-student"
                    class="truncate
                           text-sm sm:text-base
                           font-extrabold
                           text-slate-900 dark:text-white">
                    Dokumentasi
                </h2>

            </div>

            <button
                type="button"
                onclick="closeDocumentationGallery()"
                class="shrink-0
                       inline-flex items-center gap-1.5
                       px-3 py-2
                       rounded-xl
                       bg-slate-100 hover:bg-slate-200
                       dark:bg-slate-800 dark:hover:bg-slate-700
                       text-xs font-bold
                       text-slate-700 dark:text-slate-200">

                @include('partials.icon', [
                    'name' => 'x',
                    'class' => 'w-4 h-4'
                ])

                <span class="hidden sm:inline">
                    Tutup
                </span>
            </button>

        </div>


        {{-- VIEWER --}}
        <div
            class="relative
                   flex-1
                   min-h-0
                   bg-slate-950
                   flex items-center justify-center
                   overflow-hidden">

            <img
                id="documentation-modal-image"
                src=""
                alt=""
                draggable="false"
                class="block
                       w-full h-full
                       max-w-full max-h-full
                       object-contain
                       select-none">

        </div>


        {{-- INFORMASI + NAVIGASI --}}
        <div
            class="shrink-0
                   px-4 sm:px-5
                   py-3 sm:py-4
                   bg-white dark:bg-slate-900
                   border-t border-slate-200
                   dark:border-slate-800">

            {{-- INFORMASI --}}
            <div
                class="flex items-start
                       justify-between gap-3">

                <div class="min-w-0">

                    <div
                        id="documentation-modal-label"
                        class="text-sm
                               font-extrabold
                               text-slate-900
                               dark:text-white">
                    </div>

                    <p
                        id="documentation-modal-note"
                        class="mt-0.5
                               text-[11px] sm:text-xs
                               leading-relaxed
                               text-slate-500
                               dark:text-slate-400
                               line-clamp-2">
                    </p>

                </div>

                <span
                    id="documentation-modal-counter"
                    class="shrink-0
                           px-2.5 py-1
                           rounded-full
                           bg-slate-100 dark:bg-slate-800
                           text-[10px] sm:text-xs
                           font-extrabold
                           text-slate-600
                           dark:text-slate-300">
                </span>

            </div>


            {{-- NAVIGATION --}}
            <div
                class="mt-3
                       grid grid-cols-[auto_1fr_auto]
                       items-center gap-2 sm:gap-4">

                <button
                    id="documentation-prev"
                    type="button"
                    onclick="documentationPrev()"
                    class="inline-flex
                           items-center justify-center gap-1
                           min-w-10 h-10
                           sm:px-3
                           rounded-xl
                           border border-slate-200
                           dark:border-slate-700
                           bg-white dark:bg-slate-800
                           hover:bg-slate-50
                           dark:hover:bg-slate-700
                           text-slate-700
                           dark:text-slate-200
                           text-xs font-bold">

                    @include('partials.icon', [
                        'name' => 'chevron-left',
                        'class' => 'w-4 h-4'
                    ])

                    <span class="hidden md:inline">
                        Sebelumnya
                    </span>

                </button>


                <div
                    id="documentation-modal-dots"
                    class="min-w-0
                           flex items-center
                           justify-center gap-1.5">
                </div>


                <button
                    id="documentation-next"
                    type="button"
                    onclick="documentationNext()"
                    class="inline-flex
                           items-center justify-center gap-1
                           min-w-10 h-10
                           sm:px-3
                           rounded-xl
                           bg-emerald-600
                           hover:bg-emerald-700
                           text-white
                           text-xs font-bold">

                    <span class="hidden md:inline">
                        Berikutnya
                    </span>

                    @include('partials.icon', [
                        'name' => 'chevron-right',
                        'class' => 'w-4 h-4'
                    ])

                </button>

            </div>

        </div>

    </div>
</div>


{{-- PAGINATION --}}
@if($journals->hasPages())
    <div class="mt-7">
        {{ $journals->links() }}
    </div>
@endif

<script>
    let documentationPhotos = [];
    let documentationIndex = 0;


    function openDocumentationGallery(journalId) {

        const source = document.getElementById(
            'documentation-gallery-' + journalId
        );

        if (!source) return;

        try {
            documentationPhotos = JSON.parse(
                source.textContent
            );
        } catch (error) {
            console.error(error);
            return;
        }

        if (!documentationPhotos.length) return;

        documentationIndex = 0;

        const modal = document.getElementById(
            'documentation-modal'
        );

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.classList.add('overflow-hidden');

        renderDocumentationGallery();
    }


    function closeDocumentationGallery() {

        const modal = document.getElementById(
            'documentation-modal'
        );

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.classList.remove('overflow-hidden');
    }


    function documentationPrev() {

        if (!documentationPhotos.length) return;

        documentationIndex--;

        if (documentationIndex < 0) {
            documentationIndex =
                documentationPhotos.length - 1;
        }

        renderDocumentationGallery();
    }


    function documentationNext() {

        if (!documentationPhotos.length) return;

        documentationIndex++;

        if (
            documentationIndex >=
            documentationPhotos.length
        ) {
            documentationIndex = 0;
        }

        renderDocumentationGallery();
    }


    function documentationGoTo(index) {

        documentationIndex = index;

        renderDocumentationGallery();
    }


    function renderDocumentationGallery() {

        const item =
            documentationPhotos[documentationIndex];

        if (!item) return;


        const image = document.getElementById(
            'documentation-modal-image'
        );

        const label = document.getElementById(
            'documentation-modal-label'
        );

        const note = document.getElementById(
            'documentation-modal-note'
        );

        const counter = document.getElementById(
            'documentation-modal-counter'
        );

        const dots = document.getElementById(
            'documentation-modal-dots'
        );


        image.src = item.photo;
        image.alt = item.label;

        label.textContent = item.label;

        note.textContent =
            item.note || 'Tidak ada catatan tambahan.';

        counter.textContent =
            'Foto ' +
            (documentationIndex + 1) +
            ' dari ' +
            documentationPhotos.length;


        dots.innerHTML = '';

        documentationPhotos.forEach(
            function (_, index) {

                const button =
                    document.createElement('button');

                button.type = 'button';

                button.className =
                    'rounded-full transition-all duration-200 ' +
                    (
                        index === documentationIndex
                            ? 'w-6 h-2 bg-emerald-500'
                            : 'w-2 h-2 bg-slate-300 dark:bg-slate-600 hover:bg-slate-400'
                    );

                button.onclick = function () {
                    documentationGoTo(index);
                };

                dots.appendChild(button);
            }
        );


        const hideNavigation =
            documentationPhotos.length <= 1;

        document.getElementById(
            'documentation-prev'
        ).classList.toggle(
            'hidden',
            hideNavigation
        );

        document.getElementById(
            'documentation-next'
        ).classList.toggle(
            'hidden',
            hideNavigation
        );
    }


    document.addEventListener(
        'keydown',
        function (event) {

            const modal =
                document.getElementById(
                    'documentation-modal'
                );

            if (
                !modal ||
                modal.classList.contains('hidden')
            ) {
                return;
            }

            if (event.key === 'Escape') {
                closeDocumentationGallery();
            }

            if (event.key === 'ArrowLeft') {
                documentationPrev();
            }

            if (event.key === 'ArrowRight') {
                documentationNext();
            }
        }
    );


    document
        .getElementById('documentation-modal')
        .addEventListener('click', function (event) {

            if (event.target === this) {
                closeDocumentationGallery();
            }

        });
</script>

@endsection
