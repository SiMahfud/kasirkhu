<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <h2><i class="fas fa-cash-register"></i> Buat Transaksi Baru</h2>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?php if (is_array(session()->getFlashdata('error'))): ?>
                <ul>
                    <?php foreach (session()->getFlashdata('error') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            <?php else: ?>
                <?= esc(session()->getFlashdata('error')) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?= form_open('transactions/create', ['id' => 'transaction-form']) ?>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="customer_name" class="form-label">Nama Pelanggan (Opsional)</label>
                <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?= old('customer_name') ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="payment_method" class="form-label">Metode Pembayaran</label>
                <select class="form-select" id="payment_method" name="payment_method">
                    <option value="cash" <?= old('payment_method') == 'cash' ? 'selected' : '' ?>>Tunai (Cash)</option>
                    <option value="card" <?= old('payment_method') == 'card' ? 'selected' : '' ?>>Kartu Debit/Kredit</option>
                    <option value="qris" <?= old('payment_method') == 'qris' ? 'selected' : '' ?>>QRIS</option>
                    <option value="transfer" <?= old('payment_method') == 'transfer' ? 'selected' : '' ?>>Transfer Bank</option>
                </select>
            </div>
        </div>
    </div>

    <hr>

    <h4><i class="fas fa-boxes"></i> Produk/Layanan</h4>
    <div id="product-lines-container">
        <!-- Product lines will be added here by JavaScript -->
        <?php
        $oldProducts = old('products');
        if (!empty($oldProducts)):
            foreach ($oldProducts as $index => $oldProduct):
                if (empty($oldProduct['id']) && empty($oldProduct['quantity'])) continue; // Skip empty entries from potential JS errors
        ?>
            <div class="row product-line mb-2 align-items-center" data-index="<?= $index ?>">
                <div class="col-md-5">
                    <select class="form-select product-select" name="products[<?= $index ?>][id]">
                        <option value="">Pilih Produk...</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product->id ?>" data-price="<?= $product->price ?>" <?= ($oldProduct['id'] ?? '') == $product->id ? 'selected' : '' ?>>
                                <?= esc($product->name) ?> (Stok: <?= $product->stock ?? 'N/A' ?>) - Rp <?= number_format($product->price, 0, ',', '.') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control quantity-input" name="products[<?= $index ?>][quantity]" placeholder="Qty" min="1" value="<?= esc($oldProduct['quantity'] ?? 1) ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control subtotal-display" readonly placeholder="Subtotal">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-danger remove-product-line"><i class="fas fa-trash-alt"></i> Hapus</button>
                </div>
            </div>
        <?php
            endforeach;
        else: // Add one empty line by default if no old input
        ?>
            <div class="row product-line mb-2 align-items-center" data-index="0">
                <div class="col-md-5">
                    <select class="form-select product-select" name="products[0][id]">
                        <option value="">Pilih Produk...</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product->id ?>" data-price="<?= $product->price ?>">
                                <?= esc($product->name) ?> (Stok: <?= $product->stock ?? 'N/A' ?>) - Rp <?= number_format($product->price, 0, ',', '.') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control quantity-input" name="products[0][quantity]" placeholder="Qty" min="1" value="1">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control subtotal-display" readonly placeholder="Subtotal">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-danger remove-product-line"><i class="fas fa-trash-alt"></i> Hapus</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <button type="button" id="add-product-line" class="btn btn-sm btn-primary mb-3"><i class="fas fa-plus"></i> Tambah Produk</button>

    <hr>

    <div class="row justify-content-end">
        <div class="col-md-4">
            <div class="mb-3 row">
                <label for="total_amount" class="col-sm-5 col-form-label">Total</label>
                <div class="col-sm-7">
                    <input type="text" readonly class="form-control-plaintext" id="total_amount_display" value="Rp 0">
                </div>
            </div>
            <div class="mb-3 row">
                <label for="discount" class="col-sm-5 col-form-label">Diskon</label>
                <div class="col-sm-7">
                    <input type="number" class="form-control" id="discount" name="discount" value="<?= old('discount', 0) ?>" min="0" step="0.01">
                </div>
            </div>
            <div class="mb-3 row">
                <label for="final_amount" class="col-sm-5 col-form-label fw-bold">Grand Total</label>
                <div class="col-sm-7">
                    <input type="text" readonly class="form-control-plaintext fw-bold" id="final_amount_display" value="Rp 0">
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Transaksi</button>
        <a href="<?= site_url('transactions') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Batal</a>
    </div>
    <?= form_close() ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const productLinesContainer = document.getElementById('product-lines-container');
    const addProductLineButton = document.getElementById('add-product-line');
    const discountInput = document.getElementById('discount');
    const form = document.getElementById('transaction-form');
    let productLineIndex = <?= !empty($oldProducts) ? count($oldProducts) : 1 ?>;

    // Store product data for easy lookup
    const productsData = <?= json_encode(array_map(function($p){ return ['id' => $p->id, 'name' => $p->name, 'price' => $p->price, 'stock' => $p->stock]; }, $products)) ?>;

    function formatCurrency(amount) {
        return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function calculateLineSubtotal(productLine) {
        const productSelect = productLine.querySelector('.product-select');
        const quantityInput = productLine.querySelector('.quantity-input');
        const subtotalDisplay = productLine.querySelector('.subtotal-display');

        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const price = parseFloat(selectedOption.dataset.price) || 0;
        const quantity = parseInt(quantityInput.value) || 0;
        const subtotal = price * quantity;

        subtotalDisplay.value = formatCurrency(subtotal);
        return subtotal;
    }

    function calculateTotals() {
        let currentTotalAmount = 0;
        document.querySelectorAll('.product-line').forEach(line => {
            currentTotalAmount += calculateLineSubtotal(line);
        });

        const discount = parseFloat(discountInput.value) || 0;
        const finalAmount = currentTotalAmount - discount;

        document.getElementById('total_amount_display').value = formatCurrency(currentTotalAmount);
        document.getElementById('final_amount_display').value = formatCurrency(finalAmount);
    }

    function createProductLine(index) {
        const newLine = document.createElement('div');
        newLine.classList.add('row', 'product-line', 'mb-2', 'align-items-center');
        newLine.dataset.index = index;

        let optionsHtml = '<option value="">Pilih Produk...</option>';
        productsData.forEach(product => {
            optionsHtml += `<option value="${product.id}" data-price="${product.price}">${product.name} (Stok: ${product.stock === null ? 'N/A' : product.stock}) - Rp ${parseFloat(product.price).toLocaleString('id-ID')}</option>`;
        });

        newLine.innerHTML = `
            <div class="col-md-5">
                <select class="form-select product-select" name="products[${index}][id]">
                    ${optionsHtml}
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control quantity-input" name="products[${index}][quantity]" placeholder="Qty" min="1" value="1">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control subtotal-display" readonly placeholder="Subtotal">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-danger remove-product-line"><i class="fas fa-trash-alt"></i> Hapus</button>
            </div>
        `;
        return newLine;
    }

    addProductLineButton.addEventListener('click', function () {
        const newLine = createProductLine(productLineIndex);
        productLinesContainer.appendChild(newLine);
        productLineIndex++;
        attachEventListenersToLine(newLine);
        calculateTotals(); // Recalculate after adding a new line
    });

    function attachEventListenersToLine(line) {
        const productSelect = line.querySelector('.product-select');
        const quantityInput = line.querySelector('.quantity-input');
        const removeButton = line.querySelector('.remove-product-line');

        productSelect.addEventListener('change', calculateTotals);
        quantityInput.addEventListener('input', calculateTotals);
        quantityInput.addEventListener('change', calculateTotals); // For up/down arrows

        removeButton.addEventListener('click', function () {
            line.remove();
            calculateTotals();
            // Renumber product lines if needed, though backend handles arbitrary indices
        });
    }

    // Attach listeners to existing lines (e.g., from validation error causing page reload with old data)
    document.querySelectorAll('.product-line').forEach(line => {
        attachEventListenersToLine(line);
    });

    discountInput.addEventListener('input', calculateTotals);
    discountInput.addEventListener('change', calculateTotals);

    // Initial calculation for existing lines on page load
    calculateTotals();

    // Prevent form submission if no valid product lines are present.
    // The controller also validates this, but client-side check is good UX.
    form.addEventListener('submit', function(event) {
        let validLines = 0;
        document.querySelectorAll('.product-line').forEach(line => {
            const productSelect = line.querySelector('.product-select');
            const quantityInput = line.querySelector('.quantity-input');
            if (productSelect.value && parseInt(quantityInput.value) > 0) {
                validLines++;
            }
        });
        if (validLines === 0) {
            event.preventDefault();
            alert('Harap tambahkan setidaknya satu produk yang valid ke transaksi.');
            // Optionally, focus the first product select or add button.
        }
    });
});
</script>
<?= $this->endSection() ?>
