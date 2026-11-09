import zipfile
import xml.etree.ElementTree as ET
import os

W_NS = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"
ET.register_namespace('w', W_NS)
ET.register_namespace('r', "http://schemas.openxmlformats.org/officeDocument/2006/relationships")
ET.register_namespace('wp', "http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing")
ET.register_namespace('a', "http://schemas.openxmlformats.org/drawingml/2006/main")
ET.register_namespace('pic', "http://schemas.openxmlformats.org/drawingml/2006/picture")

def w_tag(tag):
    return f"{{{W_NS}}}{tag}"

def make_p(runs_data, style=None, align="both", space_before=0, space_after=100, line_spacing=276):
    p = ET.Element(w_tag("p"))
    pPr = ET.SubElement(p, w_tag("pPr"))
    if style:
        pStyle = ET.SubElement(pPr, w_tag("pStyle"))
        pStyle.attrib[w_tag("val")] = style
    if align:
        jc = ET.SubElement(pPr, w_tag("jc"))
        jc.attrib[w_tag("val")] = align
    if space_before or space_after or line_spacing:
        spacing = ET.SubElement(pPr, w_tag("spacing"))
        if space_before:
            spacing.attrib[w_tag("before")] = str(space_before)
        if space_after:
            spacing.attrib[w_tag("after")] = str(space_after)
        if line_spacing:
            spacing.attrib[w_tag("line")] = str(line_spacing)
            spacing.attrib[w_tag("lineRule")] = "auto"
            
    for item in runs_data:
        text, bold, italic, sz, color, font = item
        r = ET.SubElement(p, w_tag("r"))
        rPr = ET.SubElement(r, w_tag("rPr"))
        
        rFonts = ET.SubElement(rPr, w_tag("rFonts"))
        font_name = font or "Times New Roman"
        rFonts.attrib[w_tag("ascii")] = font_name
        rFonts.attrib[w_tag("hAnsi")] = font_name
        rFonts.attrib[w_tag("cs")] = font_name
        
        if bold:
            ET.SubElement(rPr, w_tag("b"))
            ET.SubElement(rPr, w_tag("bCs"))
        if italic:
            ET.SubElement(rPr, w_tag("i"))
            ET.SubElement(rPr, w_tag("iCs"))
        if sz:
            sz_elem = ET.SubElement(rPr, w_tag("sz"))
            sz_elem.attrib[w_tag("val")] = str(sz)
            szCs_elem = ET.SubElement(rPr, w_tag("szCs"))
            szCs_elem.attrib[w_tag("val")] = str(sz)
        if color:
            color_elem = ET.SubElement(rPr, w_tag("color"))
            color_elem.attrib[w_tag("val")] = color
            
        t = ET.SubElement(r, w_tag("t"))
        t.text = text
        if text.startswith(" ") or text.endswith(" "):
            t.attrib["{http://www.w3.org/XML/1998/namespace}space"] = "preserve"
            
    return p

def make_h1(text):
    return make_p([(text, True, False, 28, "000000", "Times New Roman")],
                  style="Heading1", align="left", space_before=280, space_after=100)

def make_h2(text):
    return make_p([(text, True, False, 26, "000000", "Times New Roman")],
                  style="Heading2", align="left", space_before=200, space_after=60)

def make_body(text, bold_prefix=None, italic=False):
    runs = []
    if bold_prefix:
        runs.append((bold_prefix, True, False, 24, "000000", "Times New Roman"))
    runs.append((text, False, italic, 24, "000000", "Times New Roman"))
    return make_p(runs, style=None, align="both", space_before=0, space_after=90, line_spacing=276)

