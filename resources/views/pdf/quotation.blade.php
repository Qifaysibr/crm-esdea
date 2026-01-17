<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation - {{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
        }
        .quotation-title {
            font-size: 18px;
            margin-top: 10px;
            font-weight: bold;
        }
        .info-box {
            margin: 20px 0;
        }
        .info-row {
            margin: 5px 0;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th {
            background-color: #4F46E5;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #E5E7EB;
        }
        table tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #4F46E5;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            font-size: 10px;
            color: #6B7280;
        }
        .page-break {
            page-break-after: always;
        }
        .terms-section {
            margin: 20px 0;
        }
        .terms-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            color: #4F46E5;
        }
        .bank-info {
            background-color: #F3F4F6;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .logo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        .logo-item {
            text-align: center;
            padding: 10px;
            border: 1px solid #E5E7EB;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- PAGE 1: QUOTATION DETAILS -->
    <div class="header">
        <div class="company-name">PT ESDEA ASSISTANCE MANAGEMENT</div>
        <div style="font-size: 11px; color: #6B7280;">Jasa Konsultasi Legalitas & Sertifikasi Usaha</div>
        <div class="quotation-title">PENAWARAN HARGA</div>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Nomor Quotation:</span>
            <span>{{ $quotation->quotation_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal:</span>
            <span>{{ $quotation->quotation_date->format('d F Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Berlaku Hingga:</span>
            <span>{{ $quotation->valid_until->format('d F Y') }}</span>
        </div>
    </div>

    <div class="info-box" style="margin-top: 30px;">
        <div style="font-weight: bold; margin-bottom: 10px;">Kepada Yth:</div>
        <div class="info-row">
            <span class="info-label">Nama:</span>
            <span>{{ $quotation->customer_name }}</span>
        </div>
        @if($quotation->customer_company)
        <div class="info-row">
            <span class="info-label">Perusahaan:</span>
            <span>{{ $quotation->customer_company }}</span>
        </div>
        @endif
        @if($quotation->customer_email)
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span>{{ $quotation->customer_email }}</span>
        </div>
        @endif
        @if($quotation->customer_phone)
        <div class="info-row">
            <span class="info-label">Telepon:</span>
            <span>{{ $quotation->customer_phone }}</span>
        </div>
        @endif
    </div>

    <p style="margin: 30px 0;">
        Terima kasih atas kepercayaan Anda kepada PT Esdea Assistance Management. Berikut kami sampaikan penawaran harga untuk layanan yang Anda butuhkan:
    </p>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Deskripsi Layanan</th>
                <th width="20%">Keterangan</th>
                <th width="15%" class="text-right">Harga Satuan</th>
                <th width="8%" class="text-center">Qty</th>
                <th width="17%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $item->product_name }}</strong>
                    @if($item->description)
                    <br><small style="color: #6B7280;">{{ $item->description }}</small>
                    @endif
                </td>
                <td>{{ $item->notes ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</span>
        </div>
        @if($quotation->discount_amount > 0)
        <div class="total-row">
            <span>Diskon ({{ $quotation->discount_percentage }}%):</span>
            <span>Rp {{ number_format($quotation->discount_amount, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="total-row grand-total">
            <span>TOTAL:</span>
            <span>Rp {{ number_format($quotation->total, 0, ',', '.') }}</span>
        </div>
    </div>

    <div style="clear: both;"></div>

    <div class="footer">
        <p>Quotation ini dibuat oleh {{ $creator->name }} - PT Esdea Assistance Management</p>
        <p>Halaman 1 dari 3</p>
    </div>

    <!-- PAGE 2: TERMS & COMPANY PROFILE -->
    <div class="page-break"></div>

    <div class="header">
        <div class="company-name">PT ESDEA ASSISTANCE MANAGEMENT</div>
        <div style="font-size: 11px; color: #6B7280;">Syarat & Ketentuan</div>
    </div>

    <div class="terms-section">
        <div class="terms-title">KETENTUAN PEMBAYARAN</div>
        <ol style="margin-left: 20px;">
            <li>DP (Down Payment) sebesar <strong>60%</strong> dari total harga setelah penandatanganan kontrak</li>
            <li>Pelunasan dilakukan <strong>H+1</strong> setelah pekerjaan selesai</li>
            <li>Pembayaran dilakukan melalui transfer bank ke rekening perusahaan</li>
            <li>Quotation ini berlaku selama <strong>14 hari</strong> sejak tanggal terbit</li>
        </ol>
    </div>

    <div class="bank-info">
        <div style="font-weight: bold; margin-bottom: 10px; color: #4F46E5;">INFORMASI REKENING</div>
        <div style="margin: 5px 0;"><strong>Bank:</strong> Bank Mandiri</div>
        <div style="margin: 5px 0;"><strong>Nama Rekening:</strong> PT Esdea Assistance Management</div>
        <div style="margin: 5px 0;"><strong>Nomor Rekening:</strong> 1234567890</div>
    </div>

    <div class="terms-section">
        <div class="terms-title">CATATAN PENTING</div>
        <ul style="margin-left: 20px;">
            <li>Harga belum termasuk pajak (jika ada)</li>
            <li>Waktu pengerjaan akan dikonfirmasi setelah pembayaran DP diterima</li>
            <li>Proses pengurusan disesuaikan dengan ketentuan instansi terkait</li>
        </ul>
    </div>

    <div class="terms-section" style="margin-top: 40px;">
        <div class="terms-title">TENTANG KAMI</div>
        <p style="text-align: justify;">
            <strong>PT Esdea Assistance Management</strong> adalah perusahaan konsultan yang bergerak di bidang legalitas dan sertifikasi usaha sejak tahun <strong>2023</strong>. 
            Kami telah membantu ratusan perusahaan dalam mengurus berbagai perizinan dan sertifikasi usaha, termasuk SILO, SIO, NIB, BPOM, Halal MUI, dan berbagai izin lainnya.
        </p>
        <p style="text-align: justify; margin-top: 10px;">
            Dengan tim profesional yang berpengalaman dan jaringan yang luas dengan instansi pemerintah, kami berkomitmen memberikan layanan terbaik dengan proses yang cepat, transparan, dan terpercaya.
        </p>
    </div>

    <div class="footer">
        <p>Untuk informasi lebih lanjut, hubungi kami di email: info@esdea.com | Telepon: 021-12345678</p>
        <p>Halaman 2 dari 3</p>
    </div>

    <!-- PAGE 3: SERVICE CATALOG & CLIENTS -->
    <div class="page-break"></div>

    <div class="header">
        <div class="company-name">PT ESDEA ASSISTANCE MANAGEMENT</div>
        <div style="font-size: 11px; color: #6B7280;">Katalog Layanan & Klien Kami</div>
    </div>

    <div class="terms-section">
        <div class="terms-title">LAYANAN KAMI</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
            <div>
                <strong style="color: #4F46E5;">Legalitas & Perizinan:</strong>
                <ul style="margin-left: 20px; margin-top: 5px;">
                    <li>NIB (Nomor Induk Berusaha)</li>
                    <li>Sertifikasi SILO</li>
                    <li>Sertifikasi SIO</li>
                    <li>TDP & SIUP</li>
                    <li>Izin Usaha Perdagangan</li>
                </ul>
            </div>
            <div>
                <strong style="color: #4F46E5;">Sertifikasi Usaha:</strong>
                <ul style="margin-left: 20px; margin-top: 5px;">
                    <li>Sertifikat Halal MUI</li>
                    <li>Izin Edar BPOM</li>
                    <li>SNI & ISO</li>
                    <li>Sertifikasi K3</li>
                    <li>HKI & Merek Dagang</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="terms-section" style="margin-top: 30px;">
        <div class="terms-title">KLIEN & MITRA KAMI</div>
        <p style="margin-bottom: 15px;">Kami bangga telah bekerjasama dengan berbagai perusahaan terkemuka:</p>
        
        <div class="logo-grid">
            <div class="logo-item">
                <div style="font-size: 24px; font-weight: bold; color: #4F46E5;">JAKPRO</div>
                <div style="font-size: 9px; color: #6B7280; margin-top: 5px;">PT Jakarta Propertindo</div>
            </div>
            <div class="logo-item">
                <div style="font-size: 24px; font-weight: bold; color: #10B981;">LENURGI</div>
                <div style="font-size: 9px; color: #6B7280; margin-top: 5px;">PT Lestari Energi</div>
            </div>
            <div class="logo-item">
                <div style="font-size: 24px; font-weight: bold; color: #3B82F6;">BCA</div>
                <div style="font-size: 9px; color: #6B7280; margin-top: 5px;">Bank Central Asia</div>
            </div>
        </div>
    </div>

    <div class="terms-section" style="margin-top: 30px; text-align: center; padding: 20px; background-color: #F3F4F6; border-radius: 10px;">
        <div style="font-size: 16px; font-weight: bold; color: #4F46E5; margin-bottom: 10px;">
            HUBUNGI KAMI
        </div>
        <p style="margin: 5px 0;">📧 Email: info@esdea.com</p>
        <p style="margin: 5px 0;">📱 WhatsApp: 0812-3456-7890</p>
        <p style="margin: 5px 0;">🏢 Alamat: Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta</p>
        <p style="margin: 5px 0;">🌐 Website: www.esdea.com</p>
    </div>

    <div class="footer" style="margin-top: 40px;">
        <p style="text-align: center; font-weight: bold; color: #4F46E5;">Terima kasih atas kepercayaan Anda!</p>
        <p style="text-align: center;">Halaman 3 dari 3</p>
    </div>
</body>
</html>
