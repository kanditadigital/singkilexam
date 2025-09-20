<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Exam Panel' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('vendor/stislaravel/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/stislaravel/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/stislaravel/css/exampanel.css') }}">
</head>
<body>

    {{-- Navbar --}}
    <nav class="exam-navbar navbar navbar-dark bg-dark shadow-sm mb-3 px-3">
        <span class="navbar-brand mb-0 h6">EXAMDITA</span>
        <div class="ml-auto text-white">
            <p class="mt-3 mb-0">{{ Auth::guard('students')->user()->student_name }}</p>
        </div>
    </nav>

    {{-- Content --}}
    <div class="exam-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong>No. Soal <span id="question-index">{{ $index }}</span>/<span id="question-total">{{ $total }}</span></strong>
                        </div>
                        <div>
                            <a href="#" class="btn btn-sm btn-info mr-2">Informasi Soal</a>
                            <span class="btn btn-sm btn-outline-dark mr-2" id="timer">00:00:00</span>
                            <button data-toggle="modal" data-target="#questionListModal" class="btn btn-sm btn-info">Daftar Soal</button>
                        </div>
                    </div>

                    {{-- Soal --}}
                    <div class="card shadow-sm">
                        <div id="question-container" class="card-body">
                            @include('std.question', [
                                'attemptQuestion' => $attemptQuestion,
                                'index' => $index,
                                'total' => $total,
                                'token' => $token,
                                'answeredCount' => $answeredCount,
                            ])
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Modal daftar soal --}}
    <div class="modal fade" id="questionListModal" data-backdrop="static" tabindex="-1" aria-labelledby="questionListLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daftar Soal</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="question-list-content" class="d-flex flex-wrap"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal konfirmasi selesai --}}
    <div class="modal fade" id="confirmFinishModal" data-backdrop="static" tabindex="-1" aria-labelledby="confirmFinishLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Selesaikan Ujian</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menyelesaikan ujian sekarang? Pastikan semua jawaban sudah sesuai.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <form action="{{ route('std.finish', $token) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">Ya, Selesaikan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- JS --}}
    <script src="{{ asset('vendor/stislaravel/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/stislaravel/js/bootstrap.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $.ajaxSetup({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
        });

        // Refresh daftar soal
        function refreshQuestionList() {
            $.get(@json(route('std.question.statuses', $token)))
                .done(function(list) {
                    const $wrap = $('#question-list-content').empty();
                    list.forEach(function(item){
                        const btnClass = item.flagged ? 'btn-warning' : (item.answered ? 'btn-success' : 'btn-secondary');
                        const $btn = $('<button/>', {
                            'class': 'btn '+btnClass+' m-1 question-goto',
                            'data-index': item.order_index,
                            'text': item.order_index,
                            'title': item.flagged ? 'Ragu-ragu' : (item.answered ? 'Sudah dijawab' : 'Belum dijawab')
                        });
                        $wrap.append($btn);
                    });
                });
        }

        // Load soal
        function loadQuestion(index) {
            $.get(@json(route('std.question.fetch', $token)), { q: index })
                .done(function(res){
                    if (res.redirect) return window.location.href = res.redirect;
                    $('#question-container').html(res.html);
                    $('#question-index').text(res.index);
                    $('#question-total').text(res.total);
                });
        }

        // Modal daftar soal
        $('#questionListModal').on('show.bs.modal', refreshQuestionList);
        $(document).on('click', '.question-goto', function(){
            $('#questionListModal').modal('hide');
            loadQuestion($(this).data('index'));
        });

        // Submit jawaban via AJAX
        // Klik tombol navigasi: simpan aksi + index tujuan
        $(document).on('click', '#question-container button[name="action"]', function(){
            const $form = $(this).closest('form');
            $form.data('last-action', $(this).val());
            $form.data('goto-index', $(this).data('next-index') || null); // ambil dari atribut data
        });

        // Submit form jawaban via AJAX
        $(document).on('submit', '#question-container form', function(e){
            e.preventDefault();
            const $form = $(this);
            const lastAction = $form.data('last-action') || 'next';
            const gotoIndexFromBtn = $form.data('goto-index');

            // serialize data form + tambahkan action
            const formData = $form.serializeArray();
            formData.push({name:'action', value:lastAction});

            $.post(@json(route('std.answer', $token)), formData)
                .done(function(res){
                    const currentIndex = parseInt($('#question-index').text(), 10);
                    const total = parseInt($('#question-total').text(), 10);

                    let target = null;

                    // 1) Prioritaskan index dari server
                    if (res && res.index) {
                        target = res.index;
                    }

                    // 2) Kalau server tidak kirim index, pakai dari tombol
                    if (!target && gotoIndexFromBtn) {
                        target = parseInt(gotoIndexFromBtn, 10);
                    }

                    // 3) Fallback terakhir: hitung manual
                    if (!target) {
                        if (lastAction === 'next' && currentIndex < total) target = currentIndex + 1;
                        if (lastAction === 'prev' && currentIndex > 1) target = currentIndex - 1;
                        if (lastAction === 'flag') target = currentIndex;
                    }

                    // Kalau target tidak valid, pakai current
                    if (!target) target = currentIndex;

                    loadQuestion(target);
                    refreshQuestionList();
                });
        });


        // Timer
        (function() {
            const timerEl = document.getElementById('timer');
            const durationMinutes = {{ (int) ($session->session_duration ?? 0) }};
            const startedAtSec = {{ optional($attempt->started_at)->timestamp ?? now()->timestamp }};
            const startedAt = startedAtSec * 1000;
            const finishForm = $('<form>', {method:'POST', action:@json(route('std.finish', $token))})
                .append($('<input>', {type:'hidden', name:'_token', value:@json(csrf_token())}))
                .append($('<input>', {type:'hidden', name:'force', value:'1'}))
                .appendTo('body');

            function pad(n){return n<10? '0'+n : n}
            function tick(){
                const now = Date.now();
                const elapsed = Math.floor((now - startedAt)/1000);
                const remain = Math.max(0, durationMinutes*60 - elapsed);
                const h = Math.floor(remain/3600);
                const m = Math.floor((remain%3600)/60);
                const s = remain%60;
                if (timerEl) timerEl.textContent = `${pad(h)}:${pad(m)}:${pad(s)}`;
                if (remain <= 0) {
                    clearInterval(intv);
                    finishForm[0].submit();
                }
            }
            const intv = setInterval(tick, 1000); tick();
        })();

        // Modal finish
        $(document).on('click', '.btn-finish-modal', () => $('#confirmFinishModal').modal('show'));
        $(document).on('submit', '#confirmFinishModal form', function(e){
            e.preventDefault();
            const formEl = this;
            $.get(@json(route('std.question.statuses', $token)))
                .done(function(list){
                    const flagged = (list || []).filter(item => !!item.flagged);
                    if (flagged.length > 0) {
                        $('#confirmFinishModal').modal('hide');
                        Swal.fire({
                            title: 'Masih ada soal ragu-ragu',
                            text: 'Mohon selesaikan soal yang ditandai ragu-ragu terlebih dahulu.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ke soal ragu',
                            cancelButtonText: 'Tutup'
                        }).then(result => {
                            if (result.isConfirmed) loadQuestion(flagged[0].order_index);
                        });
                    } else {
                        formEl.submit();
                    }
                });
        });
    </script>
</body>
</html>
