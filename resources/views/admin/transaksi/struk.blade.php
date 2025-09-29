<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-info {
            font-size: 10px;
            margin-bottom: 2px;
        }
        
        .transaction-info {
            margin-bottom: 15px;
            font-size: 11px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .items-section {
            margin-bottom: 15px;
        }
        
        .items-header {
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .item-row {
            margin-bottom: 5px;
            padding-bottom: 3px;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .item-details {
            font-size: 10px;
            display: flex;
            justify-content: space-between;
        }
        
        .totals-section {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            margin: 8px 0;
        }
        
        .payment-info {
            margin-top: 10px;
            font-size: 11px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        
        .thank-you {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .flex-between {
            display: flex;
            justify-content: space-between;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Header Toko -->
    <div class="header">
        <div class="company-name">{{ $company_name }}</div>
        <div class="company-info">{{ $company_address }}</div>
        <div class="company-info">Telp: {{ $company_phone }}</div>
    </div>
    
    <!-- Info Transaksi -->
    <div class="transaction-info">
        <div class="info-row">
            <span>No. Transaksi</span>
            <span><strong>{{ $transaksi->kode_transaksi ?? 'TRX-'.$transaksi->id }}</strong></span>
        </div>
        <div class="info-row">
            <span>Tanggal</span>
            <span>{{ $transaksi->created_at->format('d-m-Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span>Kasir</span>
            <span>{{ $kasir_name }}</span>
        </div>
    </div>
    
    <!-- Daftar Item -->
    <div class="items-section">
        <div class="items-header text-center">DETAIL PEMBELIAN</div>
        
        @foreach($details as $detail)
        <div class="item-row">
            <div class="item-name">{{ $detail->produk_name }}</div>
            <div class="item-details">
                <span>{{ $detail->qty }} x Rp {{ number_format($detail->subtotal / $detail->qty, 0, ',', '.') }}</span>
                <span><strong>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</strong></span>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Ringkasan Total -->
    <div class="totals-section">
        <div class="total-row">
            <span>Total Item</span>
            <span>{{ $total_qty }} item</span>
        </div>
        
        <div class="total-row grand-total">
            <span>TOTAL BELANJA</span>
            <span>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</span>
        </div>
        
        <div class="payment-info">
            <div class="total-row">
                <span>Dibayar</span>
                <span>Rp {{ number_format($transaksi->dibayarkan ?? $transaksi->total, 0, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span>Kembalian</span>
                <span>Rp {{ number_format(($transaksi->kembalian ?? 0), 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div class="thank-you">TERIMA KASIH</div>
        <div>Atas kunjungan Anda</div>
        <div style="margin-top: 10px;">
            <small>Dicetak: {{ date('d-m-Y H:i:s') }}</small>
        </div>
    </div>
</body>
</html>
