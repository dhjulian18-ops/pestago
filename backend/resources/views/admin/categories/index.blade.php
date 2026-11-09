@extends('layouts.app')
@section('title', 'Kategori — Admin PestaGo')
@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'categories'])
    <div class="dashboard-content">
        <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div><h1 class="dashboard-title">Manajemen Kategori</h1></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-8);">
            {{-- Add Category Form --}}
            <div class="card"><div class="card-body">
                <h3 style="font-weight:700;margin-bottom:var(--space-4);">Tambah Kategori</h3>
                <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group"><label class="form-label">Nama *</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    <div class="form-group"><label class="form-label">Icon (Bootstrap Icons class)</label><input type="text" name="icon" class="form-control" placeholder="cth: bi-camera"></div>
                    <div class="form-group"><label class="form-label">Gambar</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus"></i> Tambah</button>
                </form>
            </div></div>

            {{-- Categories List --}}
            <div class="card"><div class="card-body">
                <h3 style="font-weight:700;margin-bottom:var(--space-4);">Daftar Kategori</h3>
                @foreach($categories as $cat)
                <div style="display:flex;align-items:center;gap:var(--space-3);padding:var(--space-3) 0;border-bottom:1px solid var(--gray-50);">
                    <div class="category-icon" style="width:40px;height:40px;font-size:1rem;"><i class="{{ $cat->icon }}"></i></div>
                    <div style="flex:1;">
                        <strong>{{ $cat->name }}</strong>
                        <div style="font-size:var(--font-size-xs);color:var(--gray-400);">{{ $cat->services_count }} layanan</div>
                    </div>
                    <span class="badge badge-{{ $cat->is_active ? 'success' : 'gray' }}">{{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                @endforeach
            </div></div>
        </div>
    </div>
</div>
@endsection
