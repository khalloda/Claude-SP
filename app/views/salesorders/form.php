
<?php
use App\Core\I18n;
use App\Core\Helpers;

$isEdit = isset($salesOrder);
$title = $isEdit ? 
    I18n::t('navigation.sales_orders') . ' - ' . I18n::t('actions.edit') . ' #' . str_pad($salesOrder['id'], 4, '0', STR_PAD_LEFT) :
    I18n::t('navigation.sales_orders') . ' - ' . I18n::t('actions.create');

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= $isEdit ? 'Edit Sales Order #' . str_pad($salesOrder['id'], 4, '0', STR_PAD_LEFT) : 'Create New Sales Order' ?></h1>
        <a href="<?= $isEdit ? '/salesorders/' . $salesOrder['id'] : '/salesorders' ?>" class="btn btn-secondary">
            <?= I18n::t('actions.back') ?>
        </a>
    </div>

    <?php if (!$isEdit): ?>
        <!-- Info for creating new sales order -->
        <div class="alert alert-info mb-4">
            <h5>📋 Sales Order Creation Process</h5>
            <p><strong>Typical workflow:</strong> Sales Orders are usually created by converting approved quotes.</p>
            <div class="row mt-3">
                <div class="col-md-6">
                    <h6>Recommended Process:</h6>
                    <ol>
                        <li>Create a <a href="/quotes/create">Quote</a> first</li>
                        <li>Get client approval</li>
                        <li>Convert quote to sales order</li>
                        <li>Process delivery and invoicing</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h6>Direct Sales Order:</h6>
                    <p>Use this form only when you need to create a sales order directly without a quote (e.g., for repeat orders or special cases).</p>
                    <a href="/quotes/create" class="btn btn-primary">Create Quote Instead</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isEdit && in_array($salesOrder['status'], ['delivered', 'rejected'])): ?>
        <div class="alert alert-warning mb-4">
            <strong>Note:</strong> This sales order has been <?= $salesOrder['status'] ?>. Only notes can be updated for record-keeping purposes.
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= $isEdit ? '/salesorders/' . $salesOrder['id'] : '/salesorders' ?>" id="salesOrderForm">
        <?= Helpers::csrfField() ?>
        
        <div class="row">
            <!-- Left Column - Main Form -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Sales Order Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="client_id" class="form-label">
                                        <?= I18n::t('navigation.client') ?> <span class="text-danger">*</span>
                                    </label>
                                    <?php if ($isEdit): ?>
                                        <input type="text" class="form-control" 
                                               value="<?= Helpers::escape($salesOrder['client_name']) ?> (<?= ucfirst($salesOrder['client_type']) ?>)" 
                                               readonly>
                                        <input type="hidden" name="client_id" value="<?= $salesOrder['client_id'] ?>">
                                        <small class="text-muted">Client cannot be changed for existing sales orders</small>
                                    <?php else: ?>
                                        <select class="form-control" id="client_id" name="client_id" required>
                                            <option value="">Select a client...</option>
                                            <?php foreach ($clients ?? [] as $client): ?>
                                                <option value="<?= $client['id'] ?>" 
                                                        <?= Helpers::old('client_id') == $client['id'] ? 'selected' : '' ?>>
                                                    <?= Helpers::escape($client['name']) ?> (<?= ucfirst($client['type']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?= Helpers::getError('client_id') ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="form-label">Status</label>
                                    <?php if ($isEdit && in_array($salesOrder['status'], ['delivered', 'rejected'])): ?>
                                        <input type="text" class="form-control" 
                                               value="<?= ucfirst($salesOrder['status']) ?>" readonly>
                                        <small class="text-muted">Status cannot be changed for <?= $salesOrder['status'] ?> orders</small>
                                    <?php else: ?>
                                        <select class="form-control" id="status" name="status">
                                            <option value="open" <?= ($isEdit && $salesOrder['status'] == 'open') || Helpers::old('status') == 'open' ? 'selected' : '' ?>>Open</option>
                                            <option value="delivered" <?= ($isEdit && $salesOrder['status'] == 'delivered') || Helpers::old('status') == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="rejected" <?= ($isEdit && $salesOrder['status'] == 'rejected') || Helpers::old('status') == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                        </select>
                                        <?php if (!$isEdit): ?>
                                            <small class="text-muted">New sales orders start as "Open"</small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($isEdit && !empty($salesOrder['quote_id'])): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Source Quote</label>
                                        <div class="p-3" style="background-color: #f8f9fa; border-radius: 0.375rem;">
                                            <p class="mb-0">
                                                This sales order was converted from 
                                                <a href="/quotes/<?= $salesOrder['quote_id'] ?>" style="text-decoration: none; color: #667eea;">
                                                    Quote #<?= str_pad($salesOrder['quote_id'], 4, '0', STR_PAD_LEFT) ?>
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="notes" class="form-label"><?= I18n::t('common.notes') ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="4" 
                                      placeholder="Order notes, special instructions, delivery requirements..."><?= $isEdit ? Helpers::escape($salesOrder['notes'] ?? '') : Helpers::old('notes') ?></textarea>
                        </div>
                    </div>
                </div>

                <?php if (!$isEdit): ?>
                    <!-- Sales Order Items (for new orders only) -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Sales Order Items</h4>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addSalesOrderItem()">
                                + Add Item
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="salesorder-items">
                                <div class="text-center text-muted py-4" id="no-items-message">
                                    No items added yet. Click "Add Item" to begin.
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Display items for edit mode (read-only) -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Sales Order Items</h4>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($items)): ?>
                                <div class="alert alert-info">
                                    <strong>Note:</strong> Items cannot be modified for existing sales orders. To change items, create a new quote and convert it to a sales order.
                                </div>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Price</th>
                                                <th>Tax</th>
                                                <th>Discount</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $item): ?>
                                                <?php
                                                $itemSubtotal = $item['qty'] * $item['price'];
                                                $itemTax = $item['tax_type'] === 'percent' ? ($itemSubtotal * $item['tax'] / 100) : $item['tax'];
                                                $itemDiscount = $item['discount_type'] === 'percent' ? ($itemSubtotal * $item['discount'] / 100) : $item['discount'];
                                                $itemTotal = $itemSubtotal + $itemTax - $itemDiscount;
                                                ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= Helpers::escape($item['product_name']) ?></strong>
                                                        <br><small class="text-muted"><?= Helpers::escape($item['product_code']) ?></small>
                                                    </td>
                                                    <td><?= number_format($item['qty'], 2) ?></td>
                                                    <td><?= Helpers::formatCurrency($item['price']) ?></td>
                                                    <td>
                                                        <?php if ($item['tax'] > 0): ?>
                                                            <?= $item['tax'] ?><?= $item['tax_type'] === 'percent' ? '%' : '' ?>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($item['discount'] > 0): ?>
                                                            <?= $item['discount'] ?><?= $item['discount_type'] === 'percent' ? '%' : '' ?>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><strong><?= Helpers::formatCurrency($itemTotal) ?></strong></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p>No items found for this sales order.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column - Summary & Actions -->
            <div class="col-md-4">
                <?php if ($isEdit): ?>
                    <!-- Order Summary (Edit mode) -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Order Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong><?= Helpers::formatCurrency($salesOrder['items_subtotal']) ?></strong>
                            </div>
                            
                            <?php if ($salesOrder['items_tax_total'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Items Tax:</span>
                                    <span><?= Helpers::formatCurrency($salesOrder['items_tax_total']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($salesOrder['items_discount_total'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Items Discount:</span>
                                    <span style="color: #28a745;">-<?= Helpers::formatCurrency($salesOrder['items_discount_total']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($salesOrder['tax_total'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Global Tax:</span>
                                    <span><?= Helpers::formatCurrency($salesOrder['tax_total']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($salesOrder['discount_total'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Global Discount:</span>
                                    <span style="color: #28a745;">-<?= Helpers::formatCurrency($salesOrder['discount_total']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-3" style="font-size: 1.2rem; font-weight: bold;">
                                <span>Grand Total:</span>
                                <span><?= Helpers::formatCurrency($salesOrder['grand_total']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Summary for new orders -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Order Summary</h4>
                        </div>
                        <div class="card-body">
                            <div id="order-summary" style="display: none;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Items Subtotal:</span>
                                    <span id="items-subtotal">$0.00</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3" style="font-size: 1.2rem; font-weight: bold;">
                                    <span>Grand Total:</span>
                                    <span id="grand-total">$0.00</span>
                                </div>
                            </div>
                            
                            <div id="no-items-summary" style="text-align: center;">
                                <p class="text-muted">Add items to see order summary</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Actions Card -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <?= $isEdit ? I18n::t('actions.update') : I18n::t('actions.create') ?> Sales Order
                        </button>
                        <a href="<?= $isEdit ? '/salesorders/' . $salesOrder['id'] : '/salesorders' ?>" class="btn btn-secondary btn-block">
                            <?= I18n::t('actions.cancel') ?>
                        </a>
                        
                        <?php if (!$isEdit): ?>
                            <hr>
                            <small class="text-muted">
                                <strong>Alternative:</strong> Consider creating a <a href="/quotes/create">Quote</a> first for better workflow management.
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.form-label {
    font-weight: 600;
    color: #374151;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #e3e6f0;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.salesorder-item-row {
    margin-bottom: 1rem;
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.salesorder-item-row:last-child hr {
    display: none;
}

#no-items-message {
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* RTL Support */
[dir="rtl"] .d-flex.justify-content-between {
    flex-direction: row-reverse;
}
</style>

<script>
let itemIndex = 0;
let products = <?= json_encode($products ?? []) ?>;

// Add new sales order item row
function addSalesOrderItem() {
    const noItemsMessage = document.getElementById('no-items-message');
    if (noItemsMessage) {
        noItemsMessage.style.display = 'none';
    }
    
    const container = document.getElementById('salesorder-items');
    const itemHtml = `
        <div class="salesorder-item-row" data-index="${itemIndex}">
            <div class="row align-items-end">
                <div class="col-md-4">
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
                <div class="col-md-2">
                    <label class="form-label">Qty</label>
                    <input type="number" class="form-control qty-input" name="qty[]" 
                           step="0.01" min="0.01" value="1" onchange="calculateItemTotal(this)" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Price</label>
                    <input type="number" class="form-control price-input" name="price[]" 
                           step="0.01" min="0" value="0" onchange="calculateItemTotal(this)" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total</label>
                    <div class="form-control item-total font-weight-bold" style="background: #e9ecef;">$0.00</div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeSalesOrderItem(this)">Remove</button>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
    itemIndex++;
    calculateTotals();
}

// Remove sales order item row
function removeSalesOrderItem(button) {
    const row = button.closest('.salesorder-item-row');
    row.remove();
    
    // Show no items message if no items left
    const remainingItems = document.querySelectorAll('.salesorder-item-row');
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
    const row = selectElement.closest('.salesorder-item-row');
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
    const row = element.closest('.salesorder-item-row');
    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    
    // Calculate total
    const total = qty * price;
    
    // Update display
    const totalElement = row.querySelector('.item-total');
    totalElement.textContent = formatCurrency(total);
    
    // Recalculate overall totals
    calculateTotals();
}

// Calculate overall totals
function calculateTotals() {
    let itemsSubtotal = 0;
    
    // Sum up all item totals
    document.querySelectorAll('.salesorder-item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        
        const subtotal = qty * price;
        itemsSubtotal += subtotal;
    });
    
    // Update display
    const itemsSubtotalElement = document.getElementById('items-subtotal');
    const grandTotalElement = document.getElementById('grand-total');
    const orderSummary = document.getElementById('order-summary');
    const noItemsSummary = document.getElementById('no-items-summary');
    
    if (itemsSubtotal > 0) {
        if (itemsSubtotalElement) itemsSubtotalElement.textContent = formatCurrency(itemsSubtotal);
        if (grandTotalElement) grandTotalElement.textContent = formatCurrency(itemsSubtotal);
        
        if (orderSummary) orderSummary.style.display = 'block';
        if (noItemsSummary) noItemsSummary.style.display = 'none';
    } else {
        if (orderSummary) orderSummary.style.display = 'none';
        if (noItemsSummary) noItemsSummary.style.display = 'block';
    }
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
document.getElementById('salesOrderForm').addEventListener('submit', function(e) {
    <?php if (!$isEdit): ?>
    const items = document.querySelectorAll('.salesorder-item-row');
    
    if (items.length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the sales order.');
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
    <?php endif; ?>
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!$isEdit): ?>
    // Add first item if creating new sales order
    if (document.querySelectorAll('.salesorder-item-row').length === 0) {
        addSalesOrderItem();
    }
    <?php endif; ?>
    
    console.log('Sales Order Form Loaded');
    console.log('Edit Mode:', <?= $isEdit ? 'true' : 'false' ?>);
    console.log('Products Available:', products.length);
    
    <?php if ($isEdit): ?>
    console.log('Sales Order ID: <?= $salesOrder['id'] ?>');
    console.log('Status: <?= $salesOrder['status'] ?>');
    console.log('Grand Total: <?= $salesOrder['grand_total'] ?>');
    <?php endif; ?>
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
