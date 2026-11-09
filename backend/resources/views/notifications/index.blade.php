@extends('layouts.app')
@section('title', 'Notifikasi — PestaGo')
@section('content')
<div style="padding-top:calc(var(--navbar-height) + var(--space-8));">
    <div class="container" style="max-width:800px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--space-6);">
            <h1 style="font-size:var(--font-size-2xl);font-weight:800;">
                <i class="bi bi-bell" style="color:var(--primary);"></i> Notifikasi
            </h1>
            <form action="{{ route('notifications.readAll') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm">
                    <i class="bi bi-check2-all"></i> Tandai Semua Dibaca
                </button>
            </form>
        </div>

        @if($notifications->isEmpty())
            <div class="card"><div class="card-body" style="text-align:center;padding:var(--space-12);">
                <i class="bi bi-bell-slash" style="font-size:3rem;color:var(--gray-300);"></i>
                <p style="color:var(--gray-500);margin-top:var(--space-4);">Belum ada notifikasi.</p>
            </div></div>
        @else
            <div style="display:grid;gap:var(--space-3);">
                @foreach($notifications as $notif)
                <div class="card notif-card {{ !$notif->is_read ? 'notif-unread' : '' }}">
                    <div class="card-body" style="display:flex;gap:var(--space-4);align-items:flex-start;">
                        <div class="notif-icon-wrap">
                            @php
                                $icons = [
                                    'order_confirmed' => ['bi-check-circle-fill', 'success'],
                                    'order_rejected' => ['bi-x-circle-fill', 'danger'],
                                    'order_cancelled' => ['bi-x-circle-fill', 'warning'],
                                    'order_completed' => ['bi-trophy-fill', 'success'],
                                    'payment_confirmed' => ['bi-credit-card-fill', 'success'],
                                    'payment_rejected' => ['bi-credit-card-fill', 'danger'],
                                    'payment_submitted' => ['bi-upload', 'info'],
                                    'vendor_verification' => ['bi-shield-check', 'primary'],
                                    'new_review' => ['bi-star-fill', 'warning'],
                                    'new_message' => ['bi-chat-dots-fill', 'info'],
                                    'refund_approved' => ['bi-arrow-counterclockwise', 'success'],
                                    'refund_rejected' => ['bi-arrow-counterclockwise', 'danger'],
                                ];
                                $ic = $icons[$notif->type] ?? ['bi-bell-fill', 'gray'];
                            @endphp
                            <span class="notif-icon notif-icon-{{ $ic[1] }}"><i class="bi {{ $ic[0] }}"></i></span>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:var(--space-2);">
                                <strong style="font-size:var(--font-size-sm);">{{ $notif->title }}</strong>
                                <span style="font-size:var(--font-size-xs);color:var(--gray-400);white-space:nowrap;">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                            <p style="font-size:var(--font-size-sm);color:var(--gray-600);margin:var(--space-1) 0 0;">{{ $notif->message }}</p>
                            <div style="display:flex;gap:var(--space-2);margin-top:var(--space-2);">
                                @if(!$notif->is_read)
                                <form action="{{ route('notifications.read', $notif) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-ghost" style="font-size:var(--font-size-xs);padding:2px 8px;">
                                        <i class="bi bi-check2"></i> Dibaca
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('notifications.destroy', $notif) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost" style="font-size:var(--font-size-xs);padding:2px 8px;color:var(--gray-400);">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div style="margin-top:var(--space-6);">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
<div style="padding-bottom:var(--space-16);"></div>
@endsection
