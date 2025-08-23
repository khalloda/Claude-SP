<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = isset($quote) ? I18n::t('quotes.edit') : I18n::t('quotes.create');
$showNav = true;

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= $title ?></h1>
        <a href="/quotes" class="btn btn-secondary"><?= I18n::t('actions.back') ?></a>
    </div>

    <form id="quoteForm" method="POST" action="<?= isset($quote) ? '/quotes/' . $quote['id'] . '/update' : '/quotes/store' ?>">
        <?= Helpers::csrfField() ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Quote Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="client_id" class="form-label"><?= I18n::t('quotes.client') ?> *</label>
                                    <select name="client_id" id="client_id" class="form-control" required>
                                        <option value="">Select Client</option>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?= $client['id'] ?>" 
                                                    <?= (isset($quote) && $quote['client_id'] == $client['id']) ? 'selected' : '' ?>>
                                                <?= Helpers::escape($client['name']) ?> (<?= ucfirst($client['type']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- NEW: Currency Selection -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="currency_code" class="form-label">Currency *</label>
                                    <select name="currency_code" id="currency_code" class="form-control currency-selector" required>
                                        <?= Helpers::getCurrencyOptions($quote['currency_code'] ?? null) ?>
                                    </select>
                                    <div class="currency-conversion-info mt-1" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="notes" class="form-label"><?= I18n::t('quotes.notes') ?></label>
                            <textarea name="notes" id="notes" class="form-control" rows="3"><?= isset($quote) ? Helpers::escape($quote['notes']) : '' ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Quote Items -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>Quote Items</h3>
                        <button type="button" onclick="addQuoteItem()" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="quote-items-container">
                            <?php if (isset($items) && !empty($items)): ?>
                                <?php foreach ($items as $index => $item): ?>
                                    <!-- Existing items will be rendered here -->
                                    <div class="quote-item-row" data-index="<?= $index ?>">
                                        <!-- Item form fields... -->
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div id="no-items-message" class="text-center py-4 text-muted" style="<?= (isset($items) && !empty($items)) ? 'display: none;' : '' ?>">
                            <i class="fas fa-box-open fa-3x mb-3 opacity-50"></i>
                            <p>No items added yet. Click "Add Item" to get started.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quote Summary -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Quote Summary</h3>
                    </div>
                    <div class="card-body">
                        <div class="summary-row mb-2">
                            <div class="d-flex justify-content-between">
                                <span>Items Subtotal:</span>
                                <span id="items-subtotal" class="currency-amount">0.00 <span class="currency-symbol">ج.م</span></span>
                            </div>
                        </div>
                        
                        <div class="summary-row mb-2">
                            <div class="d-flex justify-content-between">
                                <span>Items Tax:</span>
                                <span id="items-tax" class="currency-amount">0.00 <span class="currency-symbol">ج.م</span></span>
                            </div>
                        </div>
                        
                        <div class="summary-row mb-2">
                            <div class="d-flex justify-content-between">
                                <span>Items Discount:</span>
                                <span id="items-discount" class="currency-amount text-success">-0.00 <span class="currency-symbol">ج.م</span></span>
                            </div>
                        </div>

                        <!-- Global Tax & Discount -->
                        <hr>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Global Tax</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" id="global_tax_value" name="global_tax_value" 
                                           step="0.01" min="0" value="<?= isset($quote) ? $quote['global_tax_value'] : 0 ?>" 
                                           onchange="calculateTotals()">
                                    <select class="form-control" id="global_tax_type" name="global_tax_type" onchange="calculateTotals()">
                                        <option value="percent" <?= (isset($quote) && $quote['global_tax_type'] === 'percent') ? 'selected' : '' ?>">%</option>
                                        <option value="amount" <?= (isset($quote) && $quote['global_tax_type'] === 'amount') ? 'selected' : '' ?>>Amount</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <label class="form-label">Global Discount</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" id="global_discount_value" name="global_discount_value" 
                                           step="0.01" min="0" value="<?= isset($quote) ? $quote['global_discount_value'] : 0 ?>" 
                                           onchange="calculateTotals()">
                                    <select class="form-control" id="global_discount_type" name="global_discount_type" onchange="calculateTotals()">
                                        <option value="percent" <?= (isset($quote) && $quote['global_discount_type'] === 'percent') ? 'selected' : '' ?>">%</option>
                                        <option value="amount" <?= (isset($quote) && $quote['global_discount_type'] === 'amount') ? 'selected' : '' ?>>Amount</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="summary-row mb-2">
                            <div class="d-flex justify-content-between">
                                <span>Global Tax:</span>
                                <span id="global-tax" class="currency-amount">0.00 <span class="currency-symbol">ج.م</span></span>
                            </div>
                        </div>
                        
                        <div class="summary-row mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Global Discount:</span>
                                <span id="global-discount" class="currency-amount text-success">-0.00 <span class="currency-symbol">ج.م</span></span>
                            </div>
                        </div>

                        <hr>
                        
                        <div class="summary-row">
                            <div class="d-flex justify-content-between fs-5 fw-bold">
                                <span>Grand Total:</span>
                                <span id="grand-total" class="currency-amount text-primary">0.00 <span class="currency-symbol">ج.م</span></span>
                            </div>
                        </div>

                        <!-- Currency conversion display (if enabled) -->
                        <div id="currency-conversion-display" class="mt-3 p-2 bg-light rounded" style="display: none;">
                            <small class="text-muted">
                                <i class="fas fa-exchange-alt"></i>
                                <span id="conversion-text"></span>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="card mt-3">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i>
                            <?= isset($quote) ? I18n::t('actions.update') : I18n::t('actions.create') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hidden fields for exchange rate tracking -->
        <input type="hidden" name="exchange_rate" id="exchange_rate" value="<?= isset($quote) ? $quote['exchange_rate'] : 1 ?>">
    </form>
</div>

<script>
let itemIndex = <?= isset($items) ? count($items) : 0 ?>;

// Enhanced addQuoteItem function with currency support
function addQuoteItem() {
    const container = document.getElementById('quote-items-container');
    const noItemsMessage = document.getElementById('no-items-message');
    
    // Hide no items message
    if (noItemsMessage) {
        noItemsMessage.style.display = 'none';
    }
    
    const currentCurrency = getCurrentCurrency();
    const currencySymbol = getCurrencySymbol(currentCurrency);
    
    const itemDiv = document.createElement('div');
    itemDiv.className = 'quote-item-row border rounded p-3 mb-3';
    itemDiv.setAttribute('data-index', itemIndex);
    
    itemDiv.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Product *</label>
                <select name="product_ids[]" class="form-control product-select" onchange="loadProductDetails(this)" required>
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= $product['id'] ?>" 
                                data-price="<?= $product['sale_price'] ?>" 
                                data-available="<?= $product['total_qty'] - $product['reserved_quotes'] - $product['reserved_orders'] ?>">
                            <?= Helpers::escape($product['code']) ?> - <?= Helpers::escape($product['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="available-qty text-muted"></small>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Quantity *</label>
                <input type="number" name="quantities[]" class="form-control quantity-input" 
                       step="0.01" min="0.01" value="1" onchange="calculateLineTotal(this)" required>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Unit Price *</label>
                <input type="number" name="prices[]" class="form-control price-input" 
                       step="0.01" min="0" value="0" onchange="calculateLineTotal(this)" required>
                <small class="text-muted">${currencySymbol}</small>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Tax</label>
                <div class="input-group input-group-sm">
                    <input type="number" name="taxes[]" class="form-control tax-input" 
                           step="0.01" min="0" value="0" onchange="calculateLineTotal(this)">
                    <select name="tax_types[]" class="form-control" onchange="calculateLineTotal(this)">
                        <option value="percent">%</option>
                        <option value="amount">${currencySymbol}</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Discount</label>
                <div class="input-group input-group-sm">
                    <input type="number" name="discounts[]" class="form-control discount-input" 
                           step="0.01" min="0" value="0" onchange="calculateLineTotal(this)">
                    <select name="discount_types[]" class="form-control" onchange="calculateLineTotal(this)">
                        <option value="percent">%</option>
                        <option value="amount">${currencySymbol}</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-10">
                <div class="text-end">
                    <strong>Line Total: <span class="line-total">${formatCurrency(0, currentCurrency)}</span></strong>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" onclick="removeQuoteItem(this)" class="btn btn-danger btn-sm w-100">Remove</button>
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
    itemIndex++;
    calculateTotals();
}

// Enhanced calculation functions with currency support
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
    
    const currentCurrency = getCurrentCurrency();
    row.querySelector('.line-total').textContent = formatCurrency(Math.max(0, lineTotal), currentCurrency);
    
    // Recalculate totals
    calculateTotals();
}

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
    
    // Get current currency
    const currentCurrency = getCurrentCurrency();
    
    // Update displays with currency formatting
    document.getElementById('items-subtotal').innerHTML = formatCurrency(itemsSubtotal, currentCurrency);
    document.getElementById('items-tax').innerHTML = formatCurrency(itemsTax, currentCurrency);
    document.getElementById('items-discount').innerHTML = formatCurrency(itemsDiscount, currentCurrency);
    document.getElementById('global-tax').innerHTML = formatCurrency(globalTax, currentCurrency);
    document.getElementById('global-discount').innerHTML = formatCurrency(globalDiscount, currentCurrency);
    document.getElementById('grand-total').innerHTML = formatCurrency(Math.max(0, grandTotal), currentCurrency);
    
    // Show currency conversion if enabled
    showCurrencyConversion(grandTotal, currentCurrency);
}

// Currency helper functions
function getCurrentCurrency() {
    const currencySelector = document.getElementById('currency_code');
    return currencySelector ? currencySelector.value : 'EGP';
}

function getCurrencySymbol(currency) {
    if (window.currencyManager) {
        return window.currencyManager.getCurrencySymbol(currency);
    }
    return currency === 'EGP' ? 'ج.م' : '$';
}

function showCurrencyConversion(amount, fromCurrency) {
    const conversionDisplay = document.getElementById('currency-conversion-display');
    const conversionText = document.getElementById('conversion-text');
    
    if (window.currencyManager && conversionDisplay) {
        const primaryCurrency = window.currencyManager.getPrimaryCurrency();
        
        if (fromCurrency !== primaryCurrency) {
            const convertedAmount = window.currencyManager.convertCurrency(amount, fromCurrency, primaryCurrency);
            const formattedConverted = window.currencyManager.formatCurrency(convertedAmount, primaryCurrency);
            
            conversionText.textContent = `≈ ${formattedConverted}`;
            conversionDisplay.style.display = 'block';
        } else {
            conversionDisplay.style.display = 'none';
        }
    }
}

// Remove quote item
function removeQuoteItem(button) {
    button.closest('.quote-item-row').remove();
    
    // Show no items message if no items left
    const remainingItems = document.querySelectorAll('.quote-item-row');
    const noItemsMessage = document.getElementById('no-items-message');
    
    if (remainingItems.length === 0 && noItemsMessage) {
        noItemsMessage.style.display = 'block';
    }
    
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
