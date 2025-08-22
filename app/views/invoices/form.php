<?php
use App\Core\I18n;
use App\Core\Helpers;

$isEdit = isset($invoice);
$title = $isEdit ? 
    I18n::t('navigation.invoices') . ' - ' . I18n::t('actions.edit') . ' #' . str_pad($invoice['id'], 4, '0', STR_PAD_LEFT) :
    I18n::t('navigation.invoices') . ' - ' . I18n::t('actions.create');

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= $isEdit ? 'Edit Invoice #' . str_pad($invoice['id'], 4, '0', STR_PAD_LEFT) : 'Create New Invoice' ?></h1>
        <a href="<?= $isEdit ? '/invoices/' . $invoice['id'] : '/invoices' ?>" class="btn btn-secondary">
            <?= I18n::t('actions.back') ?>
        </a>
    </div>

    <form method="POST" action="<?= $isEdit ? '/invoices/' . $invoice['id'] : '/invoices' ?>" id="invoiceForm">
        <?= Helpers::csrfField() ?>
        
        <div class="row">
            <!-- Left Column - Main Form -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Invoice Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="client_id" class="form-label">
                                        <?= I18n::t('navigation.client') ?> <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" id="client_id" name="client_id" required>
                                        <option value="">Select a client...</option>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?= $client['id'] ?>" 
                                                    <?= ($isEdit && $invoice['client_id'] == $client['id']) || Helpers::old('client_id') == $client['id'] ? 'selected' : '' ?>>
                                                <?= Helpers::escape($client['name']) ?> (<?= ucfirst($client['type']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= Helpers::getError('client_id') ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-control" id="status" name="status" <?= $isEdit ? '' : 'disabled' ?>>
                                        <option value="open" <?= ($isEdit && $invoice['status'] == 'open') ? 'selected' : '' ?>>Open</option>
                                        <option value="partial" <?= ($isEdit && $invoice['status'] == 'partial') ? 'selected' : '' ?>>Partial</option>
                                        <option value="paid" <?= ($isEdit && $invoice['status'] == 'paid') ? 'selected' : '' ?>>Paid</option>
                                        <option value="void" <?= ($isEdit && $invoice['status'] == 'void') ? 'selected' : '' ?>>Void</option>
                                    </select>
                                    <?php if (!$isEdit): ?>
                                        <small class="text-muted">Status will be set to "Open" for new invoices</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes" class="form-label"><?= I18n::t('common.notes') ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Optional notes for this invoice..."><?= $isEdit ? Helpers::escape($invoice['notes'] ?? '') : Helpers::old('notes') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Invoice Items</h4>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addInvoiceItem()">
                            + Add Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="invoice-items">
                            <?php if ($isEdit && !empty($items)): ?>
                                <?php foreach ($items as $index => $item): ?>
                                    <div class="invoice-item-row" data-index="<?= $index ?>">
                                        <div class="row align-items-end">
                                            <div class="col-md-3">
                                                <label class="form-label">Product</label>
                                                <select class="form-control product-select" name="product_id[]" onchange="updateProductDetails(this)" required>
                                                    <option value="">Select product...</option>
                                                    <?php foreach ($products as $product): ?>
                                                        <option value="<?= $product['id'] ?>" 
                                                                data-price="<?= $product['sale_price'] ?>"
                                                                data-available="<?= $product['total_qty'] ?>"
                                                                <?= $item['product_id'] == $product['id'] ? 'selected' : '' ?>>
                                                            [<?= $product['code'] ?>] <?= Helpers::escape($product['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">Qty</label>
                                                <input type="number" class="form-control qty-input" name="qty[]" 
                                                       step="0.01" min="0.01" value="<?= $item['qty'] ?>" onchange="calculateItemTotal(this)" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Price</label>
                                                <input type="number" class="form-control price-input" name="price[]" 
                                                       step="0.01" min="0" value="<?= $item['price'] ?>" onchange="calculateItemTotal(this)" required>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">Tax</label>
                                                <input type="number" class="form-control tax-input" name="tax[]" 
                                                       step="0.01" min="0" value="<?= $item['tax'] ?>" onchange="calculateItemTotal(this)">
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">Type</label>
                                                <select class="form-control tax-type-select" name="tax_type[]" onchange="calculateItemTotal(this)">
                                                    <option value="percent" <?= $item['tax_type'] == 'percent' ? 'selected' : '' ?>>%</option>
                                                    <option value="amount" <?= $item['tax_type'] == 'amount' ? 'selected' : '' ?>>$</option>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">Disc</label>
                                                <input type="number" class="form-control discount-input" name="discount[]" 
                                                       step="0.01" min="0" value="<?= $item['discount'] ?>" onchange="calculateItemTotal(this)">
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">Type</label>
                                                <select class="form-control discount-type-select" name="discount_type[]" onchange="calculateItemTotal(this)">
                                                    <option value="percent" <?= $item['discount_type'] == 'percent' ? 'selected' : '' ?>>%</option>
                                                    <option value="amount" <?= $item['discount_type'] == 'amount' ? 'selected' : '' ?>>$</option>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">Total</label>
                                                <div class="item-total font-weight-bold">$0.00</div>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-sm btn-danger" onclick="removeInvoiceItem(this)">×</button>
                                            </div>
                                        </div>
                                        <hr>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Empty state - will be populated by JavaScript -->
                                <div class="text-center text-muted py-4" id="no-items-message">
                                    No items added yet. Click "Add Item" to begin.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Calculations & Actions -->
            <div class="col-md-4">
                <!-- Summary Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Invoice Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Items Subtotal:</span>
                            <span id="items-subtotal">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Items Tax:</span>
                            <span id="items-tax">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Items Discount:</span>
                            <span id="items-discount" style="color: #28a745;">-$0.00</span>
                        </div>
                        <hr>
                        
                        <!-- Global Tax -->
                        <div class="form-group">
                            <label class="form-label">Global Tax</label>
                            <div class="row">
                                <div class="col-8">
                                    <input type="number" class="form-control" id="global_tax_value" name="global_tax_value" 
                                           step="0.01" min="0" value="<?= $isEdit ? $invoice['global_tax_value'] : Helpers::old('global_tax_value', 0) ?>" 
                                           onchange="calculateTotals()">
                                </div>
                                <div class="col-4">
                                    <select class="form-control" id="global_tax_type" name="global_tax_type" onchange="calculateTotals()">
                                        <option value="percent" <?= ($isEdit && $invoice['global_tax_type'] == 'percent') || Helpers::old('global_tax_type') == 'percent' ? 'selected' : '' ?>>%</option>
                                        <option value="amount" <?= ($isEdit && $invoice['global_tax_type'] == 'amount') || Helpers::old('global_tax_type') == 'amount' ? 'selected' : '' ?>>$</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Global Discount -->
                        <div class="form-group">
                            <label class="form-label">Global Discount</label>
                            <div class="row">
                                <div class="col-8">
                                    <input type="number" class="form-control" id="global_discount_value" name="global_discount_value" 
                                           step="0.01" min="0" value="<?= $isEdit ? $invoice['global_discount_value'] : Helpers::old('global_discount_value', 0) ?>" 
                                           onchange="calculateTotals()">
                                </div>
                                <div class="col-4">
                                    <select class="form-control" id="global_discount_type" name="global_discount_type" onchange="calculateTotals()">
                                        <option value="percent" <?= ($isEdit && $invoice['global_discount_type'] == 'percent') || Helpers::old('global_discount_type') == 'percent' ? 'selected' : '' ?>>%</option>
                                        <option value="amount" <?= ($isEdit && $invoice['global_discount_type'] == 'amount') || Helpers::old('global_discount_type') == 'amount' ? 'selected' : '' ?>>$</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Global Tax:</span>
                            <span id="global-tax-amount">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Global Discount:</span>
                            <span id="global-discount-amount" style="color: #28a745;">-$0.00</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3" style="font-size: 1.2rem; font-weight: bold;">
                            <span>Grand Total:</span>
                            <span id="grand-total">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <?= $isEdit ? I18n::t('actions.update') : I18n::t('actions.create') ?> Invoice
                        </button>
                        <a href="<?= $isEdit ? '/invoices/' . $invoice['id'] : '/invoices' ?>" class="btn btn-secondary btn-block">
                            <?= I18n::t('actions.cancel') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.invoice-item-row {
    margin-bottom: 1rem;
}

.invoice-item-row:last-child hr {
    display: none;
}

.item-total {
    padding: 0.375rem 0.75rem;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
    text-align: right;
}

#no-items-message {
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-label {
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

/* RTL Support */
[dir="rtl"] .item-total {
    text-align: left;
}

[dir="rtl"] .d-flex.justify-content-between {
    flex-direction: row-reverse;
}
</style>

<script>
let itemIndex = <?= $isEdit && !empty($items) ? count($items) : 0 ?>;
let products = <?= json_encode($products) ?>;

// Add new invoice item row
function addInvoiceItem() {
    const noItemsMessage = document.getElementById('no-items-message');
    if (noItemsMessage) {
        noItemsMessage.style.display = 'none';
    }
    
    const container = document.getElementById('invoice-items');
    const itemHtml = `
        <div class="invoice-item-row" data-index="${itemIndex}">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Product</label>
                    <select class="form-control product-select" name="product_id[]" onchange="updateProductDetails(this)" required>
                        <option value="">Select product...</option>
                        ${products.map(product => `
                            <option value="${product.id}" 
                                    data-price="${product.sale_price}" 
                                    data-available="${product.total_qty}">
                                [${product.code}] ${escapeHtml(product.name)}
                            </option>
                        `).join('')}
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Qty</label>
                    <input type="number" class="form-control qty-input" name="qty[]" 
                           step="0.01" min="0.01" value="1" onchange="calculateItemTotal(this)" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Price</label>
                    <input type="number" class="form-control price-input" name="price[]" 
                           step="0.01" min="0" value="0" onchange="calculateItemTotal(this)" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Tax</label>
                    <input type="number" class="form-control tax-input" name="tax[]" 
                           step="0.01" min="0" value="0" onchange="calculateItemTotal(this)">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Type</label>
                    <select class="form-control tax-type-select" name="tax_type[]" onchange="calculateItemTotal(this)">
                        <option value="percent">%</option>
                        <option value="amount">$</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Disc</label>
                    <input type="number" class="form-control discount-input" name="discount[]" 
                           step="0.01" min="0" value="0" onchange="calculateItemTotal(this)">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Type</label>
                    <select class="form-control discount-type-select" name="discount_type[]" onchange="calculateItemTotal(this)">
                        <option value="percent">%</option>
                        <option value="amount">$</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Total</label>
                    <div class="item-total font-weight-bold">$0.00</div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeInvoiceItem(this)">×</button>
                </div>
            </div>
            <hr>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
    itemIndex++;
}

// Remove invoice item row
function removeInvoiceItem(button) {
    const row = button.closest('.invoice-item-row');
    row.remove();
    
    // Show no items message if no items left
    const remainingItems = document.querySelectorAll('.invoice-item-row');
    if (remainingItems.length === 0) {
        const noItemsMessage = document.getElementById('no-items-message');
        if (noItemsMessage) {
            noItemsMessage.style.display = 'flex';
        }
    }
    
    calculateTotals();
}

// Update product details when product is selected
function updateProductDetails(selectElement) {
    const row = selectElement.closest('.invoice-item-row');
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    
    if (selectedOption.value) {
        const price = parseFloat(selectedOption.dataset.price) || 0;
        const available = parseFloat(selectedOption.dataset.available) || 0;
        
        // Update price field
        const priceInput = row.querySelector('.price-input');
        priceInput.value = price.toFixed(2);
        
        // Update quantity max
        const qtyInput = row.querySelector('.qty-input');
        qtyInput.setAttribute('max', available);
        
        // Show available quantity hint
        let hint = row.querySelector('.qty-hint');
        if (!hint) {
            hint = document.createElement('small');
            hint.className = 'qty-hint text-muted';
            qtyInput.parentNode.appendChild(hint);
        }
        hint.textContent = `Available: ${available}`;
        
        calculateItemTotal(selectElement);
    }
}

// Calculate individual item total
function calculateItemTotal(element) {
    const row = element.closest('.invoice-item-row');
    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const tax = parseFloat(row.querySelector('.tax-input').value) || 0;
    const taxType = row.querySelector('.tax-type-select').value;
    const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
    const discountType = row.querySelector('.discount-type-select').value;
    
    // Calculate subtotal
    const subtotal = qty * price;
    
    // Calculate tax amount
    let taxAmount = 0;
    if (tax > 0) {
        taxAmount = taxType === 'percent' ? (subtotal * tax / 100) : tax;
    }
    
    // Calculate discount amount
    let discountAmount = 0;
    if (discount > 0) {
        discountAmount = discountType === 'percent' ? (subtotal * discount / 100) : discount;
    }
    
    // Calculate final total
    const total = subtotal + taxAmount - discountAmount;
    
    // Update display
    const totalElement = row.querySelector('.item-total');
    totalElement.textContent = formatCurrency(total);
    
    // Recalculate overall totals
    calculateTotals();
}

// Calculate overall totals
function calculateTotals() {
    let itemsSubtotal = 0;
    let itemsTax = 0;
    let itemsDiscount = 0;
    
    // Sum up all item totals
    document.querySelectorAll('.invoice-item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const tax = parseFloat(row.querySelector('.tax-input').value) || 0;
        const taxType = row.querySelector('.tax-type-select').value;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        const discountType = row.querySelector('.discount-type-select').value;
        
        const subtotal = qty * price;
        itemsSubtotal += subtotal;
        
        // Item tax
        if (tax > 0) {
            itemsTax += taxType === 'percent' ? (subtotal * tax / 100) : tax;
        }
        
        // Item discount
        if (discount > 0) {
            itemsDiscount += discountType === 'percent' ? (subtotal * discount / 100) : discount;
        }
    });
    
    // Global tax calculation
    const globalTaxValue = parseFloat(document.getElementById('global_tax_value').value) || 0;
    const globalTaxType = document.getElementById('global_tax_type').value;
    const subtotalAfterItemDiscounts = itemsSubtotal + itemsTax - itemsDiscount;
    let globalTaxAmount = 0;
    
    if (globalTaxValue > 0) {
        globalTaxAmount = globalTaxType === 'percent' ? 
            (subtotalAfterItemDiscounts * globalTaxValue / 100) : globalTaxValue;
    }
    
    // Global discount calculation
    const globalDiscountValue = parseFloat(document.getElementById('global_discount_value').value) || 0;
    const globalDiscountType = document.getElementById('global_discount_type').value;
    let globalDiscountAmount = 0;
    
    if (globalDiscountValue > 0) {
        const baseForDiscount = subtotalAfterItemDiscounts + globalTaxAmount;
        globalDiscountAmount = globalDiscountType === 'percent' ? 
            (baseForDiscount * globalDiscountValue / 100) : globalDiscountValue;
    }
    
    // Calculate grand total
    const grandTotal = subtotalAfterItemDiscounts + globalTaxAmount - globalDiscountAmount;
    
    // Update display
    document.getElementById('items-subtotal').textContent = formatCurrency(itemsSubtotal);
    document.getElementById('items-tax').textContent = formatCurrency(itemsTax);
    document.getElementById('items-discount').textContent = '-' + formatCurrency(itemsDiscount);
    document.getElementById('global-tax-amount').textContent = formatCurrency(globalTaxAmount);
    document.getElementById('global-discount-amount').textContent = '-' + formatCurrency(globalDiscountAmount);
    document.getElementById('grand-total').textContent = formatCurrency(grandTotal);
}

// Format currency
function formatCurrency(amount) {
    return ' + amount.toFixed(2);
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Form validation
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.invoice-item-row');
    
    if (items.length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the invoice.');
        return false;
    }
    
    // Validate each item
    let hasValidItems = false;
    items.forEach(row => {
        const productId = row.querySelector('.product-select').value;
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        
        if (productId && qty > 0 && price > 0) {
            hasValidItems = true;
        }
    });
    
    if (!hasValidItems) {
        e.preventDefault();
        alert('Please ensure all items have a product, quantity, and price.');
        return false;
    }
});

// Initialize calculations on page load
document.addEventListener('DOMContentLoaded', function() {
    // Calculate totals for existing items (edit mode)
    document.querySelectorAll('.invoice-item-row').forEach(row => {
        calculateItemTotal(row.querySelector('.qty-input'));
    });
    
    // Add first item if creating new invoice
    <?php if (!$isEdit): ?>
    if (document.querySelectorAll('.invoice-item-row').length === 0) {
        addInvoiceItem();
    }
    <?php endif; ?>
    
    console.log('Invoice Form Loaded');
    console.log('Edit Mode:', <?= $isEdit ? 'true' : 'false' ?>);
    console.log('Products Available:', products.length);
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
