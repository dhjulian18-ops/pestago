@extends('layouts.app')
@section('title', 'Chat CS — ' . ($conversation->customer->name ?? 'Bantuan') . ' — Admin PestaGo')
@section('hide_footer', '1')

@section('content')
@php
    $isVendor = ($conversation->customer->role ?? '') === 'vendor';
@endphp

<div class="dashboard">
    @include('admin._sidebar', ['active' => 'chats'])

    <div class="dashboard-content" style="padding-bottom:24px;">
        {{-- Navigation Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
            <a href="{{ route('admin.chats.index') }}" class="btn" style="height:36px;padding:0 14px;background:#ffffff;border:1px solid #e5e5e5;color:#111111;font-size:12px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;border-radius:2px;display:inline-flex;align-items:center;gap:6px;box-shadow:none;">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Chat CS
            </a>
            <span class="uq-chat-tag" style="background:#ffffff;border:1px solid #e5e5e5;color:#111111;font-size:11px;padding:6px 12px;">
                <i class="bi bi-tag" style="color:#D81A24;"></i> Topik: {{ $conversation->support_subject ?: 'Bantuan Umum' }}
            </span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;min-height:calc(100vh - 180px);" class="admin-chat-grid">
            {{-- Main Chat Window --}}
            <div style="display:flex;flex-direction:column;background:#ffffff;border:1px solid #e5e5e5;border-radius:4px;overflow:hidden;">
                {{-- Header --}}
                <div style="padding:14px 20px;border-bottom:1px solid #e5e5e5;display:flex;align-items:center;gap:14px;background:#ffffff;">
                    <div class="uq-chat-avatar {{ $isVendor ? 'vendor' : 'customer' }}">
                        {{ strtoupper(substr($conversation->customer->name ?? '?', 0, 1)) }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <strong style="font-size:15px;color:#111111;font-weight:700;">{{ $conversation->customer->name ?? 'Pengguna' }}</strong>
                            <span class="uq-chat-tag {{ $isVendor ? 'vendor' : 'customer' }}">
                                {{ $isVendor ? 'Vendor' : 'Pelanggan' }}
                            </span>
                        </div>
                        <div style="font-size:12px;color:#767676;margin-top:2px;">
                            {{ $conversation->customer->email }} • {{ $conversation->customer->phone ?: 'Tidak ada nomor telp' }}
                        </div>
                    </div>
                </div>

                {{-- Messages List --}}
                <div id="chatMessages" style="flex:1;overflow-y:auto;padding:20px 24px;display:flex;flex-direction:column;gap:14px;background:#fafafa;">
                    @forelse($conversation->messages as $msg)
                    @php
                        $isAdminMsg = in_array($msg->sender->role ?? '', ['super_admin', 'admin']);
                    @endphp
                    <div class="uq-msg-row {{ $isAdminMsg ? 'self' : 'other' }}">
                        <div class="uq-msg-bubble">
                            @if($isAdminMsg)
                                <div style="font-size:11px;font-weight:700;color:#ffffff;opacity:0.85;margin-bottom:4px;display:flex;align-items:center;gap:4px;">
                                    <i class="bi bi-shield-check"></i> CS: {{ $msg->sender->name }}
                                </div>
                            @else
                                <div style="font-size:11px;font-weight:700;color:#111111;margin-bottom:4px;">
                                    {{ $msg->sender->name }}
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
                                    <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:{{ $isAdminMsg ? '#ffffff' : '#111111' }};text-decoration:underline;padding:4px 0;">
                                        <i class="bi bi-file-earmark-arrow-down"></i> Unduh Lampiran
                                    </a>
                                @endif
                            </div>
                            @endif
                        </div>

                        <div class="uq-msg-meta">
                            <span>{{ $msg->created_at->format('H:i') }}</span>
                            @if($isAdminMsg)
                                <i class="bi bi-check2-all" style="color:#D81A24;"></i>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center;color:#888888;margin:auto;padding:40px 20px;">
                        Belum ada pesan dalam percakapan bantuan ini.
                    </div>
                    @endforelse
                </div>

                {{-- Attachment Indicator --}}
                <div id="adminFileSelectedBar" style="display:none;padding:8px 20px;background:#f5f5f5;border-top:1px solid #e5e5e5;font-size:12px;color:#111111;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="bi bi-paperclip" style="color:#D81A24;"></i>
                        <span id="adminSelectedFileName" style="font-weight:600;"></span>
                    </div>
                    <button type="button" onclick="clearAdminAttachment()" style="background:none;border:none;color:#888888;cursor:pointer;font-size:14px;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>

                {{-- Reply Box --}}
                <div style="padding:14px 20px;border-top:1px solid #e5e5e5;background:#ffffff;">
                    <form action="{{ route('admin.chats.reply', $conversation) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div style="display:flex;gap:10px;align-items:flex-end;">
                            <input type="file" name="attachment" id="adminAttachment" style="display:none;" onchange="handleAdminFile(this)">
                            
                            <button type="button" onclick="document.getElementById('adminAttachment').click()" class="uq-room-btn-clip" title="Lampirkan File">
                                <i class="bi bi-paperclip"></i>
                            </button>

                            <textarea name="message" class="uq-room-textarea" rows="2" placeholder="Ketik balasan Customer Care PestaGo..." required style="height:44px;min-height:44px;" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();if(this.value.trim().length>0){this.form.submit();}}"></textarea>

                            <button type="submit" class="uq-room-btn-send">
                                <i class="bi bi-send-fill"></i>
                                <span>Balas</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sidebar Info Pengguna --}}
            <div style="display:flex;flex-direction:column;gap:16px;">
                {{-- Data Pengguna --}}
                <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:4px;padding:20px;">
                    <h4 style="font-weight:800;margin:0 0 16px;font-size:13px;letter-spacing:0.5px;text-transform:uppercase;color:#111111;display:flex;align-items:center;gap:8px;">
                        <i class="bi bi-person-lines-fill" style="color:#D81A24;"></i> Informasi Pengguna
                    </h4>

                    <div style="font-size:13px;display:grid;gap:12px;color:#333333;">
                        <div>
                            <div style="font-size:11px;color:#767676;text-transform:uppercase;font-weight:600;margin-bottom:2px;">Nama Lengkap</div>
                            <div style="font-weight:700;color:#111111;">{{ $conversation->customer->name }}</div>
                        </div>

                        <div>
                            <div style="font-size:11px;color:#767676;text-transform:uppercase;font-weight:600;margin-bottom:2px;">Email</div>
                            <div>{{ $conversation->customer->email }}</div>
                        </div>

                        <div>
                            <div style="font-size:11px;color:#767676;text-transform:uppercase;font-weight:600;margin-bottom:2px;">Nomor Telepon</div>
                            <div>{{ $conversation->customer->phone ?: '-' }}</div>
                        </div>

                        <div>
                            <div style="font-size:11px;color:#767676;text-transform:uppercase;font-weight:600;margin-bottom:4px;">Tipe Akun</div>
                            <div>
                                <span class="uq-chat-tag {{ $isVendor ? 'vendor' : 'customer' }}">
                                    {{ ucfirst($conversation->customer->role) }}
                                </span>
                            </div>
                        </div>

                        @if($conversation->customer->vendorProfile)
                        <div style="margin-top:4px;padding-top:12px;border-top:1px solid #eeeeee;">
                            <div style="font-size:11px;color:#767676;text-transform:uppercase;font-weight:600;margin-bottom:2px;">Nama Usaha / Toko</div>
                            <div style="font-weight:700;color:#111111;">{{ $conversation->customer->vendorProfile->business_name }}</div>
                            <div style="margin-top:4px;">
                                <span class="badge {{ $conversation->customer->vendorProfile->status === 'active' ? 'badge-success' : 'badge-warning' }}" style="font-size:10px;">
                                    Status: {{ strtoupper($conversation->customer->vendorProfile->status) }}
                                </span>
                            </div>
                            <div style="margin-top:10px;">
                                <a href="{{ route('admin.vendors.show', $conversation->customer->vendorProfile) }}" class="btn" style="width:100%;height:32px;padding:0 12px;background:#ffffff;border:1px solid #111111;color:#111111;border-radius:2px;font-size:11px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;display:inline-flex;align-items:center;justify-content:center;gap:6px;box-shadow:none;">
                                    <i class="bi bi-box-arrow-up-right"></i> Profil Toko
                                </a>
                            </div>
                        </div>
                        @endif

                        <div style="margin-top:4px;padding-top:12px;border-top:1px solid #eeeeee;font-size:11px;color:#888888;">
                            Terdaftar sejak: {{ $conversation->customer->created_at->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </div>

                {{-- Status Penanganan CS --}}
                <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:4px;padding:20px;">
                    <h4 style="font-weight:800;margin:0 0 12px;font-size:13px;letter-spacing:0.5px;text-transform:uppercase;color:#111111;display:flex;align-items:center;gap:8px;">
                        <i class="bi bi-headset" style="color:#111111;"></i> Staf Penanggung Jawab
                    </h4>
                    <div style="font-size:13px;">
                        @if($conversation->admin)
                            <strong style="color:#111111;display:block;">{{ $conversation->admin->name }}</strong>
                            <div style="font-size:12px;color:#767676;margin-top:2px;">{{ $conversation->admin->admin_role_title ?: 'Staf Customer Care' }}</div>
                        @else
                            <span style="color:#888888;font-size:12px;">Belum ada staf yang merespons.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function handleAdminFile(input) {
        const bar = document.getElementById('adminFileSelectedBar');
        const nameEl = document.getElementById('adminSelectedFileName');
        if (input.files && input.files[0]) {
            nameEl.textContent = input.files[0].name;
            bar.style.display = 'flex';
        } else {
            bar.style.display = 'none';
        }
    }

    function clearAdminAttachment() {
        const input = document.getElementById('adminAttachment');
        input.value = '';
        document.getElementById('adminFileSelectedBar').style.display = 'none';
    }

    // Auto scroll chat to bottom
    const chatBox = document.getElementById('chatMessages');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>
@endpush
@endsection
