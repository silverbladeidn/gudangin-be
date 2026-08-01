<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body {
            font-family: sans-serif;
            margin: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h3>Stock Movement Report</h3>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Produk</th>
                <th>Tipe</th>
                <th>Kuantitas</th>
                <th>Stok Awal</th>
                <th>Stok Saat Ini</th>
                <th>Referensi</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stockMove as $index => $smove)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $smove->product->name ?? '-' }}</td>
                    <td>{{ $smove->type }}</td>
                    <td>{{ $smove->quantity }}</td>
                    <td>{{ $smove->previous_stock }}</td>
                    <td>{{ $smove->current_stock }}</td>
                    <td>{{ $smove->reference }}</td>
                    <td>{{ $smove->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
</body>
</html>