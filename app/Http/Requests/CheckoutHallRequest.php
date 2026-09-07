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
            'hall_id' => ['required', 'integer', 'exists:halls,id'],
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
        });
    }
}
