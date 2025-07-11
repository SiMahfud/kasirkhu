<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi - <?= esc($transaction->transaction_code) ?></title>
    <!-- Bootstrap CSS - Consider using a minimal version or only necessary styles for print -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Courier New', Courier, monospace; /* Common receipt font */
            font-size: 10pt; /* Adjust as needed */
            color: #000;
            background-color: #fff; /* Ensure background is white for printing */
        }
        .receipt-container {
            max-width: 320px; /* Typical thermal printer width, adjust if needed */
            margin: 20px auto;
            padding: 15px;
            border: 1px dashed #ccc;
        }
        .store-name {
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
        }
        .store-info {
            text-align: center;
            font-size: 0.8em;
            margin-bottom: 10px;
        }
        .transaction-info p, .item-details p {
            margin-bottom: 2px;
        }
        .transaction-info {
            margin-bottom: 10px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }
        .item-header, .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .item-header div, .item-row div {
            padding: 2px 0;
        }
        .item-name {
            flex-basis: 50%;
            text-align: left;
        }
        .item-qty, .item-price, .item-subtotal {
            flex-basis: 15%;
            text-align: right;
        }
        .item-qty { flex-basis: 10%; text-align: center;}
        .item-price { flex-basis: 25%; }
        .item-subtotal { flex-basis: 25%; }

        .totals-section {
            margin-top: 10px;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .totals-row .label {
            font-weight: normal;
        }
        .totals-row .value {
            font-weight: bold;
            text-align: right;
        }
        .footer-message {
            text-align: center;
            font-size: 0.8em;
            margin-top: 15px;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }
        .print-button-container {
            text-align: center;
            margin-top: 20px;
        }

        @media print {
            body {
                font-size: 9pt; /* Slightly smaller for print if needed */
            }
            .receipt-container {
                margin: 0;
                border: none;
                max-width: 100%; /* Use full available width for print */
                box-shadow: none;
            }
            .print-button-container {
                display: none;
            }
            /* Ensure Bootstrap background colors are not printed */
            *, ::before, ::after {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                background-color: transparent !important; /* Override Bootstrap's potential bg colors */
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="store-name"><?= esc($storeInfo['name']) ?></div>
        <div class="store-info">
            <?= esc($storeInfo['address']) ?><br>
            Telp: <?= esc($storeInfo['phone']) ?>
        </div>

        <div class="transaction-info">
            <p>No: <?= esc($transaction->transaction_code) ?></p>
            <p>Tgl: <?= esc(date('d/m/Y H:i:s', strtotime($transaction->created_at))) ?></p>
            <p>Kasir: <?= esc($transaction->cashier_name ?? 'N/A') ?></p>
            <?php if (!empty($transaction->customer_name)): ?>
                <p>Pelanggan: <?= esc($transaction->customer_name) ?></p>
            <?php endif; ?>
        </div>

        <div class="item-details">
            <!-- Header -->
            <div class="item-header">
                <div class="item-name"><strong>Produk</strong></div>
                <div class="item-qty"><strong>Qty</strong></div>
                <div class="item-price"><strong>Harga</strong></div>
                <div class="item-subtotal"><strong>Subtotal</strong></div>
            </div>
            <!-- Items -->
            <?php foreach ($details as $item): ?>
            <div class="item-row">
                <div class="item-name"><?= esc($item->product_name) ?></div>
                <div class="item-qty"><?= esc($item->quantity) ?></div>
                <div class="item-price"><?= number_format($item->price_per_unit, 0, ',', '.') ?></div>
                <div class="item-subtotal"><?= number_format($item->subtotal, 0, ',', '.') ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="totals-section">
            <div class="totals-row">
                <div class="label">Total Barang:</div>
                <div class="value"><?= number_format($transaction->total_amount, 0, ',', '.') ?></div>
            </div>
            <?php if (isset($transaction->discount) && $transaction->discount > 0): ?>
            <div class="totals-row">
                <div class="label">Diskon:</div>
                <div class="value">- <?= number_format($transaction->discount, 0, ',', '.') ?></div>
            </div>
            <?php endif; ?>
            <div class="totals-row" style="font-size: 1.1em;">
                <div class="label"><strong>TOTAL AKHIR:</strong></div>
                <div class="value"><strong><?= number_format($transaction->final_amount, 0, ',', '.') ?></strong></div>
            </div>
            <?php if (!empty($transaction->payment_method)): ?>
            <div class="totals-row">
                <div class="label">Metode Bayar:</div>
                <div class="value"><?= esc($transaction->payment_method) ?></div>
            </div>
             <?php endif; ?>
        </div>

        <?php if (!empty($storeInfo['receipt_footer'])): ?>
        <div class="footer-message">
            <?= esc($storeInfo['receipt_footer']) ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="print-button-container">
        <button class="btn btn-primary" onclick="window.print()">Cetak Struk</button>
        <a href="<?= site_url('transactions/' . $transaction->id) ?>" class="btn btn-secondary">Kembali ke Detail</a>
    </div>

    <!-- Optional: Bootstrap JS for any components, though not strictly needed for receipt display -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script> -->
</body>
</html>
