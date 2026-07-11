<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Item Request - {{ $itemRequest->request_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.4;
        }

        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin-right: 20px;
            border: 1px solid #ddd;
            padding: 5px;
        }

        .company-info {
            flex-grow: 1;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0 0 5px 0;
        }

        .company-address {
            font-size: 11px;
            color: #666;
            margin: 0;
        }

        .document-title {
            text-align: center;
            margin: 20px 0;
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
        }

        .request-info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .request-info-table td {
            width: 33%;
            padding: 6px 8px;
            vertical-align: top;
            border: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #2c3e50;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .totals {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .total-box {
            padding: 12px 20px;
            background: #ecf0f1;
            border-radius: 5px;
            font-weight: bold;
            border: 1px solid #bdc3c7;
        }

        .status-pill {
            display: inline-block;
            padding: 0.2px 2px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
            text-transform: capitalize;
            vertical-align: middle;
        }

        .status-completed {
            background: #eafaf1;
            color: #3642b3;
            border: 1px solid #3642b3;
        }

        .status-partially-approved {
            background: #eafaf1;
            color: #29cc49;
            border: 1px solid #29cc49;
        }

        .status-approved {
            background: #eafaf1;
            color: #27ae60;
            border: 1px solid #27ae60;
        }

        .status-pending {
            background: #fff4e5;
            color: #f39c12;
            border: 1px solid #f39c12;
        }

        .status-cancelled {
            background: white;
            color: #000;
            border: 1px solid #000;
        }

        .status-rejected {
            background: #fdecea;
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }

        .note-section {
            margin-bottom: 20px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Company Logo">
        <div class="company-info">
            <h1 class="company-name">INVENTOPIA</h1>
            <p class="company-address">
                Jl. Industri Kapal Dalam No.25A, Tugu, Kec. Cimanggis<br>
                Kota Depok, Jawa Barat 16451<br>
                Telp: 0812-8333-0186| Email: adm.semut@yahoo.co.id
            </p>
        </div>
    </div>

    <!-- Judul dokumen -->
    <div class="document-title">
        Formulir Permintaan Barang (Rejected)
    </div>

    <!-- Informasi request (tabel 3 kolom biar sejajar) -->
    <table class="request-info-table">
        <tr>
            <td>
                <strong>Nomor Permintaan:</strong> {{ $itemRequest->request_number }}<br>
                <strong>Hari/Tanggal:</strong> {{ \Carbon\Carbon::parse($itemRequest->created_at)->translatedFormat('l, d F Y') }}
            </td>
            <td>
                <strong>Requested By:</strong> {{ $itemRequest->user?->name }}<br>
                <strong>Department:</strong> {{ $itemRequest->user?->department ?? 'N/A' }}
            </td>
            <td style="display: flex; align-items: center; gap: 4px;">
                <strong>Status:</strong>
                <span class="status-pill status-{{ $itemRequest->status }}">
                    {{ ucfirst($itemRequest->status) }}
                </span>
            </td>
        </tr>
    </table>

    <!-- Catatan -->
    <div style="margin-bottom: 20px;">
        <p><strong>Request Note:</strong></p>
        <div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px; background: #f8f9fa;">
            {{ $itemRequest->note ?: 'No additional notes provided.' }}
        </div>
    </div>

    <!-- Items -->
    <h3 style="color: #2c3e50; border-bottom: 1px solid #2c3e50; padding-bottom: 5px;">Requested Items</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="40%">Product</th>
                <th width="15%">Requested Qty</th>
                <th width="15%">Approved Qty</th>
                <th width="25%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itemRequest->details as $index => $detail)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $detail->product?->name }}</td>
                <td>{{ $detail->requested_quantity }}</td>
                <td>{{ $detail->approved_quantity }}</td>
                <td>
                    <span class="status-pill status-{{ $detail->status }}">
                        {{ ucfirst($detail->status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <div class="total-box">
            Total Requested Items: {{ $itemRequest->total_items }} Units
        </div>
    </div>

    <div style="margin-top: 30px;">
        <table style="border: none; width: 100%;">
            <tr>
                <td style="border: none; width: 33%; text-align: center;">
                    <p>Requested By</p>
                    <div style="margin-top: 50px; border-top: 1px solid #000; width: 80%; margin-left: auto; margin-right: auto;"></div>
                    <p style="margin-top: 5px;">{{ $itemRequest->user?->name }}</p>
                </td>
                <td style="border: none; width: 33%; text-align: center;">
                    <p>Approved By</p>
                    <div style="margin-top: 50px; border-top: 1px solid #000; width: 80%; margin-left: auto; margin-right: auto;"></div>
                    <p style="margin-top: 5px;">{{ $itemRequest->approvedBy->name ?? 'Department Head' }}</p>
                </td>
                <td style="border: none; width: 33%; text-align: center;">
                    <p>Received By</p>
                    <div style="margin-top: 50px; border-top: 1px solid #000; width: 80%; margin-left: auto; margin-right: auto;"></div>
                    <p style="margin-top: 5px;">Warehouse</p>
                </td>
            </tr>
        </table>
    </div>


    <!-- Footer -->
    <div class="footer">
        <p>Document generated on {{ \Carbon\Carbon::now()->format('d F Y H:i') }} | Inventopia Inventory Management System</p>
    </div>
</body>

</html>