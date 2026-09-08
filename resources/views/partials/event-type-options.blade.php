{{-- Opsi jenis acara — nilainya wajib cocok dengan CheckoutHallRequest::EVENT_TYPES. --}}
@php
    $eventTypeOptions = [
        'wedding' => 'Pernikahan',
        'corporate' => 'Rapat / Korporat',
        'birthday' => 'Ulang Tahun',
        'graduation' => 'Wisuda',
        'seminar' => 'Seminar',
        'other' => 'Lainnya',
    ];
    $selectedEventType = old('event_type', $selected ?? '');
@endphp
@foreach ($eventTypeOptions as $value => $label)
    <option value="{{ $value }}" @selected($selectedEventType === $value)>{{ $label }}</option>
@endforeach