def make_bullet(label, description):
    p = ET.Element(w_tag("p"))
    pPr = ET.SubElement(p, w_tag("pPr"))
    pStyle = ET.SubElement(pPr, w_tag("pStyle"))
    pStyle.attrib[w_tag("val")] = "ListBullet"
    
    ind = ET.SubElement(pPr, w_tag("ind"))
    ind.attrib[w_tag("left")] = "540"
    ind.attrib[w_tag("hanging")] = "270"
    
    jc = ET.SubElement(pPr, w_tag("jc"))
    jc.attrib[w_tag("val")] = "both"
    
    spacing = ET.SubElement(pPr, w_tag("spacing"))
    spacing.attrib[w_tag("before")] = "0"
    spacing.attrib[w_tag("after")] = "50"
    spacing.attrib[w_tag("line")] = "276"
    spacing.attrib[w_tag("lineRule")] = "auto"
    
    r_sym = ET.SubElement(p, w_tag("r"))
    rPr_sym = ET.SubElement(r_sym, w_tag("rPr"))
    rFonts_sym = ET.SubElement(rPr_sym, w_tag("rFonts"))
    rFonts_sym.attrib[w_tag("ascii")] = "Times New Roman"
    sz_sym = ET.SubElement(rPr_sym, w_tag("sz"))
    sz_sym.attrib[w_tag("val")] = "24"
    t_sym = ET.SubElement(r_sym, w_tag("t"))
    t_sym.text = "• "
    t_sym.attrib["{http://www.w3.org/XML/1998/namespace}space"] = "preserve"
    
    if label:
        r_lbl = ET.SubElement(p, w_tag("r"))
        rPr_lbl = ET.SubElement(r_lbl, w_tag("rPr"))
        ET.SubElement(rPr_lbl, w_tag("b"))
        sz_lbl = ET.SubElement(rPr_lbl, w_tag("sz"))
        sz_lbl.attrib[w_tag("val")] = "24"
        t_lbl = ET.SubElement(r_lbl, w_tag("t"))
        t_lbl.text = label + " "
        t_lbl.attrib["{http://www.w3.org/XML/1998/namespace}space"] = "preserve"
        
    r_desc = ET.SubElement(p, w_tag("r"))
    rPr_desc = ET.SubElement(r_desc, w_tag("rPr"))
    sz_desc = ET.SubElement(rPr_desc, w_tag("sz"))
    sz_desc.attrib[w_tag("val")] = "24"
    t_desc = ET.SubElement(r_desc, w_tag("t"))
    t_desc.text = description
    
    return p

def make_numbered_item(num_str, label, description):
    p = ET.Element(w_tag("p"))
    pPr = ET.SubElement(p, w_tag("pPr"))
    
    ind = ET.SubElement(pPr, w_tag("ind"))
    ind.attrib[w_tag("left")] = "540"
    ind.attrib[w_tag("hanging")] = "270"
    
    jc = ET.SubElement(pPr, w_tag("jc"))
    jc.attrib[w_tag("val")] = "both"
    
    spacing = ET.SubElement(pPr, w_tag("spacing"))
    spacing.attrib[w_tag("before")] = "20"
    spacing.attrib[w_tag("after")] = "50"
    spacing.attrib[w_tag("line")] = "276"
    spacing.attrib[w_tag("lineRule")] = "auto"
    
    r_num = ET.SubElement(p, w_tag("r"))
    rPr_num = ET.SubElement(r_num, w_tag("rPr"))
    ET.SubElement(rPr_num, w_tag("b"))
    sz_num = ET.SubElement(rPr_num, w_tag("sz"))
    sz_num.attrib[w_tag("val")] = "24"
    t_num = ET.SubElement(r_num, w_tag("t"))
    t_num.text = num_str + " "
    t_num.attrib["{http://www.w3.org/XML/1998/namespace}space"] = "preserve"
    
    if label:
        r_lbl = ET.SubElement(p, w_tag("r"))
        rPr_lbl = ET.SubElement(r_lbl, w_tag("rPr"))
        ET.SubElement(rPr_lbl, w_tag("b"))
        sz_lbl = ET.SubElement(rPr_lbl, w_tag("sz"))
        sz_lbl.attrib[w_tag("val")] = "24"
        t_lbl = ET.SubElement(r_lbl, w_tag("t"))
        t_lbl.text = label + " "
        t_lbl.attrib["{http://www.w3.org/XML/1998/namespace}space"] = "preserve"
        
    r_desc = ET.SubElement(p, w_tag("r"))
    rPr_desc = ET.SubElement(r_desc, w_tag("rPr"))
    sz_desc = ET.SubElement(rPr_desc, w_tag("sz"))
    sz_desc.attrib[w_tag("val")] = "24"
    t_desc = ET.SubElement(r_desc, w_tag("t"))
    t_desc.text = description
    
    return p

