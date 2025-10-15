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
    <nav class="exam-navbar navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-3 px-4">
        <a class="navbar-brand font-weight-bold text-uppercase tracking-wide" href="#">
            <i class="fas fa-graduation-cap mr-2 text-warning"></i> EXAMDITA
        </a>

        <div class="ml-auto d-flex align-items-center">
            <div class="d-flex align-items-center bg-secondary rounded-pill px-3 py-1 shadow-sm">
                <i class="far fa-user-circle text-white-50 mr-2"></i>
                <span class="text-white font-weight-medium">
                    {{ $participant['name'] }}
                </span>
            </div>
        </div>
    </nav>


    {{-- Content --}}
    <div class="exam-content mb-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                        </div>
                        <div>
                            <span class="btn btn-sm btn-warning mr-2" style="font-weight: 600; font-size: 1rem;" id="timer">00:00:00</span>
                            <button data-toggle="modal" data-target="#questionListModal" class="btn btn-sm btn-info" style="font-size: 1rem;"><i class="fas fa-fw fa-list"></i> Daftar Soal</button>
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
                                'sessionId' => $attempt->exam_session_id,
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

    {{-- Modal istirahat antar mata pelajaran --}}
    <div class="modal fade" id="subjectBreakModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="subjectBreakLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="subjectBreakLabel"><i class="fas fa-fw fa-coffee"></i> Istirahat Sejenak</h5>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-2">Anda telah menyelesaikan mata pelajaran <strong id="break-subject-name">-</strong>.</p>
                    <p class="text-muted mb-3">Silakan manfaatkan waktu istirahat sebelum melanjutkan ke pelajaran berikutnya.</p>
                    <div class="display-4 font-weight-bold" id="break-countdown">01:00</div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-primary" id="break-continue-btn" disabled>Lanjutkan Ujian</button>
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
                    <form action="{{ route('std.finish', ['token' => $token, 'session' => $attempt->exam_session_id]) }}" method="POST" class="d-inline">
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
        (function ($) {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            const subjectSegments = @json($subjectSegments);
            const initialStatuses = @json($questionStatuses);
            const questionStatusUrl = @json(route('std.question.statuses', ['token' => $token, 'session' => $attempt->exam_session_id]));
            const fetchQuestionUrl = @json(route('std.question.fetch', ['token' => $token, 'session' => $attempt->exam_session_id]));
            const saveAnswerUrl = @json(route('std.answer', ['token' => $token, 'session' => $attempt->exam_session_id]));
            const finishUrl = @json(route('std.finish', ['token' => $token, 'session' => $attempt->exam_session_id]));
            const csrfTokenValue = @json(csrf_token());

            const subjectBreakModal = $('#subjectBreakModal');
            const breakCountdownEl = $('#break-countdown');
            const breakSubjectNameEl = $('#break-subject-name');
            const breakContinueBtn = $('#break-continue-btn');

            const indexToSubject = {};
            const subjectState = subjectSegments.map((segment, idx) => {
                const indexes = Array.isArray(segment.question_indexes)
                    ? segment.question_indexes.slice()
                    : [];
                indexes.forEach(orderIndex => {
                    indexToSubject[orderIndex] = idx;
                });

                return {
                    subjectId: segment.subject_id,
                    name: segment.subject_name,
                    questionIndexes: indexes,
                    breakAfter: !!segment.break_after,
                    breakDuration: segment.break_duration_seconds > 0 ? segment.break_duration_seconds : 60,
                    completed: false,
                    breakCompleted: !segment.break_after,
                    answeredCount: 0,
                };
            });

            let currentQuestionIndex = {{ (int) $index }};
            if (currentQuestionIndex <= 0) {
                currentQuestionIndex = parseInt($('#question-index').text(), 10) || 1;
            }

            let currentSubjectIndexState = typeof indexToSubject[currentQuestionIndex] !== 'undefined'
                ? indexToSubject[currentQuestionIndex]
                : {{ (int) $currentSubjectIndex }};
            if (currentSubjectIndexState < 0) {
                currentSubjectIndexState = 0;
            }

            let totalBreakSeconds = 0;
            let activeBreak = null;
            let breakIntervalHandle = null;

            function pad(value) {
                return value < 10 ? '0' + value : String(value);
            }

            function formatTime(seconds) {
                const secs = Math.max(0, Math.floor(seconds));
                const minutes = Math.floor(secs / 60);
                const remainder = secs % 60;
                return `${pad(minutes)}:${pad(remainder)}`;
            }

            function applyStatusBadge($badge, isFlagged, isAnswered) {
                $badge.removeClass('badge-warning text-dark badge-success badge-secondary');
                if (isFlagged) {
                    $badge.addClass('badge-warning text-dark').text('Ditandai ragu-ragu');
                    return;
                }
                if (isAnswered) {
                    $badge.addClass('badge-success').text('Sudah dijawab');
                    return;
                }
                $badge.addClass('badge-secondary').text('Belum dijawab');
            }

            function syncOptionCardSelection(context) {
                const $ctx = context ? $(context) : $('#question-container');
                $ctx.find('.option-card').each(function () {
                    const $card = $(this);
                    const $input = $card.find('.option-input');
                    $card.toggleClass('is-selected', $input.is(':checked'));
                });
            }

            function updateSubjectStateFromStatuses(statuses) {
                if (!Array.isArray(statuses) || !subjectState.length) {
                    return;
                }

                const answeredMap = {};
                statuses.forEach(item => {
                    answeredMap[item.order_index] = !!item.answered;
                });

                subjectState.forEach(state => {
                    const total = state.questionIndexes.length;
                    let answered = 0;
                    state.questionIndexes.forEach(orderIndex => {
                        if (answeredMap[orderIndex]) {
                            answered++;
                        }
                    });
                    state.answeredCount = answered;
                    state.completed = total > 0 && answered >= total;
                });
            }

            function fetchQuestionStatuses(updateUI = true) {
                const request = $.get(questionStatusUrl);

                return request.done(function (list) {
                    const statuses = Array.isArray(list) ? list : [];

                    if (updateUI) {
                        const $wrap = $('#question-list-content').empty();
                        const currentIndex = parseInt($('#question-index').text(), 10);
                        let currentStatus = null;
                        let answeredCount = 0;

                        statuses.forEach(function (item) {
                            if (item.answered) {
                                answeredCount++;
                            }
                            if (item.order_index === currentIndex) {
                                currentStatus = item;
                            }

                            const btnClass = item.flagged
                                ? 'btn-warning'
                                : (item.answered ? 'btn-success' : 'btn-secondary');

                            $('<button/>', {
                                'class': 'btn ' + btnClass + ' m-1 question-goto',
                                'data-index': item.order_index,
                                'text': item.order_index,
                                'title': item.flagged ? 'Ragu-ragu' : (item.answered ? 'Sudah dijawab' : 'Belum dijawab')
                            }).appendTo($wrap);
                        });

                        if (statuses.length) {
                            $('#meta-answered-current').text(answeredCount);
                            $('#meta-answered-total').text(statuses.length);
                        }

                        const $statusBadge = $('#meta-answer-status');
                        if ($statusBadge.length && currentStatus) {
                            applyStatusBadge($statusBadge, currentStatus.flagged, currentStatus.answered);
                        }
                    }

                    updateSubjectStateFromStatuses(statuses);
                });
            }

            function refreshQuestionList() {
                fetchQuestionStatuses(true);
            }

            function loadQuestion(index) {
                $.get(fetchQuestionUrl, { q: index })
                    .done(function (res) {
                        if (res.redirect) {
                            window.location.href = res.redirect;
                            return;
                        }

                        $('#question-container').html(res.html);
                        $('#question-index').text(res.index);
                        $('#question-total').text(res.total);

                        currentQuestionIndex = parseInt(res.index, 10) || currentQuestionIndex;
                        if (typeof indexToSubject[currentQuestionIndex] !== 'undefined') {
                            currentSubjectIndexState = indexToSubject[currentQuestionIndex];
                        }

                        syncOptionCardSelection('#question-container');
                        refreshQuestionList();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
            }

            function shouldTriggerBreak(targetIndex) {
                if (!subjectState.length) {
                    return false;
                }

                const targetSubjectIdx = indexToSubject[targetIndex];
                if (typeof targetSubjectIdx === 'undefined') {
                    return false;
                }
                if (targetSubjectIdx === currentSubjectIndexState) {
                    return false;
                }
                if (targetSubjectIdx < currentSubjectIndexState) {
                    return false;
                }

                const currentState = subjectState[currentSubjectIndexState];
                if (!currentState || !currentState.breakAfter) {
                    return false;
                }
                if (currentState.breakCompleted) {
                    return false;
                }
                return currentState.completed;
            }

            function tickBreakCountdown() {
                if (!activeBreak) {
                    if (breakIntervalHandle) {
                        clearInterval(breakIntervalHandle);
                        breakIntervalHandle = null;
                    }
                    return;
                }

                const elapsed = Math.floor((Date.now() - activeBreak.startedAt) / 1000);
                const remaining = Math.max(0, activeBreak.duration - elapsed);
                breakCountdownEl.text(formatTime(remaining));

                if (remaining <= 0) {
                    if (breakIntervalHandle) {
                        clearInterval(breakIntervalHandle);
                        breakIntervalHandle = null;
                    }
                    breakContinueBtn.prop('disabled', false);
                    activeBreak.ready = true;
                }
            }

            function startBreak(nextIndex) {
                const currentState = subjectState[currentSubjectIndexState];
                if (!currentState) {
                    loadQuestion(nextIndex);
                    return;
                }
                if (activeBreak) {
                    return;
                }

                const duration = currentState.breakDuration > 0 ? currentState.breakDuration : 60;
                activeBreak = {
                    segmentIndex: currentSubjectIndexState,
                    duration: duration,
                    nextIndex: nextIndex,
                    startedAt: Date.now(),
                    ready: false
                };

                breakSubjectNameEl.text(currentState.name || 'Pelajaran berikutnya');
                breakCountdownEl.text(formatTime(duration));
                breakContinueBtn.prop('disabled', true);
                subjectBreakModal.modal('show');

                if (breakIntervalHandle) {
                    clearInterval(breakIntervalHandle);
                }
                breakIntervalHandle = setInterval(tickBreakCountdown, 1000);
                tickBreakCountdown();
            }

            function endBreak() {
                if (!activeBreak || !activeBreak.ready) {
                    return;
                }

                if (breakIntervalHandle) {
                    clearInterval(breakIntervalHandle);
                    breakIntervalHandle = null;
                }

                const { duration, nextIndex, segmentIndex } = activeBreak;
                const state = subjectState[segmentIndex];
                if (state) {
                    state.breakCompleted = true;
                }
                totalBreakSeconds += duration;
                subjectBreakModal.modal('hide');
                activeBreak = null;
                navigateToQuestion(nextIndex, { force: true });
            }

            function navigateToQuestion(index, options = {}) {
                const force = !!options.force;

                if (activeBreak && !force) {
                    return;
                }

                if (!force && shouldTriggerBreak(index)) {
                    startBreak(index);
                    return;
                }

                loadQuestion(index);
            }

            function ensureLatestStatuses() {
                if (!subjectState.length) {
                    return $.Deferred().resolve().promise();
                }
                return fetchQuestionStatuses(false);
            }

            function serializeAnswer(formArray, actionValue, includeNavigation) {
                const data = formArray.filter(item => item.name !== 'action');
                data.push({ name: 'action', value: actionValue });

                if (!includeNavigation && (actionValue === 'save' || actionValue === 'save-multi')) {
                    return data.filter(item => item.name !== 'goto-index');
                }

                return data;
            }

            function autoSaveAnswer(action) {
                const $form = $('#question-container form');
                if ($form.length === 0) return;

                const formData = serializeAnswer($form.serializeArray(), action, false);

                if (window.__autoSaveRequest) {
                    window.__autoSaveRequest.abort();
                }

                window.__autoSaveRequest = $.post(saveAnswerUrl, formData)
                    .done(function () {
                        refreshQuestionList();
                    })
                    .always(function () {
                        window.__autoSaveRequest = null;
                    });
            }

            $(document).on('change', '#question-container .option-card .option-input', function () {
                const $input = $(this);
                const $card = $input.closest('.option-card');
                if ($input.attr('type') === 'radio') {
                    const name = $input.attr('name');
                    $card.closest('.option-grid').find(`input[name="${name}"]`).each(function () {
                        $(this).closest('.option-card').removeClass('is-selected');
                    });
                }
                $card.toggleClass('is-selected', $input.is(':checked'));
                autoSaveAnswer($input.attr('type') === 'checkbox' ? 'save-multi' : 'save');
            });

            $(document).on('change', '#question-container select[name^="matching"]', function () {
                autoSaveAnswer('save');
            });

            $(document).on('change', '#question-container input[name^="tf\\["]', function () {
                autoSaveAnswer('save');
            });

            $(document).on('click', '#question-container button[name="action"]', function () {
                const $form = $(this).closest('form');
                $form.data('last-action', $(this).val());
                $form.data('goto-index', $(this).data('next-index') || null);
            });

            $(document).on('submit', '#question-container form', function (e) {
                e.preventDefault();
                const $form = $(this);
                const lastAction = $form.data('last-action') || 'next';
                const gotoIndexFromBtn = $form.data('goto-index');

                const formData = serializeAnswer($form.serializeArray(), lastAction, true);

                $.post(saveAnswerUrl, formData)
                    .done(function (res) {
                        const currentIndex = parseInt($('#question-index').text(), 10);
                        const total = parseInt($('#question-total').text(), 10);

                        let target = res && res.index ? parseInt(res.index, 10) : NaN;
                        if (Number.isNaN(target) && gotoIndexFromBtn) {
                            target = parseInt(gotoIndexFromBtn, 10);
                        }
                        if (Number.isNaN(target)) {
                            if (lastAction === 'next' && currentIndex < total) target = currentIndex + 1;
                            if (lastAction === 'prev' && currentIndex > 1) target = currentIndex - 1;
                            if (lastAction === 'flag') target = currentIndex;
                        }
                        if (Number.isNaN(target) || target <= 0) {
                            target = currentIndex;
                        }

                        ensureLatestStatuses().always(function () {
                            navigateToQuestion(target);
                        });
                    });
            });

            $('#questionListModal').on('show.bs.modal', refreshQuestionList);
            $(document).on('click', '.question-goto', function () {
                $('#questionListModal').modal('hide');
                const target = parseInt($(this).data('index'), 10);
                if (!Number.isNaN(target)) {
                    ensureLatestStatuses().always(function () {
                        navigateToQuestion(target);
                    });
                }
            });

            $(document).on('click', '.btn-finish-modal', function () {
                $('#confirmFinishModal').modal('show');
            });

            $(document).on('submit', '#confirmFinishModal form', function (e) {
                e.preventDefault();
                const formEl = this;
                fetchQuestionStatuses(false).done(function (list) {
                    const statuses = Array.isArray(list) ? list : [];
                    const unanswered = statuses.filter(item => !item.answered);
                    const flagged = statuses.filter(item => !!item.flagged);

                    const goToQuestionFn = index => {
                        $('#confirmFinishModal').modal('hide');
                        navigateToQuestion(index, { force: true });
                    };

                    if (unanswered.length > 0) {
                        $('#confirmFinishModal').modal('hide');
                        Swal.fire({
                            title: 'Masih ada soal yang belum dijawab',
                            text: 'Selesaikan terlebih dahulu soal yang belum dijawab.',
                            icon: 'warning',
                            confirmButtonText: 'Ke soal yang belum dijawab'
                        }).then(() => goToQuestionFn(unanswered[0].order_index));
                        return;
                    }

                    if (flagged.length > 0) {
                        $('#confirmFinishModal').modal('hide');
                        Swal.fire({
                            title: 'Ada soal yang ditandai ragu-ragu',
                            text: 'Ingin tetap menyelesaikan ujian sekarang?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Tetap selesaikan',
                            cancelButtonText: 'Lihat soal ragu'
                        }).then(result => {
                            if (result.isConfirmed) {
                                formEl.submit();
                            } else if (result.dismiss === Swal.DismissReason.cancel) {
                                goToQuestionFn(flagged[0].order_index);
                            }
                        });
                        return;
                    }

                    formEl.submit();
                });
            });

            breakContinueBtn.on('click', function () {
                endBreak();
            });

            (function initTimer() {
                const timerEl = document.getElementById('timer');
                const durationMinutes = {{ (int) ($session->session_duration ?? 0) }};
                const startedAtSec = {{ optional($attempt->started_at)->timestamp ?? now()->timestamp }};
                const startedAt = startedAtSec * 1000;

                const finishForm = $('<form>', { method: 'POST', action: finishUrl })
                    .append($('<input>', { type: 'hidden', name: '_token', value: csrfTokenValue }))
                    .append($('<input>', { type: 'hidden', name: 'force', value: '1' }))
                    .appendTo('body');

                function tick() {
                    const now = Date.now();
                    const elapsedSeconds = Math.floor((now - startedAt) / 1000);
                    let adjustedElapsed = elapsedSeconds - totalBreakSeconds;

                    if (activeBreak) {
                        const breakElapsed = Math.min(activeBreak.duration, Math.floor((now - activeBreak.startedAt) / 1000));
                        adjustedElapsed -= breakElapsed;
                    }

                    if (adjustedElapsed < 0) {
                        adjustedElapsed = 0;
                    }

                    const remain = Math.max(0, durationMinutes * 60 - adjustedElapsed);
                    const hours = Math.floor(remain / 3600);
                    const minutes = Math.floor((remain % 3600) / 60);
                    const seconds = remain % 60;

                    if (timerEl) {
                        timerEl.textContent = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
                    }

                    if (remain <= 0) {
                        clearInterval(timerInterval);
                        finishForm[0].submit();
                    }
                }

                const timerInterval = setInterval(tick, 1000);
                tick();
            })();

            syncOptionCardSelection('#question-container');
            if (Array.isArray(initialStatuses)) {
                updateSubjectStateFromStatuses(initialStatuses);
            }
        })(jQuery);
    </script>
</body>
</html>
