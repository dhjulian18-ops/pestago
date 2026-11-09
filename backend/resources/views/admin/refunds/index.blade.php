@extends('layouts.app')
@section('title', 'Refund — Admin PestaGo')
@section('content')
    <div class="dashboard">
        @include('admin._sidebar', ['active' => 'refunds'])
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Manajemen Refund</h1>
            </div>

            {{-- Filter Status --}}
            <div style="display:flex;gap:var(--space-2);margin-bottom:var(--space-6);flex-wrap:wrap;">
                <a href="{{ route('admin.refunds') }}"
                    class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-ghost' }}">Semua</a>
                <a href="{{ route('admin.refunds', ['status' => 'pending']) }}"
                    class="btn btn-sm {{ $status == 'pending' ? 'btn-primary' : 'btn-ghost' }}">Menunggu</a>
                <a href="{{ route('admin.refunds', ['status' => 'approved']) }}"
                    class="btn btn-sm {{ $status == 'approved' ? 'btn-primary' : 'btn-ghost' }}">Disetujui</a>
                <a href="{{ route('admin.refunds', ['status' => 'rejected']) }}"
                    class="btn btn-sm {{ $status == 'rejected' ? 'btn-primary' : 'btn-ghost' }}">Ditolak</a>
            </div>

            {{-- Refund Table --}}
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Refund</th>
                            <th>Customer</th>
                            <th>Order #</th>
                            <th>Layanan</th>
                            <th>Jumlah</th>
                            <th>Alasan</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($refunds as $refund)
                            <tr>
                                <td style="font-weight:600;">
                                    {{ $refund->refund_number ?? 'RF-' . str_pad($refund->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:var(--space-2);">
                                        <span class="user-avatar"
                                            style="width:32px;height:32px;font-size:var(--font-size-xs);">{{ strtoupper(substr($refund->order->user->name ?? 'U', 0, 1)) }}</span>
                                        <div>
                                            <div style="font-weight:500;">{{ $refund->order->user->name ?? '-' }}</div>
                                            <div style="font-size:var(--font-size-xs);color:var(--gray-400);">
                                                {{ $refund->order->user->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $refund->order_id) }}" class="text-primary"
                                        style="font-weight:500;">
                                        {{ $refund->order->order_number ?? '-' }}
                                    </a>
                                </td>
                                <td>{{ $refund->orderItem->service_name_snapshot ?? '-' }}</td>
                                <td style="font-weight:600;color:var(--primary);">Rp
                                    {{ number_format($refund->amount, 0, ',', '.') }}</td>
                                <td>
                                    <span
                                        style="max-width:200px;display:inline-block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                        title="{{ $refund->reason }}">
                                        {{ \Illuminate\Support\Str::limit($refund->reason, 50) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusMap = [
                                            'pending' => ['Menunggu', 'warning'],
                                            'approved' => ['Disetujui', 'success'],
                                            'rejected' => ['Ditolak', 'danger'],
                                            'processing' => ['Diproses', 'info'],
                                        ];
                                        $s = $statusMap[$refund->status] ?? [$refund->status, 'gray'];
                                    @endphp
                                    <span class="badge badge-{{ $s[1] }}">{{ $s[0] }}</span>
                                </td>
                                <td style="white-space:nowrap;">{{ $refund->created_at->translatedFormat('d M Y') }}</td>
                                <td>
                                    @if($refund->status === 'pending')
                                        <div style="display:flex;gap:var(--space-1);align-items:center;">
                                            {{-- Approve Form --}}
                                            <form action="{{ route('admin.refunds.process', $refund) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-sm btn-success"
                                                    onclick="return confirm('Setujui refund ini?')" title="Setujui">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                            {{-- Reject with notes (opens a mini modal) --}}
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="toggleRefundReject({{ $refund->id }})" title="Tolak">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                        {{-- Reject Form (hidden) --}}
                                        <div id="rejectForm{{ $refund->id }}" style="display:none;margin-top:var(--space-2);">
                                            <form action="{{ route('admin.refunds.process', $refund) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <textarea name="admin_notes" placeholder="Alasan penolakan..." rows="2"
                                                    class="form-control"
                                                    style="font-size:var(--font-size-xs);margin-bottom:var(--space-1);min-height:60px;"></textarea>
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Tolak refund ini?')">Tolak Refund</button>
                                            </form>
                                        </div>
                                    @elseif($refund->status === 'approved')
                                        <span style="color:var(--success);font-size:var(--font-size-sm);"><i
                                                class="bi bi-check-circle-fill"></i> Selesai</span>
                                    @elseif($refund->status === 'rejected')
                                        <span style="color:var(--danger);font-size:var(--font-size-sm);"
                                            title="{{ $refund->admin_notes }}"><i class="bi bi-x-circle-fill"></i> Ditolak</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted" style="padding:var(--space-8);">
                                    <div style="text-align:center;">
                                        <i class="bi bi-arrow-return-left" style="font-size:2rem;color:var(--gray-300);"></i>
                                        <p style="margin-top:var(--space-2);">Tidak ada data
                                            refund{{ $status ? ' dengan status "' . $status . '"' : '' }}.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $refunds->appends(request()->query())->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleRefundReject(id) {
            const el = document.getElementById('rejectForm' + id);
            if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
        }
    </script>
@endpush