def make_table(headers, rows, col_widths=None):
    tbl = ET.Element(w_tag("tbl"))
    
    tblPr = ET.SubElement(tbl, w_tag("tblPr"))
    tblStyle = ET.SubElement(tblPr, w_tag("tblStyle"))
    tblStyle.attrib[w_tag("val")] = "TableGrid"
    
    tblW = ET.SubElement(tblPr, w_tag("tblW"))
    tblW.attrib[w_tag("w")] = "0"
    tblW.attrib[w_tag("type")] = "auto"
    
    jc = ET.SubElement(tblPr, w_tag("jc"))
    jc.attrib[w_tag("val")] = "center"
    
    tblBorders = ET.SubElement(tblPr, w_tag("tblBorders"))
    for b_name in ["top", "left", "bottom", "right", "insideH", "insideV"]:
        b = ET.SubElement(tblBorders, w_tag(b_name))
        b.attrib[w_tag("val")] = "single"
        b.attrib[w_tag("sz")] = "4"
        b.attrib[w_tag("space")] = "0"
        b.attrib[w_tag("color")] = "B0BEC5"
        
    tblGrid = ET.SubElement(tbl, w_tag("tblGrid"))
    for w in (col_widths or [2000] * len(headers)):
        gridCol = ET.SubElement(tblGrid, w_tag("gridCol"))
        gridCol.attrib[w_tag("w")] = str(w)
        
    tr_h = ET.SubElement(tbl, w_tag("tr"))
    trPr_h = ET.SubElement(tr_h, w_tag("trPr"))
    ET.SubElement(trPr_h, w_tag("tblHeader"))
    
    for i, h in enumerate(headers):
        tc = ET.SubElement(tr_h, w_tag("tc"))
        tcPr = ET.SubElement(tc, w_tag("tcPr"))
        if col_widths:
            tcW = ET.SubElement(tcPr, w_tag("tcW"))
            tcW.attrib[w_tag("w")] = str(col_widths[i])
            tcW.attrib[w_tag("type")] = "dxa"
        shd = ET.SubElement(tcPr, w_tag("shd"))
        shd.attrib[w_tag("val")] = "clear"
        shd.attrib[w_tag("color")] = "auto"
        shd.attrib[w_tag("fill")] = "E2E8F0"
        
        tcMar = ET.SubElement(tcPr, w_tag("tcMar"))
        for m in ["top", "bottom"]:
            sub_m = ET.SubElement(tcMar, w_tag(m))
            sub_m.attrib[w_tag("w")] = "100"
            sub_m.attrib[w_tag("type")] = "dxa"
        for m in ["left", "right"]:
            sub_m = ET.SubElement(tcMar, w_tag(m))
            sub_m.attrib[w_tag("w")] = "120"
            sub_m.attrib[w_tag("type")] = "dxa"
            
        p = ET.SubElement(tc, w_tag("p"))
        pPr = ET.SubElement(p, w_tag("pPr"))
        jc_p = ET.SubElement(pPr, w_tag("jc"))
        jc_p.attrib[w_tag("val")] = "center"
        spacing = ET.SubElement(pPr, w_tag("spacing"))
        spacing.attrib[w_tag("before")] = "30"
        spacing.attrib[w_tag("after")] = "30"
        
        r = ET.SubElement(p, w_tag("r"))
        rPr = ET.SubElement(r, w_tag("rPr"))
        ET.SubElement(rPr, w_tag("b"))
        sz = ET.SubElement(rPr, w_tag("sz"))
        sz.attrib[w_tag("val")] = "21"
        t = ET.SubElement(r, w_tag("t"))
        t.text = h
        
    for r_idx, row in enumerate(rows):
        tr = ET.SubElement(tbl, w_tag("tr"))
        for c_idx, val in enumerate(row):
            tc = ET.SubElement(tr, w_tag("tc"))
            tcPr = ET.SubElement(tc, w_tag("tcPr"))
            if col_widths:
                tcW = ET.SubElement(tcPr, w_tag("tcW"))
                tcW.attrib[w_tag("w")] = str(col_widths[c_idx])
                tcW.attrib[w_tag("type")] = "dxa"
            if r_idx % 2 == 1:
                shd = ET.SubElement(tcPr, w_tag("shd"))
                shd.attrib[w_tag("val")] = "clear"
                shd.attrib[w_tag("color")] = "auto"
                shd.attrib[w_tag("fill")] = "F8FAFC"
                
            tcMar = ET.SubElement(tcPr, w_tag("tcMar"))
            for m in ["top", "bottom"]:
                sub_m = ET.SubElement(tcMar, w_tag(m))
                sub_m.attrib[w_tag("w")] = "80"
                sub_m.attrib[w_tag("type")] = "dxa"
            for m in ["left", "right"]:
                sub_m = ET.SubElement(tcMar, w_tag(m))
                sub_m.attrib[w_tag("w")] = "120"
                sub_m.attrib[w_tag("type")] = "dxa"
                
            p = ET.SubElement(tc, w_tag("p"))
            pPr = ET.SubElement(p, w_tag("pPr"))
            spacing = ET.SubElement(pPr, w_tag("spacing"))
            spacing.attrib[w_tag("before")] = "25"
            spacing.attrib[w_tag("after")] = "25"
            if c_idx == 0:
                jc_cell = ET.SubElement(pPr, w_tag("jc"))
                jc_cell.attrib[w_tag("val")] = "center"
            
            r = ET.SubElement(p, w_tag("r"))
            rPr = ET.SubElement(r, w_tag("rPr"))
            sz = ET.SubElement(rPr, w_tag("sz"))
            sz.attrib[w_tag("val")] = "20"
            t = ET.SubElement(r, w_tag("t"))
            t.text = val
            
    return tbl

def make_page_break():
    p = ET.Element(w_tag("p"))
    r = ET.SubElement(p, w_tag("r"))
    br = ET.SubElement(r, w_tag("br"))
    br.attrib[w_tag("type")] = "page"
    return p

