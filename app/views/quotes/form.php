<?php
use App\Core\I18n;
use App\Core\Helpers;

$isEdit = isset($quote);
$title = ($isEdit ? 'Edit' : 'Create') . ' Quote - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">
            <?= $isEdit ? 'Edit' : 'Create' ?> Quote
            <?php if ($isEdit): ?>
                <span style="color: #666; font-size: 0.8rem;">#<?= str_pad($quote['id'], 4, '0', STR_PAD_LEFT) ?></span>
            <?php endif; ?>
        </h1>
    </div>
    
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/quotes/' . $quote['id'] : '/quotes' ?>" id="quoteForm">
            <?= Helpers::csrfField() ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="client_id" class="form-label">Client *</label>
                        <select name="client_id" id="client_id" class="form-control" required>
                            <option value="">Select Client</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>" 
                                        <?= ($isEdit && $quote['client_id'] == $client['id']) ? 'selected' : '' ?>>
                                    <?= Helpers::escape($client['name']) ?> (<?= ucfirst($client['type']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea 
                            name="notes" 
                            id="notes" 
                            class="form-control" 
                            rows="3"
                        ><?= $isEdit ? Helpers::escape($quote['notes']) : Helpers::old('notes') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Quote Items -->
            <div style="margin-top: 2rem;">
                <h3>Quote Items</h3>
                <div id="quote-items-container">
                    <?php if ($isEdit && !empty($items)): ?>
                        <?php foreach ($items as $index => $item): ?>
                            <div class="quote-item-row" style="border: 1px solid #dee2e6; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; background: #f8f9fa;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Product *</label>
                                        <select name="products[]" class="form-control product-select" required onchange="loadProductDetails(this)">
                                            <option value="">Select Product</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?= $product['id'] ?>" 
                                                        data-price="<?= $product['sale_price'] ?>"
                                                        data-available="<?= $product['total_qty'] - $product['reserved_quotes'] - $product['reserved_orders'] ?>"
                                                        <?= $product['id'] == $item['product_id'] ? 'selected' : '' ?>>
                                                    <?= Helpers::escape($product['code']) ?> - <?= Helpers::escape($product['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="available-qty"></small>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label>Quantity *</label>
                                        <input type="number" name="quantities[]" class="form-control quantity-input" 
                                               step="0.01" min="0" value="<?= $item['qty'] ?>" required onchange="calculateLineTotal(this)">
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label>Unit Price *</label>
                                        <input type="number" name="prices[]" class="form-control price-input" 
                                               step="0.01" min="0" value="<?= $item['price'] ?>" required onchange="calculateLineTotal(this)">
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label>Tax</label>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <input type="number" name="taxes[]" class="form-control tax-input" 
                                                   step="0.01" min="0" value="<?= $item['tax'] ?>" onchange="calculateLineTotal(this)" style="flex: 2;">
                                            <select name="tax_types[]" class="form-control" style="flex: 1;" onchange="calculateLineTotal(this)">
                                                <option value="percent" <?= $item['tax_type'] === 'percent' ? 'selected' : '' ?>>%</option>
                                                <option value="amount" <?= $item['tax_type'] === 'amount' ? 'selected' : '' ?>>$</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label>Discount</label>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <input type="number" name="discounts[]" class="form-control discount-input" 
                                                   step="0.01" min="0" value="<?= $item['discount'] ?>" onchange="calculateLineTotal(this)" style="flex: 2;">
                                            <select name="discount_types[]" class="form-control" style="flex: 1;" onchange="calculateLineTotal(this)">
                                                <option value="percent" <?= $item['discount_type'] === 'percent' ? 'selected' : '' ?>>%</option>
                                                <option value="amount" <?= $item['discount_type'] === 'amount' ? 'selected' : '' ?>>$</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-1">
                                        <label>&nbsp;</label>
                                        <button type="button" onclick="removeQuoteItem(this)" class="btn btn-danger btn-sm" style="display: block; width: 100%;">Remove</button>
                                    </div>
                                </div>
                                
                                <div style="margin-top: 0.5rem; text-align: right;">
                                    <strong>Line Total: <span class="line-total">$0.00</span></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <button type="button" onclick="addQuoteItem()" class="btn btn-secondary">Add Item</button>
            </div>
            
            <!-- Global Tax & Discount -->
            <div class="row" style="margin-top: 2rem;">
                <div class="col-md-6">
                    <h4>Global Tax</h4>
                    <div style="display: flex; gap: 1rem; align-items: end;">
                        <div style="flex: 2;">
                            <label>Tax Value</label>
                            <input type="number" name="global_tax_value" id="global_tax_value" class="form-control" 
                                   step="0.01" min="0" value="<?= $isEdit ? $quote['global_tax_value'] : '0' ?>" onchange="calculateTotals()">
                        </div>
                        <div style="flex: 1;">
                            <label>Type</label>
                            <select name="global_tax_type" id="global_tax_type" class="form-control" onchange="calculateTotals()">
                                <option value="percent" <?= ($isEdit && $quote['global_tax_type'] === 'percent') ? 'selected' : '' ?>>Percent (%)</option>
                                <option value="amount" <?= ($isEdit && $quote['global_tax_type'] === 'amount') ? 'selected' : '' ?>>Amount ($)</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h4>Global Discount</h4>
                    <div style="display: flex; gap: 1rem; align-items: end;">
                        <div style="flex: 2;">
                            <label>Discount Value</label>
                            <input type="number" name="global_discount_value" id="global_discount_value" class="form-control" 
                                   step="0.01" min="0" value="<?= $isEdit ? $quote['global_discount_value'] : '0' ?>" onchange="calculateTotals()">
                        </div>
                        <div style="flex: 1;">
                            <label>Type</label>
                            <select name="global_discount_type" id="global_discount_type" class="form-control" onchange="calculateTotals()">
                                <option value="percent" <?= ($isEdit && $quote['global_discount_type'] === 'percent') ? 'selected' : '' ?>>Percent (%)</option>
                                <option value="amount" <?= ($isEdit && $quote['global_discount_type'] === 'amount') ? 'selected' : '' ?>>Amount ($)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Totals Summary -->
            <div class="totals-summary" style="margin-top: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 8px;">
                <h4>Quote Summary</h4>
                <div class="row">
                    <div class="col-md-6">
                        <table style="width: 100%;">
                            <tr>
                                <td>Items Subtotal:</td>
                                <td style="text-align: right;"><span id="items-subtotal">$0.00</span></td>
                            </tr>
                            <tr>
                                <td>Items Tax:</td>
                                <td style="text-align: right;"><span id="items-tax">$0.00</span></td>
                            </tr>
                            <tr>
                                <td>Items Discount:</td>
                                <td style="text-align: right;"><span id="items-discount">$0.00</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table style="width: 100%;">
                            <tr>
                                <td>Global Tax:</td>
                                <td style="text-align: right;"><span id="global-tax">$0.00</span></td>
                            </tr>
                            <tr>
                                <td>Global Discount:</td>
                                <td style="text-align: right;"><span id="global-discount">$0.00</span></td>
                            </tr>
                            <tr style="font-weight: bold; font-size: 1.2rem; border-top: 2px solid #667eea;">
                                <td>Grand Total:</td>
                                <td style="text-align: right;"><span id="grand-total">$0.00</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Update' : 'Create' ?> Quote
                </button>
                <a href="/quotes" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Product data for JavaScript calculations
const products = <?= json_encode($products) ?>;

// Add quote item
function addQuoteItem() {
    const container = document.getElementById('quote-items-container');
    
    const itemDiv = document.createElement('div');
    itemDiv.className = 'quote-item-row';
    itemDiv.style.cssText = 'border: 1px solid #dee2e6; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; background: #f8f9fa;';
    
    let productOptions = '<option value="">Select Product</option>';
    products.forEach(product => {
        const available = product.total_qty - product.reserved_quotes - product.reserved_orders;
        productOptions += `<option value="${product.id}" data-price="${product.sale_price}" data-available="${available}">
                          ${product.code} - ${product.name}</option>`;
    });
    
    itemDiv.innerHTML = `
        <div class="row">
            <div class="col-md-3">
                <label>Product *</label>
                <select name="products[]" class="form-control product-select" required onchange="loadProductDetails(this)">
                    ${productOptions}
                </select>
                <small class="available-qty" style="color: #666;"></small>
            </div>
            
            <div class="col-md-2">
                <label>Quantity *</label>
                <input type="number" name="quantities[]" class="form-control quantity-input" 
                       step="0.01" min="0" value="1" required onchange="calculateLineTotal(this)">
            </div>
            
            <div class="col-md-2">
                <label>Unit Price *</label>
                <input type="number" name="prices[]" class="form-control price-input" 
                       step="0.01" min="0" value="0" required onchange="calculateLineTotal(this)">
            </div>
            
            <div class="col-md-2">
                <label>Tax</label>
                <div style="display: flex; gap: 0.25rem;">
                    <input type="number" name="taxes[]" class="form-control tax-input" 
                           step="0.01" min="0" value="0" onchange="calculateLineTotal(this)" style="flex: 2;">
                    <select name="tax_types[]" class="form-control" style="flex: 1;" onchange="calculateLineTotal(this)">
                        <option value="percent">%</option>
                        <option value="amount">$</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-2">
                <label>Discount</label>
                <div style="display: flex; gap: 0.25rem;">
                    <input type="number" name="discounts[]" class="form-control discount-input" 
                           step="0.01" min="0" value="0" onchange="calculateLineTotal(this)" style="flex: 2;">
                    <select name="discount_types[]" class="form-control" style="flex: 1;" onchange="calculateLineTotal(this)">
                        <option value="percent">%</option>
                        <option value="amount">$</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-1">
                <label>&nbsp;</label>
                <button type="button" onclick="removeQuoteItem(this)" class="btn btn-danger btn-sm" style="display: block; width: 100%;">Remove</button>
            </div>
        </div>
        
        <div style="margin-top: 0.5rem; text-align: right;">
            <strong>Line Total: <span class="line-total">$0.00</span></strong>
        </div>
    `;
    
    container.appendChild(itemDiv);
}

// Remove quote item
function removeQuoteItem(button) {
    button.closest('.quote-item-row').remove();
    calculateTotals();
}

// Load product details when product is selected
function loadProductDetails(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const row = selectElement.closest('.quote-item-row');
    const priceInput = row.querySelector('.price-input');
    const availableQtySpan = row.querySelector('.available-qty');
    
    if (selectedOption.value) {
        const price = selectedOption.getAttribute('data-price');
        const available = selectedOption.getAttribute('data-available');
        
        priceInput.value = price;
        availableQtySpan.textContent = `Available: ${available}`;
        availableQtySpan.style.color = available > 0 ? '#28a745' : '#dc3545';
        
        calculateLineTotal(selectElement);
    } else {
        priceInput.value = '0';
        availableQtySpan.textContent = '';
    }
}

// Calculate line total for a specific item
function calculateLineTotal(element) {
    const row = element.closest('.quote-item-row');
    const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const tax = parseFloat(row.querySelector('.tax-input').value) || 0;
    const taxType = row.querySelector('select[name="tax_types[]"]').value;
    const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
    const discountType = row.querySelector('select[name="discount_types[]"]').value;
    
    const subtotal = qty * price;
    
    // Calculate tax
    const taxAmount = taxType === 'percent' ? (subtotal * tax / 100) : tax;
    
    // Calculate discount
    const discountAmount = discountType === 'percent' ? (subtotal * discount / 100) : discount;
    
    const lineTotal = subtotal + taxAmount - discountAmount;
    
    row.querySelector('.line-total').textContent = formatCurrency(Math.max(0, lineTotal));
    
    // Recalculate totals
    calculateTotals();
}

// Calculate all totals
function calculateTotals() {
    let itemsSubtotal = 0;
    let itemsTax = 0;
    let itemsDiscount = 0;
    
    // Sum up all line items
    document.querySelectorAll('.quote-item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const tax = parseFloat(row.querySelector('.tax-input').value) || 0;
        const taxType = row.querySelector('select[name="tax_types[]"]').value;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        const discountType = row.querySelector('select[name="discount_types[]"]').value;
        
        const subtotal = qty * price;
        itemsSubtotal += subtotal;
        
        // Calculate tax
        const taxAmount = taxType === 'percent' ? (subtotal * tax / 100) : tax;
        itemsTax += taxAmount;
        
        // Calculate discount
        const discountAmount = discountType === 'percent' ? (subtotal * discount / 100) : discount;
        itemsDiscount += discountAmount;
    });
    
    // Global tax and discount
    const globalTaxValue = parseFloat(document.getElementById('global_tax_value').value) || 0;
    const globalTaxType = document.getElementById('global_tax_type').value;
    const globalDiscountValue = parseFloat(document.getElementById('global_discount_value').value) || 0;
    const globalDiscountType = document.getElementById('global_discount_type').value;
    
    const globalTax = globalTaxType === 'percent' ? (itemsSubtotal * globalTaxValue / 100) : globalTaxValue;
    const globalDiscount = globalDiscountType === 'percent' ? (itemsSubtotal * globalDiscountValue / 100) : globalDiscountValue;
    
    const totalTax = itemsTax + globalTax;
    const totalDiscount = itemsDiscount + globalDiscount;
    const grandTotal = itemsSubtotal + totalTax - totalDiscount;
    
    // Update displays
    document.getElementById('items-subtotal').textContent = formatCurrency(itemsSubtotal);
    document.getElementById('items-tax').textContent = formatCurrency(itemsTax);
    document.getElementById('items-discount').textContent = formatCurrency(itemsDiscount);
    document.getElementById('global-tax').textContent = formatCurrency(globalTax);
    document.getElementById('global-discount').textContent = formatCurrency(globalDiscount);
    document.getElementById('grand-total').textContent = formatCurrency(Math.max(0, grandTotal));
}

// Format currency
function formatCurrency(amount) {
    return '$' + amount.toFixed(2);
}

// Initialize calculations on page load
document.addEventListener('DOMContentLoaded', function() {
    // Calculate line totals for existing items
    document.querySelectorAll('.quote-item-row').forEach(row => {
        calculateLineTotal(row.querySelector('.quantity-input'));
    });
    
    // If no items exist, add one
    if (document.querySelectorAll('.quote-item-row').length === 0) {
        addQuoteItem();
    }
    
    // Form validation
    document.getElementById('quoteForm').addEventListener('submit', function(e) {
        const items = document.querySelectorAll('.quote-item-row');
        if (items.length === 0) {
            alert('Please add at least one item to the quote');
            e.preventDefault();
            return false;
        }
        
        let hasValidItem = false;
        items.forEach(row => {
            const productSelect = row.querySelector('.product-select');
            const qtyInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('.price-input');
            
            if (productSelect.value && parseFloat(qtyInput.value) > 0 && parseFloat(priceInput.value) > 0) {
                hasValidItem = true;
            }
        });
        
        if (!hasValidItem) {
            alert('Please ensure at least one item has a product, quantity, and price');
            e.preventDefault();
            return false;
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
