@php
    $q = $attemptQuestion->question;

    // Decode jawaban hanya sekali
    $answerArray = [];
    if (!empty($attemptQuestion->answer)) {
        $decoded = json_decode($attemptQuestion->answer, true);
        if (is_array($decoded)) {
            $answerArray = $decoded;
        }
    }

    // Atur urutan opsi jika ada custom order
    $options = $q->questionOptions;
    if (is_array($attemptQuestion->options_order) && count($attemptQuestion->options_order) > 0) {
        $orderMap = array_flip($attemptQuestion->options_order);
        $options = $options->sortBy(fn($opt) => $orderMap[$opt->id] ?? PHP_INT_MAX);
    }

    // Tipe soal
    $isMultiple   = $q->question_type === 'multiple_response';
    $isMatching   = $q->question_type === 'matching';
    $isTrueFalse  = $q->question_type === 'true_false';

    // Deteksi apakah True/False berupa grid
    $labels       = $q->questionOptions->pluck('option_label')->map(fn($v) => (string)$v)->toArray();
    $hasBenarSalah= in_array('Benar', $labels, true) || in_array('Salah', $labels, true);
    $isTFGrid     = $isTrueFalse && (!$hasBenarSalah || $options->count() > 2);
@endphp

<form action="{{ route('std.answer', $token) }}" method="POST">
    @csrf
    <input type="hidden" name="index" value="{{ $index }}">

    <div class="row">
        {{-- Kolom soal --}}
        <div class="col-md-7 border-right">
            {{-- Gambar soal --}}
            @if(in_array($q->question_format, ['image','text_image']) && $q->question_image)
                <div class="mb-3 text-center">
                    <img src="{{ asset('storage/'.$q->question_image) }}" alt="Soal" class="img-fluid">
                </div>
            @endif

            {{-- Teks soal --}}
            @if(in_array($q->question_format, ['text','text_image']))
                <div class="question-text text-justify">
                    {!! $q->question_text !!}
                </div>
            @endif
        </div>

        {{-- Kolom jawaban --}}
        <div class="col-md-5">
            <div class="mb-2">
                <span class="badge {{ $attemptQuestion->flagged ? 'badge-warning' : 'badge-secondary' }}">
                    {{ $attemptQuestion->flagged ? 'Ragu-ragu' : 'Belum ditandai' }}
                </span>
            </div>

            {{-- Soal Matching --}}
            @if($isMatching)
                @php
                    $leftItems  = $q->questionOptions->filter(fn($o) => str_starts_with($o->option_label, 'L'));
                    $rightItems = $q->questionOptions->filter(fn($o) => str_starts_with($o->option_label, 'R'));
                @endphp
                <div class="row font-weight-bold mb-2">
                    <div class="col-md-6">Kiri</div>
                    <div class="col-md-6">Pasangan</div>
                </div>
                @foreach($leftItems as $left)
                    @php $sel = $answerArray[$left->option_label] ?? null; @endphp
                    <div class="row align-items-center mb-2">
                        <div class="col-md-6">
                            {{ $left->option_label }}. {{ $left->option_text }}
                        </div>
                        <div class="col-md-6">
                            <select name="matching[{{ $left->option_label }}]" class="form-control">
                                <option value="">-- Pilih --</option>
                                @foreach($rightItems as $right)
                                    <option value="{{ $right->option_label }}" {{ $sel === $right->option_label ? 'selected' : '' }}>
                                        {{ $right->option_label }}. {{ $right->option_text }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endforeach

            {{-- Soal True/False Grid --}}
            @elseif($isTFGrid)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Pernyataan</th>
                                <th class="text-center" style="width:110px">Benar</th>
                                <th class="text-center" style="width:110px">Salah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($options as $opt)
                                @php $sel = $answerArray[$opt->id] ?? null; @endphp
                                <tr>
                                    <td>{!! nl2br(e($opt->option_text)) !!}</td>
                                    <td class="text-center">
                                        <input type="radio" id="tf_{{ $opt->id }}_true" name="tf[{{ $opt->id }}]" value="true"
                                            {{ $sel === 'true' || $sel === true ? 'checked' : '' }}>
                                        <label for="tf_{{ $opt->id }}_true"></label>
                                    </td>
                                    <td class="text-center">
                                        <input type="radio" id="tf_{{ $opt->id }}_false" name="tf[{{ $opt->id }}]" value="false"
                                            {{ $sel === 'false' || $sel === false ? 'checked' : '' }}>
                                        <label for="tf_{{ $opt->id }}_false"></label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            {{-- Soal Pilihan Ganda (single/multiple) --}}
            @else
                <div class="list-group">
                    @foreach($options as $opt)
                        @php
                            $inputName = $isMultiple ? 'answer[]' : 'answer';
                            $inputId   = "opt_{$opt->id}";
                            $isChecked = in_array((string)$opt->id, array_map('strval',$answerArray), true);
                        @endphp
                        <div class="list-group-item">
                            <input type="{{ $isMultiple ? 'checkbox' : 'radio' }}"
                                   id="{{ $inputId }}"
                                   name="{{ $inputName }}"
                                   value="{{ $opt->id }}"
                                   class="mr-2"
                                   {{ $isChecked ? 'checked' : '' }}>
                            <label for="{{ $inputId }}" class="ml-2">
                                <strong>{{ $opt->option_label }}</strong><br>
                                @if($opt->option_image)
                                    <img src="{{ asset('storage/'.$opt->option_image) }}" alt="Opsi" class="img-fluid mb-2">
                                @endif
                                @if($opt->option_text)
                                    <div>{!! nl2br(e($opt->option_text)) !!}</div>
                                @endif
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Navigasi --}}
    <div class="card mt-3">
        <div class="d-flex justify-content-between p-3">
          <button
            name="action" value="prev" type="submit"
            class="btn btn-secondary"
            data-next-index="{{ max(1, $index - 1) }}"
            {{ $index <= 1 ? 'disabled' : '' }}>
            <i class="fas fa-arrow-left"></i> Sebelumnya
          </button>
      
          <button name="action" value="flag" type="submit" class="btn btn-warning">
            <i class="fas fa-flag"></i> Tandai Ragu-ragu
          </button>
      
          @if(($answeredCount ?? 0) >= $total)
            <button type="button" class="btn btn-success btn-finish-modal">
              <i class="far fa-check-circle"></i> Selesaikan Ujian
            </button>
          @endif
      
          <button
            name="action" value="next" type="submit"
            class="btn btn-secondary"
            data-next-index="{{ min($total, $index + 1) }}"
            {{ $index >= $total ? 'disabled' : '' }}>
            Selanjutnya <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </div>
      
</form>
