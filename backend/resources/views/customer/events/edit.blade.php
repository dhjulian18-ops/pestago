@extends('layouts.app')
@section('title', 'Edit Acara — PestaGo')
@section('content')
<div style="padding-top:calc(var(--navbar-height) + var(--space-8));">
    <div class="container" style="max-width:700px;">
        <a href="{{ route('customer.events.show', $event) }}" style="font-size:var(--font-size-sm);color:var(--gray-500);"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h1 style="font-size:var(--font-size-2xl);font-weight:800;margin:var(--space-4) 0 var(--space-6);">Edit Acara</h1>
        <div class="card"><div class="card-body" style="padding:var(--space-8);">
            <form action="{{ route('customer.events.update', $event) }}" method="POST">
                @csrf @method('PUT')
                <div class="form-group"><label class="form-label">Nama Acara *</label><input type="text" name="name" class="form-control" value="{{ old('name', $event->name) }}" required></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                    <div class="form-group"><label class="form-label">Jenis Acara *</label><select name="event_type" class="form-control" required>@foreach($eventTypes as $val=>$label)<option value="{{ $val }}" {{ old('event_type',$event->event_type)==$val?'selected':'' }}>{{ $label }}</option>@endforeach</select></div>
                    <div class="form-group"><label class="form-label">Tanggal *</label><input type="date" name="event_date" class="form-control" value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}" required></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                    <div class="form-group"><label class="form-label">Jumlah Tamu</label><input type="number" name="guest_count" class="form-control" value="{{ old('guest_count', $event->guest_count) }}"></div>
                    <div class="form-group"><label class="form-label">Budget (Rp)</label><input type="number" name="budget" class="form-control" value="{{ old('budget', $event->budget) }}"></div>
                </div>
                <div class="form-group"><label class="form-label">Kota</label><input type="text" name="location_city" class="form-control" value="{{ old('location_city', $event->location_city) }}"></div>
                <div class="form-group"><label class="form-label">Alamat</label><input type="text" name="location_address" class="form-control" value="{{ old('location_address', $event->location_address) }}"></div>
                <div class="form-group"><label class="form-label">Kebutuhan Vendor</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-2);">
                        @foreach($categories as $cat)
                        <label class="form-check" style="padding:var(--space-2);border:1px solid var(--gray-200);border-radius:var(--radius-md);">
                            <input type="checkbox" name="requirements[]" value="{{ $cat->id }}" {{ in_array($cat->id, old('requirements', $event->requirements ?? [])) ? 'checked' : '' }}>
                            <span style="font-size:var(--font-size-sm);">{{ $cat->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="form-group"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="3">{{ old('notes', $event->notes) }}</textarea></div>
                <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="bi bi-check-circle"></i> Simpan Perubahan</button>
            </form>
        </div></div>
    </div>
</div>
<div style="padding-bottom:var(--space-16);"></div>
@endsection
