<?php

namespace App\Exports;

use App\Models\ExamParticipant;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ParticipantCardsExport implements FromCollection, WithHeadings, WithTitle
{
    protected $exam;

    public function __construct($exam)
    {
        $this->exam = $exam;
    }

    public function collection()
    {
        $schoolId = Auth::guard('schools')->id();

        return ExamParticipant::with(['participant', 'exam'])
            ->where('exam_id', $this->exam->id)
            ->where('school_id', $schoolId)
            ->get()
            ->map(function ($participant) {
                return [
                    'Nama' => $this->participantName($participant),
                    'Identitas' => $this->participantIdentifier($participant),
                    'Jenis Peserta' => $this->typeLabelFromClass($participant->participant_type),
                    'Nama Ujian' => $participant->exam->exam_name,
                    'Kode Ujian' => $participant->exam->exam_code,
                    'Sekolah' => Auth::guard('schools')->user()->school_name,
                    'Info Tambahan' => $this->participantMeta($participant),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Identitas',
            'Jenis Peserta',
            'Nama Ujian',
            'Kode Ujian',
            'Sekolah',
            'Info Tambahan',
        ];
    }

    public function title(): string
    {
        return 'Kartu Peserta - ' . $this->exam->exam_name;
    }

    private function participantName(ExamParticipant $participant): string
    {
        $user = $participant->participant;
        if (!$user) {
            return '-';
        }

        return $participant->participant_type === \App\Models\Employee::class
            ? ($user->employee_name ?? '-')
            : ($user->student_name ?? '-');
    }

    private function participantIdentifier(ExamParticipant $participant): string
    {
        $user = $participant->participant;
        if (!$user) {
            return '-';
        }

        return $participant->participant_type === \App\Models\Employee::class
            ? ($user->username ?? $user->email ?? '-')
            : ($user->student_nisn ?? '-');
    }

    private function typeLabelFromClass(?string $class): string
    {
        if ($class === \App\Models\Employee::class) {
            return 'Guru/Staff';
        }
        return 'Siswa';
    }

    private function participantMeta(ExamParticipant $participant): string
    {
        $user = $participant->participant;
        if (!$user) {
            return '-';
        }

        return $participant->participant_type === \App\Models\Employee::class
            ? ($user->employee_type ?? '-')
            : ($user->student_gender ?? '-');
    }
}
