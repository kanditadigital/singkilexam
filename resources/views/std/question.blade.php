@php
    $question = $attemptQuestion->question;

    $answerArray = [];
    if (!empty($attemptQuestion->answer)) {
        $decoded = json_decode($attemptQuestion->answer, true);
        if (is_array($decoded)) {
            $answerArray = $decoded;
        }
    }

    if (!$question->relationLoaded('questionOptions') && method_exists($question, 'load')) {
        $question->load('questionOptions');
    }

    $options = collect($question->questionOptions ?? []);
    if (is_array($attemptQuestion->options_order) && count($attemptQuestion->options_order) > 0) {
        $orderMap = array_flip($attemptQuestion->options_order);
        $options = $options
            ->sortBy(fn($opt) => $orderMap[$opt->id] ?? PHP_INT_MAX)
            ->values();
    }

    $isMultiple   = $question->question_type === 'multiple_response';
    $isMatching   = $question->question_type === 'matching';
    $isTrueFalse  = $question->question_type === 'true_false';

    $labels       = $question->questionOptions->pluck('option_label')->map(fn($v) => (string) $v)->toArray();
    $hasTrueFalse = in_array('Benar', $labels, true) || in_array('Salah', $labels, true);
    $isTFGrid     = $isTrueFalse && (!$hasTrueFalse || $options->count() > 2);

    $rawAnswer = $attemptQuestion->answer;
    $hasAnswer = $rawAnswer !== null
        && trim($rawAnswer) !== ''
        && trim($rawAnswer) !== '[]'
        && trim($rawAnswer) !== '{}';

    $flagged = (bool) $attemptQuestion->flagged;
    $statusBadgeClass = 'badge-secondary';
    $statusBadgeText = 'Belum dijawab';

    if ($flagged) {
        $statusBadgeClass = 'badge-warning text-dark';
        $statusBadgeText = 'Ditandai ragu-ragu';
    } elseif ($hasAnswer) {
        $statusBadgeClass = 'badge-success';
        $statusBadgeText = 'Sudah dijawab';
    }

    $flagButtonLabel = $flagged ? 'Hilangkan Tanda Ragu-ragu' : 'Tandai Ragu-ragu';
    $flagButtonClass = $flagged ? 'btn-outline-warning' : 'btn-warning';

    $answeredCount = $answeredCount ?? 0;
@endphp

