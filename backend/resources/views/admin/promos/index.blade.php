@extends('layouts.app')
@section('title', 'Manajemen Promo — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'promos'])

    <div class="dashboard-content">
        <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:var(--space-4);">
            <div>
                <h1 class="dashboard-title"><i class="bi bi-tag" style="color:var(--primary);"></i> Manajemen Promo Platform</h1>
                <p class="dashboard-subtitle">Kelola banner promo, diskon spesial, dan kampanye promosi di platform PestaGo.</p>
            </div>
            <button type="button" onclick="openAddPromoModal()" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Promo Baru
            </button>
        </div>

        {{-- Stats Summary --}}
        <div class="stats-grid mb-6">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="bi bi-tag"></i></div>
                <div>
                    <div class="stat-value">{{ $promos->total() }}</div>
                    <div class="stat-label">Total Promo</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="stat-value">{{ \App\Models\Promo::where('is_active', true)->count() }}</div>
                    <div class="stat-label">Promo Aktif</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-pause-circle"></i></div>
                <div>
                    <div class="stat-value">{{ \App\Models\Promo::where('is_active', false)->count() }}</div>
                    <div class="stat-label">Promo Nonaktif</div>
                </div>
            </div>
        </div>

        {{-- Card Table --}}
        <div class="card">
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--space-4);">
                    <h2 style="font-size:var(--font-size-base);font-weight:700;margin:0;">
                        Daftar Banner Promo
                    </h2>
                </div>

                @if($promos->count() > 0)
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width:110px;">Banner</th>
                                <th>Judul Promo</th>
                                <th>Link Aksi</th>
                                <th>Periode Tayang</th>
                                <th>Status</th>
                                <th style="text-align:right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($promos as $promo)
                            <tr>
                                <td>
                                    @if($promo->image)
                                        <a href="{{ asset('storage/' . $promo->image) }}" target="_blank" title="Lihat banner ukuran penuh">
                                            <img src="{{ asset('storage/' . $promo->image) }}" alt="{{ $promo->title }}" style="width:90px;height:55px;object-fit:cover;border-radius:8px;border:1px solid var(--gray-200);">
                                        </a>
                                    @else
                                        <div style="width:90px;height:55px;background:var(--gray-100);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--gray-400);font-size:11px;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight:700;color:var(--gray-900);">{{ $promo->title }}</div>
                                    <div style="font-size:var(--font-size-xs);color:var(--gray-500);margin-top:2px;max-width:320px;line-height:1.4;">
                                        {{ Str::limit($promo->description ?? 'Tidak ada deskripsi', 110) }}
                                    </div>
                                </td>
                                <td>
                                    @if($promo->link)
                                        <a href="{{ $promo->link }}" target="_blank" class="btn btn-ghost btn-sm" style="font-size:12px;padding:3px 8px;" title="{{ $promo->link }}">
                                            <i class="bi bi-box-arrow-up-right"></i> Buka Link
                                        </a>
                                    @else
                                        <span class="text-muted" style="font-size:12px;">—</span>
                                    @endif
                                </td>
                                <td style="font-size:var(--font-size-xs);white-space:nowrap;line-height:1.5;">
                                    @if($promo->starts_at && $promo->ends_at)
                                        {{ $promo->starts_at->translatedFormat('d M Y') }}<br>
                                        <span class="text-muted">s/d</span> {{ $promo->ends_at->translatedFormat('d M Y') }}
                                    @elseif($promo->starts_at)
                                        Mulai {{ $promo->starts_at->translatedFormat('d M Y') }}
                                    @elseif($promo->ends_at)
                                        Hingga {{ $promo->ends_at->translatedFormat('d M Y') }}
                                    @else
                                        <span class="text-muted">Selalu Tayang</span>
                                    @endif
                                </td>
                                <td>
                                    @if($promo->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-gray">Nonaktif</span>
                                    @endif
                                </td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <div style="display:inline-flex;gap:4px;align-items:center;">
                                        {{-- Edit Button --}}
                                        <button type="button" class="btn btn-ghost btn-sm" onclick='openEditPromoModal(@json($promo))' title="Edit Promo">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        {{-- Toggle Active Form --}}
                                        <form action="{{ route('admin.promos.update', $promo) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="title" value="{{ $promo->title }}">
                                            <input type="hidden" name="description" value="{{ $promo->description }}">
                                            <input type="hidden" name="link" value="{{ $promo->link }}">
                                            <input type="hidden" name="starts_at" value="{{ $promo->starts_at ? $promo->starts_at->format('Y-m-d') : '' }}">
                                            <input type="hidden" name="ends_at" value="{{ $promo->ends_at ? $promo->ends_at->format('Y-m-d') : '' }}">
                                            <input type="hidden" name="is_active" value="{{ $promo->is_active ? '0' : '1' }}">
                                            <button type="submit" class="btn btn-ghost btn-sm" title="{{ $promo->is_active ? 'Nonaktifkan Promo' : 'Aktifkan Promo' }}">
                                                <i class="bi bi-toggle-{{ $promo->is_active ? 'on text-success' : 'off text-muted' }}" style="font-size:16px;"></i>
                                            </button>
                                        </form>

                                        {{-- Delete Form --}}
                                        <form action="{{ route('admin.promos.destroy', $promo) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus promo \'{{ $promo->title }}\'?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm text-danger" title="Hapus Promo">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $promos->links() }}
                </div>
                @else
                <div style="text-align:center;padding:48px 20px;">
                    <i class="bi bi-tag" style="font-size:3rem;color:var(--gray-300);"></i>
                    <h3 style="font-size:16px;font-weight:700;margin:12px 0 4px;color:var(--gray-800);">Belum Ada Promo</h3>
                    <p style="font-size:13px;color:var(--gray-500);margin-bottom:16px;">Klik tombol di bawah untuk membuat promo atau banner promosi pertama.</p>
                    <button type="button" onclick="openAddPromoModal()" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Tambah Promo Baru
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH PROMO --}}
<div id="addPromoModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div class="card" style="max-width:540px;width:100%;max-height:90vh;overflow-y:auto;border-radius:16px;box-shadow:0 20px 40px rgba(0,0,0,0.25);">
        <div class="card-body" style="padding:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:1px solid var(--gray-200);padding-bottom:12px;">
                <h3 style="font-size:18px;font-weight:800;margin:0;color:var(--gray-900);">
                    <i class="bi bi-plus-circle" style="color:var(--primary);"></i> Tambah Promo Baru
                </h3>
                <button type="button" onclick="closeAddPromoModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--gray-400);line-height:1;">&times;</button>
            </div>

            <form action="{{ route('admin.promos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group mb-3">
                    <label class="form-label">Judul Promo <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: Promo Cashback Wedding 10%" required>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Deskripsi Promo</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan detail keuntungan dan syarat ketentuan promo..."></textarea>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Gambar Banner Promo</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <div class="form-help" style="font-size:11.5px;color:var(--gray-500);margin-top:4px;">
                        Format JPG, PNG, atau WebP. Maksimal 2MB. Rasio lanskap disarankan.
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Link Aksi (Opsional)</label>
                    <input type="url" name="link" class="form-control" placeholder="https://pestago.id/layanan atau link eksternal">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="mb-4">
                    <div class="form-group">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="starts_at" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Berakhir</label>
                        <input type="date" name="ends_at" class="form-control">
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid var(--gray-200);padding-top:16px;">
                    <button type="button" onclick="closeAddPromoModal()" class="btn btn-ghost">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Simpan Promo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT PROMO --}}
