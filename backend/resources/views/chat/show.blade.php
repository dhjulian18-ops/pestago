@extends('layouts.app')
@section('title', 'Chat — PestaGo')
@section('hide_footer', '1')

@section('content')
@php
    $isSupport = $conversation->isSupport();
    $role = auth()->user()->role;
@endphp

<div class="uq-room-wrapper">
    <div class="uq-room-card">
        {{-- Chat Room Header --}}
        <div class="uq-room-header">
            <a href="{{ route('chat.index') }}" class="uq-room-back-btn" title="Kembali ke Daftar Chat">
                <i class="bi bi-arrow-left"></i>
            </a>

            @if($isSupport)
                <div class="uq-chat-avatar support">
                    <i class="bi bi-headset"></i>
                </div>
            @elseif($role === 'vendor')
                <div class="uq-chat-avatar customer">
                    {{ strtoupper(substr($conversation->customer->name ?? '?', 0, 1)) }}
                </div>
            @else
                <div class="uq-chat-avatar vendor">
                    {{ strtoupper(substr($conversation->vendorProfile->business_name ?? '?', 0, 1)) }}
                </div>
            @endif

            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <strong style="font-size:15px;color:#111111;font-weight:700;">
                        @if($isSupport)
                            @if(auth()->user()->isAdmin())
                                {{ $conversation->customer->name ?? 'Pengguna' }}
                            @else
                                Customer Care PestaGo
                            @endif
                        @elseif($role === 'vendor')
                            {{ $conversation->customer->name ?? 'Customer' }}
                        @else
                            {{ $conversation->vendorProfile->business_name ?? 'Vendor' }}
                        @endif
                    </strong>

                    @if($isSupport)
                        @if(!auth()->user()->isAdmin())
                            <span class="uq-chat-tag official"><i class="bi bi-patch-check-fill"></i> Official CS</span>
                        @else
                            <span class="uq-chat-tag {{ $conversation->customer->role === 'vendor' ? 'vendor' : 'customer' }}">
                                {{ ucfirst($conversation->customer->role) }}
                            </span>
                        @endif
                    @elseif($role === 'vendor')
                        <span class="uq-chat-tag customer">Pelanggan</span>
                    @else
                        <span class="uq-chat-tag vendor">Vendor Terverifikasi</span>
                    @endif
                </div>

                <div style="font-size:12px;color:#767676;margin-top:2px;display:flex;align-items:center;gap:8px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    @if($isSupport)
                        <span><i class="bi bi-tag"></i> Topik: {{ $conversation->support_subject ?: 'Pusat Bantuan & Layanan' }}</span>
                    @elseif($conversation->orderItem && $conversation->orderItem->service)
                        <span><i class="bi bi-box-seam"></i> Layanan: {{ $conversation->orderItem->service->name }}</span>
                    @else
                        <span style="color:#059669;display:inline-flex;align-items:center;gap:4px;">
                            <span style="width:6px;height:6px;border-radius:50%;background:#059669;"></span> Aktif
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Messages Stream --}}
        <div id="chatMessages" class="uq-room-messages">
            @forelse($conversation->messages as $msg)
            @php
                $isSelf = $msg->sender_id === auth()->id();
                $isMsgFromAdmin = in_array($msg->sender->role ?? '', ['super_admin', 'admin']);
            @endphp
            <div class="uq-msg-row {{ $isSelf ? 'self' : 'other' }}">
                <div class="uq-msg-bubble">
                    @if($isMsgFromAdmin && !$isSelf)
                        <div style="font-size:11px;font-weight:700;color:#D81A24;margin-bottom:4px;display:flex;align-items:center;gap:4px;">
                            <i class="bi bi-patch-check-fill"></i> Customer Care PestaGo
                        </div>
                    @endif

                    <p style="margin:0;white-space:pre-wrap;">{{ $msg->message }}</p>

                    @if($msg->attachment)
                    <div style="margin-top:8px;">
                        @if(in_array(pathinfo($msg->attachment, PATHINFO_EXTENSION), ['jpg','jpeg','png','gif','webp']))
                            <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" style="display:inline-block;">
                                <img src="{{ asset('storage/' . $msg->attachment) }}" alt="Lampiran" style="max-width:240px;border-radius:2px;border:1px solid #e5e5e5;display:block;">
                            </a>
                        @else
                            <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:{{ $isSelf ? '#ffffff' : '#111111' }};text-decoration:underline;padding:4px 0;">
                                <i class="bi bi-file-earmark-arrow-down"></i> Unduh Lampiran
                            </a>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="uq-msg-meta">
                    <span>{{ $msg->created_at->format('H:i') }}</span>
                    @if($isSelf)
                        @if($msg->is_read)
                            <i class="bi bi-check2-all" style="color:#D81A24;" title="Sudah dibaca"></i>
                        @else
                            <i class="bi bi-check2" title="Terkirim"></i>
                        @endif
                    @endif
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:60px 20px;color:#888888;margin:auto;">
                <div style="width:48px;height:48px;border-radius:4px;background:#f0f0f0;display:inline-flex;align-items:center;justify-content:center;font-size:22px;color:#888888;margin-bottom:12px;">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <div style="font-weight:700;color:#111111;font-size:14px;margin-bottom:4px;">Mulai Percakapan</div>
                <div style="font-size:12px;color:#767676;max-width:320px;margin:0 auto;">Ketik pesan Anda di bawah ini untuk memulai percakapan secara langsung.</div>
            </div>
            @endforelse
        </div>

        {{-- Attachment Indicator (shown when file is selected) --}}
        <div id="fileSelectedBar" style="display:none;padding:8px 20px;background:#f5f5f5;border-top:1px solid #e5e5e5;font-size:12px;color:#111111;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:8px;">
                <i class="bi bi-paperclip" style="color:#D81A24;"></i>
                <span id="selectedFileName" style="font-weight:600;"></span>
            </div>
            <button type="button" onclick="clearAttachment()" style="background:none;border:none;color:#888888;cursor:pointer;font-size:14px;">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>

        {{-- Message Input Footer --}}
        <div class="uq-room-footer">
            <form action="{{ route('chat.send', $conversation) }}" method="POST" enctype="multipart/form-data" class="uq-room-form" id="chatForm">
                @csrf
                <input type="file" name="attachment" id="chatAttachment" style="display:none;" onchange="handleFileSelected(this)">
                
                <button type="button" onclick="document.getElementById('chatAttachment').click()" class="uq-room-btn-clip" title="Lampirkan File / Gambar">
                    <i class="bi bi-paperclip"></i>
                </button>

                <textarea name="message" id="chatInputMessage" class="uq-room-textarea" rows="1" placeholder="Tulis pesan Anda..." required onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();if(this.value.trim().length>0){this.form.submit();}}"></textarea>

                <button type="submit" class="uq-room-btn-send">
                    <i class="bi bi-send-fill"></i>
                    <span>Kirim</span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function handleFileSelected(input) {
        const bar = document.getElementById('fileSelectedBar');
        const nameEl = document.getElementById('selectedFileName');
        if (input.files && input.files[0]) {
            nameEl.textContent = input.files[0].name;
            bar.style.display = 'flex';
        } else {
            bar.style.display = 'none';
        }
    }

    function clearAttachment() {
        const input = document.getElementById('chatAttachment');
        input.value = '';
        document.getElementById('fileSelectedBar').style.display = 'none';
    }

    // Auto-scroll chat to bottom
    const chatBox = document.getElementById('chatMessages');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>
@endpush
@endsection