def generate_compact_pestago_proposal():
    template_path = "template_gtnic.docx"
    output_path = "Proposal_PestaGo_GTNIC_2026.docx"
    
    with zipfile.ZipFile(template_path, 'r') as zin:
        xml_content = zin.read('word/document.xml')
        root = ET.fromstring(xml_content)
        
        body = root.find(f"{{{W_NS}}}body")
        drawing_p = body[6]
        sectPr = body[-1]
        
        body.clear()
        
        # ==========================================
        # 0. COVER PAGE
        # ==========================================
        body.append(make_p([("PROPOSAL KARYA (UX CASE STUDY)", True, False, 30, "000000", "Times New Roman")],
                           align="center", space_before=120, space_after=80))
        body.append(make_p([("GARDA TECH NATIONAL INNOVATION COMPETITION (GTNIC) 2026", True, False, 26, "000000", "Times New Roman")],
                           align="center", space_before=0, space_after=200))
        
        body.append(make_p([("KATEGORI: WEB DEVELOPMENT & UX DESIGN", True, False, 24, "1E3A8A", "Times New Roman")],
                           align="center", space_before=0, space_after=60))
        body.append(make_p([("Sub-Tema: \"Pemberdayaan UMKM Kreatif Berbasis Platform Digital & Geolocation\"", True, True, 24, "333333", "Times New Roman")],
                           align="center", space_before=0, space_after=220))
        
        body.append(make_p([("JUDUL KARYA / APLIKASI WEB:", False, False, 22, "555555", "Times New Roman")],
                           align="center", space_before=0, space_after=60))
        body.append(make_p([("PESTAGO: SMART MARKETPLACE & EVENT DIRECTORY BERBASIS GEOLOCATION DAN SISTEM PERIKLANAN FLEKSIBEL UNTUK DIGITALISASI UMKM VENDOR PESTA INDONESIA", True, False, 26, "B91C1C", "Times New Roman")],
                           align="center", space_before=0, space_after=220))
        
        # GTNIC Logo Drawing
        body.append(drawing_p)
        
        body.append(make_p([("Disusun Oleh Tim:", True, False, 24, "000000", "Times New Roman")],
                           align="center", space_before=160, space_after=80))
        body.append(make_p([("TIM PESTAGO INOVASI", True, False, 26, "1E3A8A", "Times New Roman")],
                           align="center", space_before=0, space_after=140))
        
        team_members = [
            ("1. Dika Julian Anggara", " - Ketua Tim / Lead Full-Stack Developer"),
            ("2. Siti Nurhaliza", " - Anggota 1 / UI-UX Designer & Researcher"),
            ("3. Muhammad Fikri", " - Anggota 2 / Business Analyst & Database Engineer")
        ]
        for name, role in team_members:
            body.append(make_p([(name, True, False, 22, "000000", "Times New Roman"),
                                (role, False, False, 22, "333333", "Times New Roman")],
                               align="center", space_before=0, space_after=50))
                               
        body.append(make_page_break())
        
        # ==========================================
        # 1. LATAR BELAKANG MASALAH
        # ==========================================
        body.append(make_h1("1. LATAR BELAKANG MASALAH"))
        
        body.append(make_body(
            "Industri penyelenggaraan acara (event) dan pesta di Indonesia—seperti pernikahan, khitanan, ulang tahun, hingga gathering perusahaan—memiliki perputaran ekonomi kreatif mencapai puluhan triliun rupiah per tahun dan melibatkan ratusan ribu pelaku UMKM (katering, dekorasi, tata rias/MUA, fotografi, sound system, dan venue). Namun, proses temu-kenal antara konsumen dan vendor lokal di tingkat daerah saat ini masih menghadapi hambatan struktural yang nyata:"
        ))
        
        body.append(make_body("Permasalahan Utama dari Sisi Konsumen (Penyelenggara Acara):", bold_prefix=None, italic=True))
        body.append(make_numbered_item(
            "1.", "Fragmentasi Informasi & Asimetri Pasar:",
            "Konsumen kesulitan mencari vendor terpercaya secara cepat. Informasi tersebar sporadis di media sosial tanpa standarisasi harga, portfolio terverifikasi, atau kepastian jadwal ketersediaan."
        ))
        body.append(make_numbered_item(
            "2.", "Kendala Jarak Geografis & Risiko Logistik (Geolocation):",
            "Layanan pesta sarat dengan mobilitas fisik. Memilih vendor katering atau dekorasi yang lokasinya terlalu jauh meningkatkan risiko keterlambatan pengiriman makanan, penurunan mutu rasa, dan pembengkakan biaya ongkos kirim."
        ))
        body.append(make_numbered_item(
            "3.", "Risiko Penipuan & Transaksi Tanpa Jaminan (Ghosting):",
            "Maraknya kasus vendor bodong, uang muka (DP) dibawa kabur, atau pembatalan sepihak tanpa mekanisme perlindungan dana yang jelas membuat konsumen merasa cemas dan rentan dirugikan."
        ))
        
        body.append(make_body("Permasalahan Utama dari Sisi Pelaku UMKM Vendor Pesta:", bold_prefix=None, italic=True))
        body.append(make_numbered_item(
            "1.", "Biaya Promosi Portal Besar yang Terlalu Mahal:",
            "Portal direktori komersial yang ada mematok biaya paket langganan jutaan rupiah per bulan, menutup kesempatan bagi UMKM vendor lokal yang bermodal promosi terbatas."
        ))
        body.append(make_numbered_item(
            "2.", "Manajemen Pesanan Masih Manual:",
            "Mayoritas vendor daerah mencatat pesanan via buku atau chat WhatsApp, memicu jadwal ganda (double-booking) dan kekacauan administrasi operasional."
        ))
        body.append(make_numbered_item(
            "3.", "Ketiadaan Kanal Promosi Harian Fleksibel:",
            "Vendor tidak memiliki fasilitas untuk beriklan secara fleksibel hanya pada momentum tertentu (misal peak season) dengan biaya harian yang terjangkau."
        ))
        
        body.append(make_body(
            "Merespons kendala tersebut, PestaGo hadir sebagai solusi: platform Smart Marketplace & Event Directory berbasis Geolocation yang menghubungkan customer dengan vendor terdekat, menjamin keamanan transaksi otomatis melalui Midtrans, serta menyediakan sistem periklanan kustom harian yang ramah bagi kantong UMKM."
        ))
        
        # ==========================================
        # 2. TUJUAN, MANFAAT, DAN SOLUSI
        # ==========================================
        body.append(make_h1("2. TUJUAN, MANFAAT, DAN SOLUSI"))
        
        body.append(make_h2("2.1 Tujuan"))
        body.append(make_bullet("Membangun Platform Agregator Event Berbasis Geolocation:", "Menyajikan pencarian vendor terdekat secara presisi (km) berdasarkan koordinat lokasi customer."))
        body.append(make_bullet("Mendigitalisasi UMKM Vendor Pesta:", "Menyediakan etalase online resmi, dashboard manajemen order terpadu, dan sarana promosi mandiri."))
        body.append(make_bullet("Mewujudkan Transaksi Aman & Transparan:", "Mengintegrasikan payment gateway otomatis (QRIS/VA) dan sistem ulasan terverifikasi."))
        body.append(make_bullet("Menghadirkan UX yang Bersih dan Responsif:", "Menerapkan Clean UI bernuansa minimalis modern yang ringan dan ramah pengguna seluler."))
        
        body.append(make_h2("2.2 Manfaat"))
        body.append(make_bullet("Bagi Konsumen:", "Memangkas waktu pencarian vendor hingga 70%, hemat biaya logistik, terbebas dari risiko penipuan, dan kemudahan menyimpan paket impian lewat fitur Wishlist."))
        body.append(make_bullet("Bagi UMKM Vendor:", "Memperluas jangkauan pasar lokal, bebas risiko bentrok jadwal, serta akses beriklan di beranda platform dengan bujet harian fleksibel (mulai 1 hari)."))
        body.append(make_bullet("Bagi Industri & Ekonomi:", "Mendorong percepatan digitalisasi UMKM sektor ekonomi kreatif serta standarisasi mutu layanan pesta lokal."))
        
        body.append(make_h2("2.3 Solusi Teknologi yang Ditawarkan"))
        body.append(make_bullet("1. Geolocation Proximity Algorithm (Haversine & Leaflet.js):", "Deteksi koordinat GPS instan untuk menghitung jarak kilometer akurat antara pelanggan dan vendor."))
        body.append(make_bullet("2. Sistem Iklan Billboard & Kustom Harian (Custom Daily Ads):", "Inovasi sewa papan iklan beranda per hari dengan kalkulasi tarif otomatis yang terjangkau bagi vendor UMKM."))
        body.append(make_bullet("3. Automated Multi-Channel Payment Gateway (Midtrans):", "Pembayaran instan via QRIS dan Virtual Account dengan status verifikasi lunas otomatis."))
        body.append(make_bullet("4. Clean Minimalist UI Architecture:", "Tata letak beranda dan modul dengan latar putih bersih konsisten dan navigasi fluid responsive."))
        
        # ==========================================
        # 3. TARGET PENGGUNA / PASAR APLIKASI
        # ==========================================
        body.append(make_h1("3. TARGET PENGGUNA / PASAR APLIKASI"))
        body.append(make_body(
            "PestaGo menyasar tiga segmen pengguna utama dengan kebutuhan yang saling melengkapi:"
        ))
        
        persona_headers = ["Pengguna", "Karakteristik Utama", "Kendala yang Dihadapi", "Solusi dari PestaGo"]
        persona_rows = [
            [
                "Konsumen / Calon Pengantin",
                "Masyarakat & pasangan muda (20-35 th), waktu terbatas, bujet terukur, melek ponsel.",
                "Pusing mencari vendor di medsos, takut ditipu DP hilang, dan khawatir katering basi karena jarak jauh.",
                "Pencarian vendor terdekat (km), rincian paket transparan, fitur wishlist, dan pembayaran bergaransi."
            ],
            [
                "Pelaku UMKM Vendor Pesta",
                "Pemilik usaha katering, tenda, MUA, dekorasi lokal dengan 5-15 kru.",
                "Iklan portal besar jutaan rupiah tidak terjangkau; pencatatan order via WA sering memicu double-booking.",
                "Etalase toko resmi gratis, pasang iklan harian terjangkau, serta dashboard kelola order masuk."
            ],
            [
                "Administrator Platform",
                "Tim operasional PestaGo pengelola ekosistem bisnis dan kurasi.",
                "Risiko iklan spam/ilegal, pengaturan tarif yang kaku, dan verifikasi manual yang lambat.",
                "Panel moderasi iklan berjenjang, kontrol harga harian, dan rekonsiliasi otomatis Midtrans."
            ]
        ]
        body.append(make_table(persona_headers, persona_rows, [1800, 2400, 2400, 2400]))
        
        body.append(make_h2("3.1 Analisis Jobs-To-Be-Done (JTBD)"))
        body.append(make_bullet("Functional Job:", "Konsumen memesan paket pesta terdekat tanpa repot; vendor mempromosikan jasa secara fleksibel dan mencatat booking secara tertib."))
        body.append(make_bullet("Emotional Job:", "Konsumen merasa tenang (peace of mind) tanpa rasa takut penipuan; vendor bangga memiliki profil toko digital profesional."))
        body.append(make_bullet("Social Job:", "Konsumen menyelenggarakan pesta sukses dan berkesan; vendor dikenal sebagai penyedia jasa terpercaya di lingkup daerahnya."))
        
        # ==========================================
        # 4. FITUR UTAMA & KEUNIKAN APLIKASI
        # ==========================================
        body.append(make_h1("4. FITUR UTAMA & KEUNIKAN APLIKASI"))
        
        body.append(make_h2("4.1 Fitur Utama (Functional Features)"))
        body.append(make_numbered_item("1.", "Geolocation Discovery & Peta Interaktif:", "Menghitung jarak radius (1 km, 5 km, 10 km) dan menampilkan rekomendasi 'Layanan Terdekat di Sekitarmu'."))
        body.append(make_numbered_item("2.", "Katalog Multi-Kategori Lengkap:", "Mencakup 8 kategori jasa (Venue, Katering, Dekorasi, MUA/Busana, Fotografi, Sound, Undangan, MC/Hiburan)."))
        body.append(make_numbered_item("3.", "Dedicated Customer Wishlist:", "Penyimpanan layanan favorit dalam satu klik icon love, terhubung langsung ke halaman koleksi pribadi pelanggan."))
        body.append(make_numbered_item("4.", "Sistem Iklan Vendor Billboard & Kustom Harian:", "Vendor dapat beriklan di slider papan reklame beranda dengan opsi paket tetap atau durasi hari fleksibel (hitung otomatis)."))
        body.append(make_numbered_item("5.", "Pemisahan Papan Iklan Admin & Vendor:", "Panel admin memisahkan Papan Utama (Hero Carousel platform - selalu aktif) dengan Papan Billboard Mitra."))
        body.append(make_numbered_item("6.", "Dashboard Vendor Pro:", "Pusat kontrol operasional untuk memantau pendapatan, memperbarui daftar paket, dan mengonfirmasi pesanan."))
        body.append(make_numbered_item("7.", "Multi-Payment Gateway Midtrans:", "Mendukung QRIS instan dan Bank Virtual Account (BCA, Mandiri, BRI, BNI) dengan webhook pelunasan otomatis."))
        body.append(make_numbered_item("8.", "Live Consultation Chat & Verified Review:", "Komunikasi langsung sebelum checkout dan ulasan bintang asli pasca-acara."))
        
        body.append(make_h2("4.2 Keunikan & Nilai Inovasi"))
        body.append(make_bullet("Hyperlocal Proximity Focus:", "Fokus kedekatan jarak riil untuk memitigasi risiko keterlambatan makanan katering dan menekan ongkir armada logistik."))
        body.append(make_bullet("Demokratisasi Iklan Mikro Harian:", "Pertama di portal pesta lokal: vendor UMKM tidak diwajibkan langganan bulanan mahal, melainkan cukup beriklan harian sesuai bujet."))
        body.append(make_bullet("Clean & Minimalist Modern Aesthetic:", "Desain seragam berlatar putih bersih (clean aesthetic), bebas dari kepadatan visual, serta sangat ringan dibuka di smartphone."))
        
        # ==========================================
        # 5. ARSITEKTUR SISTEM, SKEMA DATABASE & TEKNOLOGI
        # ==========================================
        body.append(make_h1("5. ARSITEKTUR SISTEM, SKEMA DATABASE & TEKNOLOGI"))
        
        body.append(make_h2("5.1 Stack Teknologi yang Digunakan"))
        tech_headers = ["Layer", "Teknologi / Tools", "Peran Fungsional"]
        tech_rows = [
            ["Backend", "PHP 8.2+ / Laravel 11 (MVC)", "Routing, Eloquent ORM, multi-auth guard, CSRF security, dan bisnis logika."],
            ["Frontend", "Blade, HTML5, CSS3, JavaScript", "Rendering komponen halaman, DOM interaktif, dan kalkulasi tarif kustom."],
            ["UI Styling", "Bootstrap 5.3 & Clean Theme", "Sistem grid fluid responsif, palet warna putih bersih, dan Bootstrap Icons."],
            ["Database", "MySQL 8.0 Relational DBMS", "Penyimpanan relasional, transaksi ACID, dan integritas referensial data."],
            ["Peta & Geolocation", "Leaflet.js & OpenStreetMap", "Rendering peta interaktif dan penghitungan jarak formula Haversine."],
            ["Payment Gateway", "Midtrans Snap API", "Pemrosesan otomatis pembayaran QRIS dan Virtual Account bank."],
            ["Desain & Alur", "Figma & Whimsical", "Penyusunan User Flow, Wireframe, dan Design System komponen antarmuka."]
        ]
        body.append(make_table(tech_headers, tech_rows, [1800, 3000, 4200]))
        
        body.append(make_h2("5.2 Arsitektur Sistem"))
        body.append(make_body(
            "PestaGo menerapkan Three-Tier Architecture yang memisahkan Client Tier (peramban pengguna & Geolocation API), Application Tier (Laravel Controller, Middleware, & Algoritma Haversine), serta Data & Integration Tier (MySQL 8.0, Midtrans API, dan OpenStreetMap) untuk menjamin keandalan dan skalabilitas sistem."
        ))
        
        body.append(make_h2("5.3 Skema Basis Data (Database Diagram)"))
        body.append(make_body("Sistem mengintegrasikan 10 tabel utama dalam skema relasional ternormalisasi:"))
        body.append(make_bullet("users & vendors:", "Menyimpan akun pengguna, hak akses peran (Customer/Vendor/Admin), legalitas toko, dan titik koordinat GPS."))
        body.append(make_bullet("categories, services, & service_packages:", "Struktur hierarki katalog layanan pesta dan rincian item tiap paket."))
        body.append(make_bullet("advertisement_plans & advertisements:", "Konfigurasi tarif papan iklan, slot harian kustom, poster, serta status tayang."))
        body.append(make_bullet("orders, order_items, & payments:", "Pencatatan nomor order, rincian booking, kanal pembayaran Midtrans, dan status lunas."))
        body.append(make_bullet("wishlists & reviews:", "Penyimpanan layanan favorit pelanggan dan ulasan kepuasan transaksi."))
        
        # ==========================================
        # 6. BATASAN SISTEM APLIKASI
        # ==========================================
        body.append(make_h1("6. BATASAN SISTEM APLIKASI"))
        body.append(make_bullet("1. Tanggung Jawab Fisik Operasional:", "PestaGo memfasilitasi temu-kenal, booking paket, dan transaksi digital. Eksekusi teknis fisik (memasak katering, mendirikan tenda) merupakan tanggung jawab mandiri vendor mitra."))
        body.append(make_bullet("2. Cakupan Wilayah Pilot Project:", "Basis data merchant dan pengujian lapangan difokuskan pada area Jabodetabek dan Jawa Barat sebelum ekspansi nasional."))
        body.append(make_bullet("3. Kanal Transaksi Non-Tunai (Cashless):", "Pembayaran hanya diproses melalui sistem Midtrans (QRIS/VA); pembayaran tunai (COD) ditiadakan guna menjamin transparansi dan keamanan."))
        body.append(make_bullet("4. Dokumen Kontrak Fisik:", "Sistem menerbitkan invoice dan rincian SPK digital. Kontrak bermeterai basah (bila dibutuhkan untuk nilai proyek besar) dilakukan mandiri di luar sistem."))
        
        # ==========================================
        # 7. TANGKAPAN LAYAR (SCREENSHOT) ANTARMUKA
        # ==========================================
        body.append(make_h1("7. TANGKAPAN LAYAR (SCREENSHOT) ANTARMUKA"))
        body.append(make_body("Berikut adalah rangkuman modul antarmuka utama yang telah diimplementasikan secara responsif:"))
        
        ui_headers = ["Modul Halaman", "Elemen Utama", "Fungsi & Peran"]
        ui_rows = [
            ["Landing Page (Beranda)", "Hero Carousel Admin, Bar Geolocation, 8 Kategori, Billboard Vendor, Layanan Terdekat.", "Pintu masuk utama untuk mencari vendor sekitar dan melihat promosi billboard resmi."],
            ["Direktori & Filter Jarak", "Filter km (1-50 km), sorting harga/rating, card layanan, dan peta Leaflet.", "Menyaring vendor berdasarkan kedekatan lokasi dan membandingkan paket."],
            ["Detail Layanan & Toko", "Galeri foto, rincian paket, info alamat, tombol love wishlist, dan tombol chat/pesan.", "Menampilkan detail paket transparan dan memfasilitasi pemesanan langsung."],
            ["Halaman Wishlist", "Grid kartu layanan favorit, estimasi bujet, dan shortcut pemesanan.", "Ruang khusus konsumen untuk meninjau kembali layanan yang disukai."],
            ["Dashboard Vendor Pro", "Ringkasan omset penjualan, tabel pesanan masuk, manajemen paket, dan status toko.", "Pusat operasional vendor untuk mengelola booking dan memantau performa toko."],
            ["Pasang Iklan Kustom Harian", "Pilihan billboard, kalkulator tarif harian dinamis, upload poster, dan bayar Midtrans.", "Memungkinkan vendor memasang iklan harian fleksibel sesuai bujet."],
            ["Panel Admin Iklan", "Pemisahan papan admin vs vendor billboard, tabel moderasi, dan kontrol tarif.", "Memoderasi poster promosi dan mengontrol status serta tarif iklan harian."],
            ["Checkout & Payment Gateway", "Rincian tagihan, popup Midtrans Snap (QRIS/VA), dan status pelunasan otomatis.", "Memproses pembayaran digital yang aman, cepat, dan terenkripsi."]
        ]
        body.append(make_table(ui_headers, ui_rows, [2200, 3600, 3200]))
        
        # ==========================================
        # 8. RENCANA PENGEMBANGAN LANJUTAN
        # ==========================================
        body.append(make_h1("8. RENCANA PENGEMBANGAN LANJUTAN (FUTURE DEVELOPMENT)"))
        body.append(make_bullet("Fase 1: AI Smart Event & Budget Planner (Q3 2026):", "Asisten AI cerdas untuk rekomendasi paket vendor optimal dan susunan rundown acara berdasarkan estimasi bujet."))
        body.append(make_bullet("Fase 2: Digital SPK & Legal Contract with e-Signature (Q4 2026):", "Penandatanganan SPK digital bermeterai resmi (bekerjasama dengan PrivyID/Peruri) langsung di dalam sistem."))
        body.append(make_bullet("Fase 3: Aplikasi Mobile Native Android & iOS (Q1 2027):", "Pengembangan aplikasi Flutter untuk menghadirkan push notifications pesanan instan dan pelacakan live GPS armada vendor."))
        body.append(make_bullet("Fase 4: Smart Escrow Split Payment & Micro-Financing (Q2 2027):", "Pencairan dana bertahap (Termin DP 30%, H-7 40%, Selesai 30%) serta permodalan mikro UMKM bersama fintech terdaftar OJK."))
        
        # ==========================================
        # 9. TAUTAN PENTING (LAMPIRAN)
        # ==========================================
        body.append(make_h1("9. TAUTAN PENTING (LAMPIRAN)"))
        body.append(make_body("Sebagai bahan evaluasi Dewan Juri GTNIC 2026, berikut adalah tautan proyek PestaGo:"))
        body.append(make_bullet("Tautan Sistem / Prototype Live :", "http://pestago.id (Dapat diakses secara publik dengan data simulasi)"))
        body.append(make_bullet("Tautan Repositori / Dokumentasi :", "https://github.com/dikajulian/pestago-v2 (Source code Laravel & panduan setup)"))
        body.append(make_bullet("Tautan Video Demo YouTube :", "https://youtu.be/pestago-demo-gtnic2026 (Demonstrasi fitur dan alur pemesanan)"))
        
        body.append(make_p([], space_before=60, space_after=60))
        body.append(make_body(
            "Demikian proposal karya inovasi PestaGo ini kami susun sebagai kontribusi nyata bagi transformasi digital dan pemberdayaan UMKM sektor pesta di Indonesia.",
            italic=True
        ))
        
        body.append(sectPr)
        new_xml = ET.tostring(root, encoding='utf-8')
        
    with zipfile.ZipFile(template_path, 'r') as zin:
        with zipfile.ZipFile(output_path, 'w', compression=zipfile.ZIP_DEFLATED) as zout:
            for item in zin.infolist():
                if item.filename == 'word/document.xml':
                    zout.writestr(item.filename, new_xml)
                else:
                    zout.writestr(item.filename, zin.read(item.filename))
                    
    print(f"SUCCESS: Compact Proposal saved to {output_path}!")

if __name__ == "__main__":
    generate_compact_pestago_proposal()