<div id="editPromoModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div class="card" style="max-width:540px;width:100%;max-height:90vh;overflow-y:auto;border-radius:16px;box-shadow:0 20px 40px rgba(0,0,0,0.25);">
        <div class="card-body" style="padding:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:1px solid var(--gray-200);padding-bottom:12px;">
                <h3 style="font-size:18px;font-weight:800;margin:0;color:var(--gray-900);">
                    <i class="bi bi-pencil" style="color:var(--primary);"></i> Edit Data Promo
                </h3>
                <button type="button" onclick="closeEditPromoModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--gray-400);line-height:1;">&times;</button>
            </div>

            <form id="editPromoForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label class="form-label">Judul Promo <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="edit_title" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Deskripsi Promo</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Ganti Gambar Banner</label>
                    <div id="edit_image_preview" style="margin-bottom:8px;"></div>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <div class="form-help" style="font-size:11.5px;color:var(--gray-500);margin-top:4px;">
                        Biarkan kosong jika tidak ingin mengubah banner saat ini.
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Link Aksi (Opsional)</label>
                    <input type="url" name="link" id="edit_link" class="form-control">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="mb-3">
                    <div class="form-group">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="starts_at" id="edit_starts_at" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Berakhir</label>
                        <input type="date" name="ends_at" id="edit_ends_at" class="form-control">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Status Tayang</label>
                    <select name="is_active" id="edit_is_active" class="form-control">
                        <option value="1">Aktif (Ditampilkan ke Pengunjung)</option>
                        <option value="0">Nonaktif (Disembunyikan)</option>
                    </select>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid var(--gray-200);padding-top:16px;">
                    <button type="button" onclick="closeEditPromoModal()" class="btn btn-ghost">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Perbarui Promo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddPromoModal() {
    document.getElementById('addPromoModal').style.display = 'flex';
}
function closeAddPromoModal() {
    document.getElementById('addPromoModal').style.display = 'none';
}

function openEditPromoModal(promo) {
    const form = document.getElementById('editPromoForm');
    form.action = `/admin/promos/${promo.id}`;

    document.getElementById('edit_title').value = promo.title || '';
    document.getElementById('edit_description').value = promo.description || '';
    document.getElementById('edit_link').value = promo.link || '';
    document.getElementById('edit_starts_at').value = promo.starts_at ? promo.starts_at.substring(0, 10) : '';
    document.getElementById('edit_ends_at').value = promo.ends_at ? promo.ends_at.substring(0, 10) : '';
    document.getElementById('edit_is_active').value = promo.is_active ? '1' : '0';

    const preview = document.getElementById('edit_image_preview');
    if (promo.image) {
        preview.innerHTML = `<img src="/storage/${promo.image}" style="max-height:80px;border-radius:6px;border:1px solid #cbd5e1;object-fit:cover;">`;
    } else {
        preview.innerHTML = '';
    }

    document.getElementById('editPromoModal').style.display = 'flex';
}

function closeEditPromoModal() {
    document.getElementById('editPromoModal').style.display = 'none';
}

// Close on outside click
window.addEventListener('click', function(e) {
    const addModal = document.getElementById('addPromoModal');
    const editModal = document.getElementById('editPromoModal');
    if (e.target === addModal) addModal.style.display = 'none';
    if (e.target === editModal) editModal.style.display = 'none';
});
</script>
@endsection