<form action="{{ route('std.answer', ['token' => $token, 'session' => $sessionId]) }}" method="POST" class="question-form">
    @csrf
    <input type="hidden" name="index" value="{{ $index }}">

    <div class="question-meta card shadow-sm mb-3">
        <div class="card-body">
            <div class="meta-item">
                <span class="meta-label">Soal</span>
                <span class="meta-value">
                    <span id="meta-question-index">{{ $index }}</span>
                    /
                    <span id="meta-question-total">{{ $total }}</span>
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Sudah dijawab</span>
                <span class="meta-value">
                    <span id="meta-answered-current">{{ $answeredCount }}</span>
                    /
                    <span id="meta-answered-total">{{ $total }}</span>
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Pelajaran</span>
                <span class="meta-value" id="meta-subject-name">{{ $question->subject->subject_name ?? 'N/A' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Status Soal</span>
                <span id="meta-answer-status" class="badge badge-pill {{ $statusBadgeClass }}">{{ $statusBadgeText }}</span>
            </div>
        </div>
    </div>

    <div class="question-card card shadow-sm">
        <div class="card-body">
            <div class="question-layout row">
                <div class="col-lg-6 question-content">
                    @if(in_array($question->question_format, ['image','text_image']) && $question->question_image)
                        <div class="question-media mb-3 text-center">
                            <img src="{{ asset('storage/'.$question->question_image) }}" alt="Gambar soal" class="img-fluid question-image">
                        </div>
                    @endif

                    @if(in_array($question->question_format, ['text','text_image']))
                        <div class="question-text align-justify">
                            {!! $question->question_text !!}
                        </div>
                    @endif
                </div>

                <div class="col-lg-6 answer-area">
                    @if($isMatching)
                        @php
                            $leftItems  = $question->questionOptions->filter(fn($o) => str_starts_with($o->option_label, 'L'));
                            $rightItems = $question->questionOptions->filter(fn($o) => str_starts_with($o->option_label, 'R'));
                        @endphp
                        <div class="matching-grid">
                            <div class="matching-header mb-2">
                                <div class="matching-col">Daftar</div>
                                <div class="matching-col">Pasangkan dengan</div>
                            </div>
                            @foreach($leftItems as $left)
                                @php $selected = $answerArray[$left->option_label] ?? ''; @endphp
                                <div class="matching-row">
                                    <div class="matching-col">
                                        <span class="matching-label">{{ $left->option_label }}.</span>
                                        <span>{{ nl2br(e($left->option_text)) }}</span>
                                    </div>
                                    <div class="matching-col">
                                        <select name="matching[{{ $left->option_label }}]" class="custom-select">
                                            <option value="">Pilih jawaban</option>
                                            @foreach($rightItems as $right)
                                                <option value="{{ $right->option_label }}" {{ $selected === $right->option_label ? 'selected' : '' }}>
                                                    {{ $right->option_label }}. {{ $right->option_text }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @elseif($isTFGrid)
                        <div class="tf-grid table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Pernyataan</th>
                                        <th class="text-center">{{ $question->true_label ?? 'Benar' }}</th>
                                        <th class="text-center">{{ $question->false_label ?? 'Salah' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($options as $opt)
                                        @php $selected = $answerArray[$opt->id] ?? null; @endphp
                                        <tr>
                                            {{-- <td>{!! nl2br(e($opt->option_text)) !!}</td> --}}
                                            <td>
                                            {{-- Tampilkan teks jika ada --}}
                                            @if(!empty($opt->option_text))
                                                {{ nl2br(e($opt->option_text)) }}
                                            @endif

                                            {{-- Tampilkan gambar jika ada --}}
                                            @if(!empty($opt->option_image))
                                                <br>
                                                <img src="{{ asset('storage/'.$opt->option_image) }}"
                                                    alt="Option Image"
                                                    style="max-width: 200px; height:auto;">
                                            @endif
                                        </td>
                                            <td class="text-center align-middle">
                                                <label class="tf-option">
                                                    <input type="radio" name="tf[{{ $opt->id }}]" value="true" {{ $selected === 'true' || $selected === true ? 'checked' : '' }}>
                                                </label>
                                            </td>
                                            <td class="text-center align-middle">
                                                <label class="tf-option">
                                                    <input type="radio" name="tf[{{ $opt->id }}]" value="false" {{ $selected === 'false' || $selected === false ? 'checked' : '' }}>
                                                </label>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    @else
                        <div class="option-grid">
                            @foreach($options as $opt)
                                @php
                                    $inputName = $isMultiple ? 'answer[]' : 'answer';
                                    $checkedValues = array_map('strval', is_array($answerArray) ? $answerArray : [$answerArray]);
                                    $isChecked = in_array((string) $opt->id, $checkedValues, true);
                                @endphp
                                <label class="option-card {{ $isChecked ? 'is-selected' : '' }}">
                                    <input
                                        type="{{ $isMultiple ? 'checkbox' : 'radio' }}"
                                        name="{{ $inputName }}"
                                        value="{{ $opt->id }}"
                                        class="option-input"
                                        {{ $isChecked ? 'checked' : '' }}
                                    >
                                    {{-- <span class="option-marker">{{ $opt->option_label }}</span> --}}
                                    <div class="option-content">
                                        @if($opt->option_image)
                                            <img src="{{ asset('storage/'.$opt->option_image) }}" alt="Pilihan {{ $opt->option_label }}" class="option-image">
                                        @endif
                                        @if($opt->option_text)
                                            <div class="option-text">{{ nl2br(e($opt->option_text)) }}</div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="question-actions card shadow-sm mt-3">
        <div class="card-body d-flex flex-wrap justify-content-between">
            <button
                type="submit"
                name="action"
                value="prev"
                class="btn btn-primary btn-action"
                data-next-index="{{ max(1, $index - 1) }}"
                {{ $index <= 1 ? 'disabled' : '' }}
            >
                <i class="fas fa-arrow-left mr-1"></i> Soal Sebelumnya
            </button>

            <button
                type="submit"
                name="action"
                value="flag"
                class="btn {{ $flagButtonClass }} btn-action"
                data-next-index="{{ $index }}"
            >
                <i class="fas fa-flag mr-1"></i> {{ $flagButtonLabel }}
            </button>

            <button type="button" class="btn btn-success btn-action btn-finish-modal">
                <i class="far fa-check-circle mr-1"></i> Selesaikan Ujian
            </button>

            <button
                type="submit"
                name="action"
                value="next"
                class="btn btn-primary btn-action"
                data-next-index="{{ min($total, $index + 1) }}"
                {{ $index >= $total ? 'disabled' : '' }}
            >
                Soal Berikutnya <i class="fas fa-arrow-right ml-1"></i>
            </button>
        </div>
    </div>
</form>
