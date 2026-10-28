import os
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE

def create_ultra_simple_pitch_deck():
    prs = Presentation()
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6]

    # Minimalist Healthcare Color Palette
    C_EMERALD = RGBColor(13, 148, 136)       # Teal/Emerald Toska (#0D9488)
    C_EMERALD_DARK = RGBColor(15, 118, 110)  # Teal 700 (#0F766E)
    C_SLATE_DARK = RGBColor(15, 23, 42)      # Slate 900 (#0F172A)
    C_SLATE_MID = RGBColor(51, 65, 85)       # Slate 700 (#334155)
    C_SLATE_MUTED = RGBColor(100, 116, 139)  # Slate 500 (#64748B)
    C_CARD_BG = RGBColor(248, 250, 252)      # Slate 50 (#F8FAFC)
    C_BORDER = RGBColor(226, 232, 240)       # Slate 200 (#E2E8F0)
    C_WHITE = RGBColor(255, 255, 255)
    C_ROSE = RGBColor(225, 29, 72)           # Rose Accent

    def add_minimal_header(slide, tag_text, title_text, subtitle_text):
        # Small pill tag
        tag_box = slide.shapes.add_textbox(Inches(0.9), Inches(0.55), Inches(10), Inches(0.3))
        tf_t = tag_box.text_frame
        tf_t.word_wrap = True
        tf_t.margin_left = tf_t.margin_top = tf_t.margin_right = tf_t.margin_bottom = 0
        p_t = tf_t.paragraphs[0]
        p_t.text = tag_text.upper()
        p_t.font.name = "Segoe UI"
        p_t.font.size = Pt(10.5)
        p_t.font.bold = True
        p_t.font.color.rgb = C_EMERALD

        # Big clean title
        title_box = slide.shapes.add_textbox(Inches(0.9), Inches(0.85), Inches(11.5), Inches(0.65))
        tf_title = title_box.text_frame
        tf_title.word_wrap = True
        tf_title.margin_left = tf_title.margin_top = tf_title.margin_right = tf_title.margin_bottom = 0
        p_title = tf_title.paragraphs[0]
        p_title.text = title_text
        p_title.font.name = "Segoe UI"
        p_title.font.size = Pt(28)
        p_title.font.bold = True
        p_title.font.color.rgb = C_SLATE_DARK

        # Subtitle
        if subtitle_text:
            sub_box = slide.shapes.add_textbox(Inches(0.9), Inches(1.55), Inches(11.5), Inches(0.4))
            tf_sub = sub_box.text_frame
            tf_sub.word_wrap = True
            tf_sub.margin_left = tf_sub.margin_top = tf_sub.margin_right = tf_sub.margin_bottom = 0
            p_sub = tf_sub.paragraphs[0]
            p_sub.text = subtitle_text
            p_sub.font.name = "Segoe UI"
            p_sub.font.size = Pt(13)
            p_sub.font.color.rgb = C_SLATE_MUTED

    def add_minimal_footer(slide, current_slide, total_slides=5):
        # Footer line
        line = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0.9), Inches(6.9), Inches(11.533), Inches(0.012))
        line.fill.solid()
        line.fill.fore_color.rgb = C_BORDER
        line.line.fill.background()

        # Left label
        fb = slide.shapes.add_textbox(Inches(0.9), Inches(7.0), Inches(8), Inches(0.3))
        tf = fb.text_frame
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0
        p = tf.paragraphs[0]
        p.text = "Laporan Magang • RSI Fatimah Banyuwangi"
        p.font.name = "Segoe UI"
        p.font.size = Pt(9.5)
        p.font.color.rgb = C_SLATE_MUTED

        # Right page number
        nb = slide.shapes.add_textbox(Inches(10.5), Inches(7.0), Inches(1.933), Inches(0.3))
        tf_n = nb.text_frame
        tf_n.margin_left = tf_n.margin_top = tf_n.margin_right = tf_n.margin_bottom = 0
        p_n = tf_n.paragraphs[0]
        p_n.alignment = PP_ALIGN.RIGHT
        p_n.text = f"{current_slide} / {total_slides}"
        p_n.font.name = "Segoe UI"
        p_n.font.size = Pt(10)
        p_n.font.bold = True
        p_n.font.color.rgb = C_EMERALD

    # =========================================================================
    # SLIDE 1: THE PROBLEM & HOOK
    # =========================================================================
    s1 = prs.slides.add_slide(blank_layout)
    add_minimal_header(s1, "01 • TANTANGAN UTAMA",
                       "Tantangan Rekrutmen Nakes Manual",
                       "3 kendala operasional yang dihadapi tim SDI pada metode konvensional.")

    problems = [
        {
            "icon": "📄",
            "stat": "60+ Jam",
            "title": "Tumpukan Kertas",
            "desc": "Ratusan map lamaran fisik menyita ruang arsip dan butuh waktu lama untuk sortir manual."
        },
        {
            "icon": "⏱️",
            "stat": "Rawan Keliru",
            "title": "Verifikasi STR Lambat",
            "desc": "Validasi masa berlaku STR dan sertifikat medis memakan waktu dan berisiko terlewat."
        },
        {
            "icon": "💬",
            "stat": "100+ Pesan",
            "title": "Banjir Tanya Status",
            "desc": "Pelamar terus-menerus bertanya lewat WhatsApp karena tidak ada sistem pelacakan mandiri."
        }
    ]

    cw = Inches(3.6)
    gap = Inches(0.36)
    sx = Inches(0.9)

    for i, p in enumerate(problems):
        cx = sx + i * (cw + gap)
        cy = Inches(2.2)

        card = s1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, cx, cy, cw, Inches(4.35))
        card.fill.solid()
        card.fill.fore_color.rgb = C_CARD_BG
        card.line.color.rgb = C_BORDER

        # Top indicator bar
        bar = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, cx, cy, cw, Inches(0.08))
        bar.fill.solid()
        bar.fill.fore_color.rgb = C_ROSE if i == 1 else C_EMERALD
        bar.line.fill.background()

        tb = s1.shapes.add_textbox(cx + Inches(0.3), cy + Inches(0.4), cw - Inches(0.6), Inches(3.7))
        tf = tb.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0

        # Big Stat / Keyword
        p_stat = tf.paragraphs[0]
        p_stat.text = p["stat"]
        p_stat.font.name = "Segoe UI"
        p_stat.font.size = Pt(26)
        p_stat.font.bold = True
        p_stat.font.color.rgb = C_ROSE if i == 1 else C_EMERALD

        # Title
        p_tit = tf.add_paragraph()
        p_tit.text = f"{p['icon']}  {p['title']}"
        p_tit.font.name = "Segoe UI"
        p_tit.font.size = Pt(16)
        p_tit.font.bold = True
        p_tit.font.color.rgb = C_SLATE_DARK

        # Short clean text
        p_desc = tf.add_paragraph()
        p_desc.text = f"\n{p['desc']}"
        p_desc.font.name = "Segoe UI"
        p_desc.font.size = Pt(12)
        p_desc.font.color.rgb = C_SLATE_MID

    s1.notes_slide.notes_text_frame.text = (
        "POIN BICARA SLIDE 1:\n"
        "\"Assalamu'alaikum wr. wb. Bapak/Ibu sekalian, selama magang di SDI, kami mencatat 3 masalah utama:\n"
        "1. Tumpukan map lamaran fisik yang menyita ruang kerja dan butuh 60 jam lebih untuk sortir.\n"
        "2. Pengecekan STR nakes yang lambat dan rawan human-error bila manual.\n"
        "3. Ratusan chat pelamar setiap hari yang menanyakan status lamaran karena belum ada sistem tracking.\n"
        "Ketiga kendala inilah yang kami selesaikan melalui proyek sistem ini.\""
    )
    add_minimal_footer(s1, 1)

    # =========================================================================
    # SLIDE 2: THE SOLUTION & PRODUCT
    # =========================================================================
    s2 = prs.slides.add_slide(blank_layout)
    add_minimal_header(s2, "02 • SOLUSI SISTEM",
                       "Sistem E-Recruitment Terpadu",
                       "Satu aplikasi web terintegrasi untuk Tim SDI dan Calon Pelamar.")

    # 2 Big Clean Columns
    w2 = Inches(5.55)
    gap2 = Inches(0.43)
    s2_x = Inches(0.9)
    s2_y = Inches(2.2)

    # Column 1: Admin SDI
    card1 = s2.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, s2_x, s2_y, w2, Inches(4.35))
    card1.fill.solid()
    card1.fill.fore_color.rgb = C_CARD_BG
    card1.line.color.rgb = C_BORDER

    bar1 = s2.shapes.add_shape(MSO_SHAPE.RECTANGLE, s2_x, s2_y, w2, Inches(0.08))
    bar1.fill.solid()
    bar1.fill.fore_color.rgb = C_EMERALD
    bar1.line.fill.background()

    tb1 = s2.shapes.add_textbox(s2_x + Inches(0.35), s2_y + Inches(0.4), w2 - Inches(0.7), Inches(3.7))
    tf1 = tb1.text_frame
    tf1.word_wrap = True
    tf1.margin_left = tf1.margin_top = tf1.margin_right = tf1.margin_bottom = 0

    p_t1 = tf1.paragraphs[0]
    p_t1.text = "🛡️ Panel Admin SDI"
    p_t1.font.name = "Segoe UI"
    p_t1.font.size = Pt(20)
    p_t1.font.bold = True
    p_t1.font.color.rgb = C_SLATE_DARK

    p_sub1 = tf1.add_paragraph()
    p_sub1.text = "KONTROL PENUH TIM INTERNAL\n"
    p_sub1.font.name = "Segoe UI"
    p_sub1.font.size = Pt(10)
    p_sub1.font.bold = True
    p_sub1.font.color.rgb = C_EMERALD

    admin_bullets = [
        "Fast Screening Dokumen di layar (tanpa download)",
        "1-Klik Keputusan: Lolos / Tolak (plus alasan resmi)",
        "Monitoring Pelamar & Formasi Aktif real-time",
        "Kunci Pendaftaran Otomatis saat periode tutup"
    ]
    for b in admin_bullets:
        pb = tf1.add_paragraph()
        pb.text = f"✔  {b}"
        pb.font.name = "Segoe UI"
        pb.font.size = Pt(12)
        pb.font.color.rgb = C_SLATE_MID

    # Column 2: Portal Pelamar
    c2_x = s2_x + w2 + gap2
    card2 = s2.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, c2_x, s2_y, w2, Inches(4.35))
    card2.fill.solid()
    card2.fill.fore_color.rgb = C_WHITE
    card2.line.color.rgb = C_BORDER

    bar2 = s2.shapes.add_shape(MSO_SHAPE.RECTANGLE, c2_x, s2_y, w2, Inches(0.08))
    bar2.fill.solid()
    bar2.fill.fore_color.rgb = C_SLATE_DARK
    bar2.line.fill.background()

    tb2 = s2.shapes.add_textbox(c2_x + Inches(0.35), s2_y + Inches(0.4), w2 - Inches(0.7), Inches(3.7))
    tf2 = tb2.text_frame
    tf2.word_wrap = True
    tf2.margin_left = tf2.margin_top = tf2.margin_right = tf2.margin_bottom = 0

    p_t2 = tf2.paragraphs[0]
    p_t2.text = "📱 Portal Pelamar Mandiri"
    p_t2.font.name = "Segoe UI"
    p_t2.font.size = Pt(20)
    p_t2.font.bold = True
    p_t2.font.color.rgb = C_SLATE_DARK

    p_sub2 = tf2.add_paragraph()
    p_sub2.text = "LAYANAN MANDIRI CALON NAKES\n"
    p_sub2.font.name = "Segoe UI"
    p_sub2.font.size = Pt(10)
    p_sub2.font.bold = True
    p_sub2.font.color.rgb = C_SLATE_MUTED

    portal_bullets = [
        "Registrasi Mandiri dengan nomor registrasi unik",
        "Upload Dokumen Pintar (validasi format & ukuran)",
        "Input Data STR & Sertifikat Medis terstandarisasi",
        "Live Tracking Status Kelulusan 24 jam nonstop"
    ]
    for b in portal_bullets:
        pb = tf2.add_paragraph()
        pb.text = f"★  {b}"
        pb.font.name = "Segoe UI"
        pb.font.size = Pt(12)
        pb.font.color.rgb = C_SLATE_MID

    s2.notes_slide.notes_text_frame.text = (
        "POIN BICARA SLIDE 2:\n"
        "\"Sistem yang kami bangun melayani dua sisi:\n"
        "1. Di sisi Tim SDI: Disediakan Panel Admin untuk kontrol penuh. Bisa fast screening berkas langsung di browser dan memutuskan kelulusan dengan 1 klik.\n"
        "2. Di sisi Pelamar: Disediakan Portal Mandiri untuk daftar online, upload berkas terstandarisasi, dan tracking status kelulusan secara transparan.\""
    )
    add_minimal_footer(s2, 2)

    # =========================================================================
    # SLIDE 3: HOW IT WORKS / FLOWCHART
    # =========================================================================
    s3 = prs.slides.add_slide(blank_layout)
    add_minimal_header(s3, "03 • ALUR KERJA",
                       "Alur Ringkas 5 Langkah",
                       "Dari publikasi lowongan hingga data siap pakai untuk kepegawaian.")

    steps = [
        {"no": "1", "icon": "🌐", "name": "Website RS", "desc": "SDI rilis lowongan resmi"},
        {"no": "2", "icon": "📝", "name": "Pilih & Input STR", "desc": "Pelamar upload berkas"},
        {"no": "3", "icon": "🔍", "name": "Fast Screening", "desc": "Admin verifikasi di layar"},
        {"no": "4", "icon": "🔔", "name": "Live Tracking", "desc": "Status terkirim otomatis"},
        {"no": "5", "icon": "📊", "name": "Ekspor SIMRS", "desc": "Data lulusan siap pakai"}
    ]

    w3 = Inches(2.1)
    gap3 = Inches(0.25)
    sx3 = Inches(0.9)
    sy3 = Inches(2.3)

    for i, st in enumerate(steps):
        cx = sx3 + i * (w3 + gap3)

        scard = s3.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, cx, sy3, w3, Inches(4.15))
        scard.fill.solid()
        scard.fill.fore_color.rgb = C_CARD_BG if i != 4 else C_WHITE
        scard.line.color.rgb = C_EMERALD if i == 4 else C_BORDER

        # Number circle
        nc = s3.shapes.add_shape(MSO_SHAPE.OVAL, cx + Inches(0.2), sy3 + Inches(0.25), Inches(0.48), Inches(0.48))
        nc.fill.solid()
        nc.fill.fore_color.rgb = C_EMERALD
        nc.line.fill.background()
        tf_nc = nc.text_frame
        p_nc = tf_nc.paragraphs[0]
        p_nc.alignment = PP_ALIGN.CENTER
        p_nc.text = st["no"]
        p_nc.font.name = "Segoe UI"
        p_nc.font.size = Pt(13)
        p_nc.font.bold = True
        p_nc.font.color.rgb = C_WHITE

        # Arrow indicator between steps
        if i < 4:
            arr_box = s3.shapes.add_textbox(cx + w3 - Inches(0.07), sy3 + Inches(1.9), Inches(0.3), Inches(0.4))
            tf_a = arr_box.text_frame
            tf_a.margin_left = tf_a.margin_top = tf_a.margin_right = tf_a.margin_bottom = 0
            p_a = tf_a.paragraphs[0]
            p_a.text = "➔"
            p_a.font.name = "Segoe UI"
            p_a.font.size = Pt(16)
            p_a.font.bold = True
            p_a.font.color.rgb = C_EMERALD

        tb_step = s3.shapes.add_textbox(cx + Inches(0.2), sy3 + Inches(1.0), w3 - Inches(0.4), Inches(2.9))
        tf_s = tb_step.text_frame
        tf_s.word_wrap = True
        tf_s.margin_left = tf_s.margin_top = tf_s.margin_right = tf_s.margin_bottom = 0

        p_ico = tf_s.paragraphs[0]
        p_ico.text = st["icon"]
        p_ico.font.size = Pt(28)

        p_name = tf_s.add_paragraph()
        p_name.text = st["name"]
        p_name.font.name = "Segoe UI"
        p_name.font.size = Pt(13.5)
        p_name.font.bold = True
        p_name.font.color.rgb = C_SLATE_DARK

        p_desc = tf_s.add_paragraph()
        p_desc.text = f"\n{st['desc']}"
        p_desc.font.name = "Segoe UI"
        p_desc.font.size = Pt(11)
        p_desc.font.color.rgb = C_SLATE_MID

    s3.notes_slide.notes_text_frame.text = (
        "POIN BICARA SLIDE 3:\n"
        "\"Alur kerja sistem ini sangat sederhana dalam 5 tahap:\n"
        "1. Website RS merilis lowongan.\n"
        "2. Pelamar memilih formasi dan menginput STR secara mandiri.\n"
        "3. Tim SDI memverifikasi berkas langsung di layar tanpa cetak kertas.\n"
        "4. Pelamar memantau status secara live.\n"
        "5. Data pelamar lolos langsung diekspor untuk kebutuhan kepegawaian SIMRS.\""
    )
    add_minimal_footer(s3, 3)

    # =========================================================================
    # SLIDE 4: KEY IMPACT & VALUE
    # =========================================================================
    s4 = prs.slides.add_slide(blank_layout)
    add_minimal_header(s4, "04 • DAMPAK & NILAI TAMBAH",
                       "Dampak Nyata bagi RSI Fatimah",
                       "Efisiensi terukur dalam waktu, anggaran operasional, dan pengelolaan data.")

    impacts = [
        {
            "big": "100%",
            "label": "Paperless",
            "desc": "Bebas tumpukan berkas fisik.\nNol biaya map & fotokopi."
        },
        {
            "big": "75%",
            "label": "Lebih Cepat",
            "desc": "Skrining tuntas dalam hari,\nbukan berminggu-minggu."
        },
        {
            "big": "SIMRS",
            "label": "Data Siap Pakai",
            "desc": "Database pelamar rapi,\nsiap ekspor ke kepegawaian."
        }
    ]

    cw4 = Inches(3.6)
    gap4 = Inches(0.36)
    sx4 = Inches(0.9)

    for i, im in enumerate(impacts):
        cx = sx4 + i * (cw4 + gap4)
        cy = Inches(2.2)

        card = s4.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, cx, cy, cw4, Inches(4.35))
        card.fill.solid()
        card.fill.fore_color.rgb = C_CARD_BG
        card.line.color.rgb = C_BORDER

        bar = s4.shapes.add_shape(MSO_SHAPE.RECTANGLE, cx, cy, cw4, Inches(0.08))
        bar.fill.solid()
        bar.fill.fore_color.rgb = C_EMERALD
        bar.line.fill.background()

        tb = s4.shapes.add_textbox(cx + Inches(0.3), cy + Inches(0.5), cw4 - Inches(0.6), Inches(3.5))
        tf = tb.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0

        p_big = tf.paragraphs[0]
        p_big.text = im["big"]
        p_big.font.name = "Segoe UI"
        p_big.font.size = Pt(44)
        p_big.font.bold = True
        p_big.font.color.rgb = C_EMERALD

        p_lbl = tf.add_paragraph()
        p_lbl.text = im["label"]
        p_lbl.font.name = "Segoe UI"
        p_lbl.font.size = Pt(18)
        p_lbl.font.bold = True
        p_lbl.font.color.rgb = C_SLATE_DARK

        p_desc = tf.add_paragraph()
        p_desc.text = f"\n{im['desc']}"
        p_desc.font.name = "Segoe UI"
        p_desc.font.size = Pt(13)
        p_desc.font.color.rgb = C_SLATE_MID

    s4.notes_slide.notes_text_frame.text = (
        "POIN BICARA SLIDE 4:\n"
        "\"Dampak nyata dari sistem ini bagi rumah sakit:\n"
        "1. 100% Paperless: Bebas kertas dan map lamaran fisik, kantor SDI lebih rapi.\n"
        "2. 75% Lebih Cepat: Proses verifikasi yang sebelumnya 2 minggu terpangkas menjadi 2-3 hari.\n"
        "3. Data Siap Pakai: Riwayat kualifikasi nakes tersimpan rapi dan siap masuk ke SIMRS.\""
    )
    add_minimal_footer(s4, 4)

    # =========================================================================
    # SLIDE 5: CONCLUSION, GRATITUDE & LIVE DEMO
    # =========================================================================
    s5 = prs.slides.add_slide(blank_layout)
    add_minimal_header(s5, "05 • PENUTUP",
                       "Terima Kasih & Mari Kita Uji Coba!",
                       "Laporan pertanggungjawaban proyek magang mahasiswa di RSI Fatimah Banyuwangi.")

    # Left: Gratitude
    w5 = Inches(5.55)
    gap5 = Inches(0.43)
    sx5 = Inches(0.9)
    sy5 = Inches(2.2)

    card_g = s5.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, sx5, sy5, w5, Inches(4.35))
    card_g.fill.solid()
    card_g.fill.fore_color.rgb = C_CARD_BG
    card_g.line.color.rgb = C_BORDER

    tb_g = s5.shapes.add_textbox(sx5 + Inches(0.4), sy5 + Inches(0.45), w5 - Inches(0.8), Inches(3.6))
    tf_g = tb_g.text_frame
    tf_g.word_wrap = True
    tf_g.margin_left = tf_g.margin_top = tf_g.margin_right = tf_g.margin_bottom = 0

    p_gt = tf_g.paragraphs[0]
    p_gt.text = "Apresiasi & Terima Kasih"
    p_gt.font.name = "Segoe UI"
    p_gt.font.size = Pt(20)
    p_gt.font.bold = True
    p_gt.font.color.rgb = C_SLATE_DARK

    p_gsub = tf_g.add_paragraph()
    p_gsub.text = (
        "\nTerima kasih yang sebesar-besarnya kepada:\n\n"
        "• Pimpinan & Direksi RSI Fatimah atas kepercayaan & kesempatan yang diberikan.\n\n"
        "• Pembimbing Lapangan & Tim SDI atas bimbingan dan arahan luar biasa selama magang.\n\n"
        "• Seluruh Keluarga Besar RSI Fatimah atas lingkungan kerja yang hangat dan inspiratif."
    )
    p_gsub.font.name = "Segoe UI"
    p_gsub.font.size = Pt(12)
    p_gsub.font.color.rgb = C_SLATE_MID

    # Right: Call to Action Demo
    cx5 = sx5 + w5 + gap5
    card_d = s5.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, cx5, sy5, w5, Inches(4.35))
    card_d.fill.solid()
    card_d.fill.fore_color.rgb = C_SLATE_DARK
    card_d.line.fill.background()

    tb_d = s5.shapes.add_textbox(cx5 + Inches(0.4), sy5 + Inches(0.55), w5 - Inches(0.8), Inches(3.5))
    tf_d = tb_d.text_frame
    tf_d.word_wrap = True
    tf_d.margin_left = tf_d.margin_top = tf_d.margin_right = tf_d.margin_bottom = 0

    p_dt = tf_d.paragraphs[0]
    p_dt.text = "Live Demo Aplikasi 🚀"
    p_dt.font.name = "Segoe UI"
    p_dt.font.size = Pt(24)
    p_dt.font.bold = True
    p_dt.font.color.rgb = C_WHITE

    p_dsub = tf_d.add_paragraph()
    p_dsub.text = (
        "\nSistem telah siap 100% untuk diuji coba:\n\n"
        "✔ Demo Pendaftaran Pelamar & Upload STR\n"
        "✔ Demo Fast Screening di Panel Admin SDI\n"
        "✔ Sesi Tanya Jawab (Q&A)"
    )
    p_dsub.font.name = "Segoe UI"
    p_dsub.font.size = Pt(13)
    p_dsub.font.color.rgb = RGBColor(226, 232, 240)

    # Status pill
    pill = s5.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, cx5 + Inches(0.4), sy5 + Inches(3.2), w5 - Inches(0.8), Inches(0.65))
    pill.fill.solid()
    pill.fill.fore_color.rgb = RGBColor(30, 41, 59)
    pill.line.color.rgb = RGBColor(51, 65, 85)
    tf_p = pill.text_frame
    tf_p.margin_left = Inches(0.2)
    tf_p.margin_top = Inches(0.12)
    p_pl = tf_p.paragraphs[0]
    p_pl.text = "STATUS: SIAP UNTUK LIVE DEMO"
    p_pl.font.name = "Segoe UI"
    p_pl.font.size = Pt(11)
    p_pl.font.bold = True
    p_pl.font.color.rgb = C_EMERALD

    s5.notes_slide.notes_text_frame.text = (
        "POIN BICARA SLIDE 5:\n"
        "\"Sebagai penutup, kami mengucapkan terima kasih banyak kepada Pimpinan dan seluruh Tim SDI atas bimbingannya selama ini.\n"
        "Kini kami mengundang Bapak/Ibu sekalian untuk menyaksikan langsung Live Demo sistem ini dan kami membuka sesi tanya jawab. Terima kasih. Wassalamu'alaikum wr. wb.\""
    )
    add_minimal_footer(s5, 5)

    # Save to both standard names
    out_primary = os.path.join(os.getcwd(), "Pitch_Deck_Magang_RSI_Fatimah.pptx")
    out_secondary = os.path.join(os.getcwd(), "Pitch_Deck_E-Recruitment_RSI_Fatimah.pptx")
    
    prs.save(out_primary)
    prs.save(out_secondary)
    print(f"Ultra-simple presentations successfully generated:\n- {out_primary}\n- {out_secondary}")

if __name__ == "__main__":
    create_ultra_simple_pitch_deck()
