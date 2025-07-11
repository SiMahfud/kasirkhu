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
                            <?php
                                // Attempt to get category name - requires ProductModel to join categories or load them
                                // For now, let's assume category_id is directly comparable or product name is key
                                $categoryName = ''; // Placeholder, ideally fetch this if needed for logic
                                // Example: if $product->category_name is available from controller
                                // $categoryName = $product->category_name ?? '';
                            ?>
                            <option value="<?= $product->id ?>"
                                    data-price="<?= $product->price ?>"
                                    data-name="<?= esc(strtolower($product->name)) ?>"
                                    data-category-id="<?= esc($product->category_id) ?>"
                                    data-unit="<?= esc($product->unit) ?>">
                                <?= esc($product->name) ?> (Stok: <?= $product->stock ?? 'N/A' ?>) - Rp <?= number_format($product->price, 0, ',', '.') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 col-qty">
                    <input type="number" class="form-control quantity-input" name="products[0][quantity]" placeholder="Qty" min="1" value="1">
                </div>
                <div class="col-md-3 col-price-subtotal">
                    <!-- Input harga manual akan muncul di sini jika diperlukan -->
                    <input type="text" class="form-control subtotal-display" readonly placeholder="Subtotal">
                </div>
                <div class="col-md-2 col-action">
                    <button type="button" class="btn btn-sm btn-danger remove-product-line"><i class="fas fa-trash-alt"></i> Hapus</button>
                </div>
                <!-- Placeholder for service specific inputs -->
                <div class="col-12 service-inputs mt-2" style="display: none;">
                    <!-- Fotokopi/Print Inputs -->
                    <div class="row gx-2 fotocopy-print-inputs" style="display: none;">
                        <div class="col-md-3"><input type="number" class="form-control form-control-sm service-pages" name="products[0][service_pages]" placeholder="Jml Halaman"></div>
                        <div class="col-md-3">
                            <select class="form-select form-select-sm service-paper" name="products[0][service_paper_type]">
                                <option value="hvs">HVS</option>
                                <option value="artpaper">Art Paper</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select form-select-sm service-color" name="products[0][service_color_type]">
                                <option value="bw">Hitam Putih</option>
                                <option value="color">Warna</option>
                            </select>
                        </div>
                         <div class="col-md-3"><input type="number" class="form-control form-control-sm service-calculated-price" name="products[0][service_item_price]" placeholder="Harga Item Jasa"></div>
                    </div>
                    <!-- Design/Edit Inputs - Manual Price -->
                    <div class="row gx-2 design-edit-inputs" style="display: none;">
                        <div class="col-md-9"><input type="text" class="form-control form-control-sm service-description" name="products[0][service_description]" placeholder="Deskripsi Jasa (mis: Desain Logo)"></div>
                        <!-- Harga manual untuk item ini akan di-handle oleh perubahan input harga di baris utama -->
                    </div>
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
    let productLineIndex = productLinesContainer.querySelectorAll('.product-line').length > 0 ? productLinesContainer.querySelectorAll('.product-line').length : 1;

    // Ensure productLineIndex is at least 1 if starting fresh or if oldProducts resulted in 0 lines rendered
    if (productLinesContainer.children.length === 0) { // if no lines rendered by PHP (e.g. no old products)
        productLineIndex = 0; // Will be incremented to 0 by first add, then names will be products[0]
        // Or, if createProductLine is called immediately for the first line:
        // const firstLine = createProductLine(0); productLinesContainer.appendChild(firstLine); attachEventListenersToLine(firstLine); productLineIndex = 1;
    } else {
         // productLineIndex should be the next available index
         productLineIndex = Math.max(...Array.from(productLinesContainer.querySelectorAll('.product-line')).map(line => parseInt(line.dataset.index))) + 1;
    }


    // Store product data for easy lookup, including category_id and unit
    const productsData = <?= json_encode(array_map(function($p){
        return [
            'id' => $p->id,
            'name' => strtolower($p->name), // lowercase for easier matching
            'price' => $p->price,
            'stock' => $p->stock,
            'category_id' => $p->category_id, // Assuming category_id is available
            'unit' => strtolower($p->unit ?? '') // lowercase for easier matching
        ];
    }, $products)) ?>;

    // Define service product identifiers (examples, adjust as needed)
    // These could also be passed from controller if dynamic
    const FOTOCOPY_PRINT_KEYWORDS = ['fotokopi', 'print', 'cetak dokumen'];
    const FOTOCOPY_PRINT_UNITS = ['lembar', 'halaman'];
    // Category IDs for services (example, get from DB or config)
    // const FOTOCOPY_CATEGORY_ID = 10;
    // const PRINT_CATEGORY_ID = 11;

    const DESIGN_EDIT_KEYWORDS = ['desain', 'design', 'edit', 'banner', 'spanduk', 'logo'];
    // const DESIGN_CATEGORY_ID = 12;


    function formatCurrency(amount) {
        return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function calculateLineSubtotal(productLine) {
        const productSelect = productLine.querySelector('.product-select');
        const quantityInput = productLine.querySelector('.quantity-input');
        const subtotalDisplay = productLine.querySelector('.subtotal-display');
        const manualPriceInput = productLine.querySelector('.manual-price-input');
        const serviceCalculatedPriceInput = productLine.querySelector('.service-calculated-price');

        const selectedOption = productSelect.options[productSelect.selectedIndex];
        let price = parseFloat(selectedOption.dataset.price) || 0;
        const productName = selectedOption.dataset.name || "";
        const productUnit = selectedOption.dataset.unit || "";
        // const productCategoryId = selectedOption.dataset.categoryId || ""; // For category-based logic

        let quantity = parseInt(quantityInput.value) || 0;

        // Check if this line has specific service inputs visible
        const serviceInputsContainer = productLine.querySelector('.service-inputs');
        const fotocopyPrintInputs = serviceInputsContainer.querySelector('.fotocopy-print-inputs');
        const designEditInputs = serviceInputsContainer.querySelector('.design-edit-inputs');

        let finalItemPrice = price; // This is price per unit

        if (fotocopyPrintInputs.style.display !== 'none') {
            const pages = parseInt(productLine.querySelector('.service-pages').value) || 0;
            // For fotokopi/print, quantity might mean number of sets, pages is per set.
            // Or, quantity could be total pages. For now, assume quantity input is primary multiplier.
            // Price calculation for fotokopi/print:
            // This is a placeholder. Real logic would use pages, paper type, color to determine price.
            // Example: if price from DB is per page B/W HVS.
            // price = pages * price; // if DB price is per page
            // For now, let product's selected price be the price per item, and quantity be number of items.
            // The service-calculated-price input can be used to override this.
            if (serviceCalculatedPriceInput && serviceCalculatedPriceInput.value !== '') {
                 finalItemPrice = parseFloat(serviceCalculatedPriceInput.value) || price;
                 // If service-calculated-price is used, quantity typically becomes 1 for that "package"
                 // Or, if it's price per page, then quantity is pages.
                 // For this demo, let's assume service-calculated-price IS the finalItemPrice for ONE service unit.
                 // And the main quantity input is how many of those service units.
            }
            // If pages are relevant, and quantity is sets:
            // quantity = quantity * pages; // to get total pages if price is per page.
            // This part needs clear definition from client.
            // Let's assume for now: `quantity` is the main multiplier.
            // `service-calculated-price` if filled, becomes the new `finalItemPrice`.
        }

        if (manualPriceInput) { // If manual price input exists and is used
            finalItemPrice = parseFloat(manualPriceInput.value) || price;
        }

        const subtotal = finalItemPrice * quantity;
        subtotalDisplay.value = formatCurrency(subtotal);
        return subtotal;
    }

    function calculateTotals() {
        let currentTotalAmount = 0;
        document.querySelectorAll('.product-line').forEach(line => {
            currentTotalAmount += calculateLineSubtotal(line); // calculateLineSubtotal must be accurate
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
            optionsHtml += `<option value="${product.id}" data-price="${product.price}" data-name="${product.name.toLowerCase()}" data-category-id="${product.category_id}" data-unit="${(product.unit || '').toLowerCase()}">${product.name} (Stok: ${product.stock === null ? 'N/A' : product.stock}) - Rp ${parseFloat(product.price).toLocaleString('id-ID')}</option>`;
        });

        const currentProductLineIndex = productLineIndex; // Capture index for use in names

        newLine.innerHTML = `
            <div class="col-md-5">
                <select class="form-select product-select" name="products[${currentProductLineIndex}][id]">
                    ${optionsHtml}
                </select>
            </div>
            <div class="col-md-2 col-qty">
                <input type="number" class="form-control quantity-input" name="products[${currentProductLineIndex}][quantity]" placeholder="Qty" min="1" value="1">
            </div>
            <div class="col-md-3 col-price-subtotal">
                <!-- Manual price input might be dynamically added here by JS -->
                <input type="text" class="form-control subtotal-display" readonly placeholder="Subtotal">
            </div>
            <div class="col-md-2 col-action">
                <button type="button" class="btn btn-sm btn-danger remove-product-line"><i class="fas fa-trash-alt"></i> Hapus</button>
            </div>
            <!-- Placeholder for service specific inputs -->
            <div class="col-12 service-inputs mt-2" style="display: none;">
                <!-- Fotokopi/Print Inputs -->
                <div class="row gx-2 fotocopy-print-inputs" style="display: none;">
                    <div class="col-md-3"><input type="number" class="form-control form-control-sm service-pages" name="products[${currentProductLineIndex}][service_pages]" placeholder="Jml Halaman"></div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm service-paper" name="products[${currentProductLineIndex}][service_paper_type]">
                            <option value="hvs">HVS</option> <option value="artpaper">Art Paper</option> <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm service-color" name="products[${currentProductLineIndex}][service_color_type]">
                            <option value="bw">Hitam Putih</option> <option value="color">Warna</option>
                        </select>
                    </div>
                    <div class="col-md-3"><input type="number" class="form-control form-control-sm service-calculated-price" name="products[${currentProductLineIndex}][service_item_price]" placeholder="Harga Item Jasa"></div>
                </div>
                <!-- Design/Edit Inputs - Manual Price -->
                <div class="row gx-2 design-edit-inputs" style="display: none;">
                     <div class="col-md-9"><input type="text" class="form-control form-control-sm service-description" name="products[${currentProductLineIndex}][service_description]" placeholder="Deskripsi Jasa (mis: Desain Logo)"></div>
                </div>
            </div>
        `;
        return newLine;
    }

    function isFotocopyPrintProduct(productName, productUnit, productPrice) {
        if (FOTOCOPY_PRINT_UNITS.includes(productUnit)) return true;
        for (const keyword of FOTOCOPY_PRINT_KEYWORDS) {
            if (productName.includes(keyword)) return true;
        }
        // Add category check if FOTOCOPY_CATEGORY_ID is defined
        return false;
    }

    function isDesignEditProduct(productName, productUnit, productPrice) {
        if (parseFloat(productPrice) === 0 && !isFotocopyPrintProduct(productName, productUnit, productPrice)) return true; // Price 0 and not fotocopy
        for (const keyword of DESIGN_EDIT_KEYWORDS) {
            if (productName.includes(keyword)) return true;
        }
        // Add category check if DESIGN_CATEGORY_ID is defined
        return false;
    }


    function handleProductSelection(productLine) {
        const productSelect = productLine.querySelector('.product-select');
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const serviceInputsContainer = productLine.querySelector('.service-inputs');
        const fotocopyPrintUi = serviceInputsContainer.querySelector('.fotocopy-print-inputs');
        const designEditUi = serviceInputsContainer.querySelector('.design-edit-inputs');
        const priceSubtotalCol = productLine.querySelector('.col-price-subtotal');
        let manualPriceInput = productLine.querySelector('.manual-price-input');

        // Reset all service UIs
        serviceInputsContainer.style.display = 'none';
        fotocopyPrintUi.style.display = 'none';
        designEditUi.style.display = 'none';
        if (manualPriceInput) manualPriceInput.remove(); // Remove existing manual price input

        if (!selectedOption || !selectedOption.value) {
            calculateTotals();
            return;
        }

        const productName = selectedOption.dataset.name.toLowerCase();
        const productUnit = (selectedOption.dataset.unit || "").toLowerCase();
        const productPrice = parseFloat(selectedOption.dataset.price);

        if (isFotocopyPrintProduct(productName, productUnit, productPrice)) {
            serviceInputsContainer.style.display = 'block';
            fotocopyPrintUi.style.display = 'flex'; // Use flex for row items
            // Potentially auto-fill service-calculated-price if productPrice is per page/item
            const serviceCalculatedPriceField = fotocopyPrintUi.querySelector('.service-calculated-price');
            if (serviceCalculatedPriceField) serviceCalculatedPriceField.value = productPrice; // Default to product's price

            // For fotocopy/print, quantity might become "pages" or "sets", adjust if needed
            // productLine.querySelector('.quantity-input').value = 1; // Or based on pages
        } else if (isDesignEditProduct(productName, productUnit, productPrice)) {
            serviceInputsContainer.style.display = 'block';
            designEditUi.style.display = 'flex';

            // Add manual price input directly into the price/subtotal column for these services
            if (!manualPriceInput) {
                manualPriceInput = document.createElement('input');
                manualPriceInput.type = 'number';
                manualPriceInput.classList.add('form-control', 'form-control-sm', 'mb-1', 'manual-price-input');
                manualPriceInput.placeholder = 'Harga Manual Item';
                manualPriceInput.name = productLine.querySelector('.product-select').name.replace('[id]', '[manual_price]'); // products[index][manual_price]
                manualPriceInput.value = productPrice; // Default to product's price (likely 0)

                priceSubtotalCol.insertBefore(manualPriceInput, priceSubtotalCol.querySelector('.subtotal-display'));
                manualPriceInput.addEventListener('input', calculateTotals);
                manualPriceInput.addEventListener('change', calculateTotals);
            }
        }

        // Add listeners to new service inputs if any
        serviceInputsContainer.querySelectorAll('input, select').forEach(input => {
            input.removeEventListener('input', calculateTotals); // Avoid multiple listeners
            input.removeEventListener('change', calculateTotals);
            input.addEventListener('input', calculateTotals);
            input.addEventListener('change', calculateTotals);
        });

        calculateTotals();
    }


    addProductLineButton.addEventListener('click', function () {
        const newLine = createProductLine(productLineIndex++); // Increment after use for next line
        productLinesContainer.appendChild(newLine);
        attachEventListenersToLine(newLine);
        handleProductSelection(newLine); // Handle initial state of the new line
        // calculateTotals(); // handleProductSelection calls calculateTotals
    });

    function attachEventListenersToLine(line) {
        const productSelect = line.querySelector('.product-select');
        const quantityInput = line.querySelector('.quantity-input');
        const removeButton = line.querySelector('.remove-product-line');

        productSelect.addEventListener('change', () => handleProductSelection(line));
        quantityInput.addEventListener('input', calculateTotals);
        quantityInput.addEventListener('change', calculateTotals);

        removeButton.addEventListener('click', function () {
            line.remove();
            calculateTotals();
        });

        // Attach to any service inputs that might be there from start (e.g. validation reload)
        line.querySelectorAll('.service-inputs input, .service-inputs select').forEach(input => {
             input.addEventListener('input', calculateTotals);
             input.addEventListener('change', calculateTotals);
        });
        const manualPriceInput = line.querySelector('.manual-price-input');
        if(manualPriceInput){
            manualPriceInput.addEventListener('input', calculateTotals);
            manualPriceInput.addEventListener('change', calculateTotals);
        }
    }

    // Attach listeners to existing lines and handle product selection for them
    document.querySelectorAll('.product-line').forEach(line => {
        attachEventListenersToLine(line);
        handleProductSelection(line); // Important to initialize UI based on pre-filled data
    });

    discountInput.addEventListener('input', calculateTotals);
    discountInput.addEventListener('change', calculateTotals);

    // Initial calculation for existing lines on page load
    // calculateTotals(); // This is now called by handleProductSelection for each line

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
