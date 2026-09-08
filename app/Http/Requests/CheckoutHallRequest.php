<?php

namespace App\Http\Requests;

use App\Models\EventPackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutHallRequest extends FormRequest
{
    public const EVENT_TYPES = [
        'wedding',
        'corporate',
        'birthday',
        'graduation',
        'seminar',
        'other',
    ];

    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'hall_id' => ['required', 'integer', Rule::exists('halls', 'id')->where('is_active', true)],
            'session_id' => ['required', 'integer', 'exists:hall_sessions,id'],
            'event_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'event_type' => ['required', 'string', Rule::in(self::EVENT_TYPES)],
            'event_package_id' => ['nullable', 'integer', 'exists:event_packages,id'],
            'special_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $packageId = $this->input('event_package_id');
            $hallId = $this->input('hall_id');

            if ($packageId && $hallId) {
                $belongs = EventPackage::whereKey($packageId)->where('hall_id', $hallId)->exists();
                if (! $belongs) {
                    $validator->errors()->add('event_package_id', 'Paket acara tidak tersedia untuk gedung ini.');
                }
            }

            // Tolak sesi hari ini yang jam mulainya sudah lewat.
            $sessionId = $this->input('session_id');
            $eventDate = $this->input('event_date');
            if ($sessionId && $eventDate) {
                try {
                    $session = \App\Models\HallSession::find($sessionId);
                    if ($session && $eventDate === today()->format('Y-m-d')) {
                        $startTime = $session->start_time instanceof \DateTimeInterface
                            ? $session->start_time->format('H:i')
                            : (string) $session->start_time;
                        $start = \Carbon\Carbon::parse($eventDate.' '.$startTime);
                        if ($start->lte(now())) {
                            $validator->errors()->add('event_date', 'Sesi hari ini sudah mulai/lewat. Pilih tanggal atau sesi lain.');
                        }
                    }
                } catch (\Throwable) {
                    // Abaikan; validasi format tanggal sudah menangani.
                }
            }
        });
    }
}
