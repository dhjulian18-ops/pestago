{{-- Modal Konfirmasi Transfer Manual (Approve) & Tolak (Reject) --}}

{{-- 1. Modal Approve Transfer --}}
<div id="approveWithdrawModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.65);z-index:99999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#ffffff;max-width:480px;width:100%;border:1px solid #111111;box-shadow:0 12px 36px rgba(0,0,0,0.2);">
        <div style="padding:16px 20px;border-bottom:1px solid #eeeeee;display:flex;align-items:center;justify-content:space-between;background:#fafafa;">
            <div style="font-size:13px;font-weight:900;letter-spacing:0.04em;text-transform:uppercase;color:#111111;display:flex;align-items:center;gap:8px;">
                <i class="bi bi-check2-circle" style="color:#16a34a;font-size:16px;"></i> Konfirmasi Transfer Penarikan
            </div>
            <button type="button" onclick="closeApproveModal()" style="background:none;border:none;font-size:22px;color:#888888;cursor:pointer;line-height:1;">&times;</button>
        </div>

        <form id="approveWithdrawForm" action="" method="POST" enctype="multipart/form-data" style="margin:0;">
            @csrf
            <div style="padding:22px;">
                {{-- Info Box --}}
                <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:14px;margin-bottom:16px;">
                    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Detail Penarikan Dana</div>
                    <div style="font-size:18px;font-weight:900;color:#D81A24;margin-top:4px;" id="approveModalAmount">
                        Rp 0
                    </div>
                    <div style="font-size:12px;color:#1e293b;margin-top:6px;line-height:1.4;" id="approveModalBank">
                        -
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-top:4px;" id="approveModalTrx">
                        Kode: -
                    </div>
                </div>

                <div style="font-size:12px;color:#555555;margin-bottom:16px;line-height:1.5;">
                    Pastikan Anda telah melakukan transfer dana secara manual ke nomor rekening vendor di atas sebelum mengkonfirmasi.
                </div>

                {{-- Upload Bukti Transfer --}}
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:11px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;color:#111111;margin-bottom:6px;">
                        Unggah Bukti Transfer / Struk Bank <span style="font-weight:500;color:#888;">(Opsional)</span>
                    </label>
                    <input type="file" name="proof_image" accept="image/*" style="width:100%;font-size:12px;border:1px solid #d1d5db;padding:8px;">
                    <span style="font-size:10.5px;color:#888888;display:block;margin-top:4px;">Format JPG/PNG, maksimal 3MB</span>
                </div>

                {{-- Catatan / No Referensi --}}
                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:11px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;color:#111111;margin-bottom:6px;">
                        No. Referensi Transfer / Catatan Admin <span style="font-weight:500;color:#888;">(Opsional)</span>
                    </label>
                    <input type="text" name="admin_notes" placeholder="Contoh: Transfer via BCA No. Ref: 2026110901" style="width:100%;height:38px;border:1px solid #cccccc;padding:0 12px;font-size:12.5px;outline:none;">
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" onclick="closeApproveModal()" style="height:38px;padding:0 18px;background:#f5f5f5;border:1px solid #e5e5e5;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;color:#444444;cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit" style="height:38px;padding:0 22px;background:#16a34a;border:1px solid #16a34a;color:#ffffff;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                        <i class="bi bi-check-lg"></i> Ya, Sudah Ditransfer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 2. Modal Tolak Penarikan (Reject) --}}
<div id="rejectWithdrawModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.65);z-index:99999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#ffffff;max-width:480px;width:100%;border:1px solid #111111;box-shadow:0 12px 36px rgba(0,0,0,0.2);">
        <div style="padding:16px 20px;border-bottom:1px solid #eeeeee;display:flex;align-items:center;justify-content:space-between;background:#fff8f8;">
            <div style="font-size:13px;font-weight:900;letter-spacing:0.04em;text-transform:uppercase;color:#b91c1c;display:flex;align-items:center;gap:8px;">
                <i class="bi bi-x-circle-fill" style="color:#b91c1c;font-size:16px;"></i> Tolak Permintaan Penarikan
            </div>
            <button type="button" onclick="closeRejectModal()" style="background:none;border:none;font-size:22px;color:#888888;cursor:pointer;line-height:1;">&times;</button>
        </div>

        <form id="rejectWithdrawForm" action="" method="POST" style="margin:0;">
            @csrf
            <div style="padding:22px;">
                <div style="background:#fef2f2;border:1px solid #fecaca;padding:14px;color:#991b1b;font-size:12px;margin-bottom:16px;line-height:1.5;">
                    <strong>Perhatian:</strong> Jika Anda menolak permintaan ini, dana sebesar <strong id="rejectModalAmount">Rp 0</strong> akan <strong>langsung dikembalikan secara otomatis</strong> ke Saldo Tersedia vendor.
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block;font-size:11px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;color:#111111;margin-bottom:6px;">
                        Alasan Penolakan <span style="color:#D81A24;">*</span>
                    </label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Contoh: Nomor rekening bank tidak ditemukan / nama pemilik rekening tidak sesuai dengan identitas vendor." style="width:100%;border:1px solid #cccccc;padding:10px 12px;font-size:12.5px;outline:none;resize:vertical;"></textarea>
                    <span style="font-size:10.5px;color:#888888;display:block;margin-top:4px;">Alasan ini akan dikirimkan sebagai notifikasi langsung kepada vendor.</span>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" onclick="closeRejectModal()" style="height:38px;padding:0 18px;background:#f5f5f5;border:1px solid #e5e5e5;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;color:#444444;cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit" style="height:38px;padding:0 22px;background:#dc2626;border:1px solid #dc2626;color:#ffffff;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                        <i class="bi bi-arrow-counterclockwise"></i> Tolak & Kembalikan Saldo
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openApproveModal(id, trxNumber, amount, bankInfo) {
    var form = document.getElementById('approveWithdrawForm');
    form.action = "{{ url('/admin/wallets/withdrawals') }}/" + id + "/approve";
    document.getElementById('approveModalAmount').innerText = 'Rp ' + amount;
    document.getElementById('approveModalBank').innerHTML = '<strong>Tujuan:</strong> ' + bankInfo;
    document.getElementById('approveModalTrx').innerText = 'Kode Transaksi: ' + trxNumber;
    document.getElementById('approveWithdrawModal').style.display = 'flex';
}

function closeApproveModal() {
    document.getElementById('approveWithdrawModal').style.display = 'none';
}

function openRejectModal(id, trxNumber, amount) {
    var form = document.getElementById('rejectWithdrawForm');
    form.action = "{{ url('/admin/wallets/withdrawals') }}/" + id + "/reject";
    document.getElementById('rejectModalAmount').innerText = 'Rp ' + amount;
    document.getElementById('rejectWithdrawModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectWithdrawModal').style.display = 'none';
}

function copyAccountNumber(text, btn) {
    navigator.clipboard.writeText(text).then(function() {
        var original = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check"></i> Tersalin!';
        btn.style.color = '#16a34a';
        setTimeout(function() {
            btn.innerHTML = original;
            btn.style.color = '';
        }, 2000);
    });
}
</script>

