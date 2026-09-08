{{-- Banner error booking (check_in/out / event / paket). --}}
@if ($errors->has('check_in_date') || $errors->has('check_out_date') || $errors->has('event_date') || $errors->has('number_of_rooms') || $errors->has('event_type') || $errors->has('event_package_id') || $errors->has('hall_id') || $errors->has('session_id') || $errors->has('room_type_id'))
    <div class="flex items-start gap-3 rounded-xl border border-danger/20 bg-danger/5 p-4 text-sm text-danger" role="alert">
        <span class="material-symbols-outlined text-[20px]">error</span>
        <div class="flex-1">
            <p class="font-semibold">Ketersediaan berubah</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-5 opacity-90">
                @foreach (['check_in_date', 'check_out_date', 'event_date', 'number_of_rooms', 'event_type', 'event_package_id', 'hall_id', 'session_id', 'room_type_id'] as $key)
                    @foreach ($errors->get($key) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                @endforeach
            </ul>
            <div class="mt-2 flex flex-wrap gap-3 text-xs font-semibold">
                <a href="{{ route('rooms.index') }}" class="underline underline-offset-4">Lihat kamar lain</a>
                <a href="{{ route('halls.index') }}" class="underline underline-offset-4">Lihat gedung lain</a>
            </div>
        </div>
    </div>
@endif
