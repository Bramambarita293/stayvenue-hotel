<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'check_in_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date_format:Y-m-d', 'after:check_in_date'],
            'number_of_rooms' => ['required', 'integer', 'min:1', 'max:5'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $in = $this->input('check_in_date');
            $out = $this->input('check_out_date');

            if (! $in || ! $out) {
                return;
            }

            try {
                $nights = \Carbon\Carbon::parse($in)->diffInDays(\Carbon\Carbon::parse($out));
            } catch (\Throwable) {
                return;
            }

            if ($nights < 1 || $nights > 14) {
                $validator->errors()->add('check_out_date', 'Lama menginap maksimal 14 malam.');
            }
        });
    }
}
