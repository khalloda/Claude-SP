<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.invoices') . ' - Invoice #' . str_pad($invoice['id'], 4, '0', STR_PAD_LEFT);

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1>Invoice #<?= str_pad($invoice['id'], 4, '0', STR_PAD_LEFT) ?></h1>
            <?php if (!empty($invoice['sales_order_id'])): ?>
                <p class="text-muted">
                    Converted from <a href="/salesorders/<?= $invoice['sales_order_id'] ?>">Sales Order #<?= $invoice['sales_order_id'] ?></a>
                </p>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <span class="badge badge-<?= $invoice['status'] ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                <?= ucfirst($invoice['status']) ?>
            </span>
            
            <div style="display: flex; gap: 0.5rem;">
                <a href="/invoices" class="btn btn-secondary"><?= I18n::t('actions.back') ?></a>
                
                <?php if ($invoice['status'] !== 'void' && $balance > 0): ?>
                    <button onclick="showPaymentModal()" class="btn btn-success">Add Payment</button>
                <?php endif; ?>
                
                <?php if ($invoice['status'] !== 'void' && $invoice['paid_total'] == 0): ?>
                    <form method="POST" action="/invoices/<?= $invoice['id'] ?>/void" style="display: inline;" 
                          onsubmit="return confirm('Void this invoice? This action cannot be undone.')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-danger">Void Invoice</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Client & Invoice Info -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4><?= I18n::t('navigation.client') ?> Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><?= Helpers::escape($invoice['client_name']) ?></h5>
                            <p class="text-muted"><?= ucfirst($invoice['client_type']) ?></p>
                            <?php if (!empty($invoice['client_email'])): ?>
                                <p><strong>Email:</strong> <?= Helpers::escape($invoice['client_email']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($invoice['client_phone'])): ?>
                                <p><strong>Phone:</strong> <?= Helpers::escape($invoice['client_phone']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($invoice['client_address'])): ?>
                                <p><strong>Address:</strong><br><?= nl2br(Helpers::escape($invoice['client_address'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Invoice Date:</strong> <?= Helpers::formatDate($invoice['created_at']) ?></p>
                            <?php if (!empty($invoice['notes'])): ?>
                                <p><strong>Notes:</strong><br><?= nl2br(Helpers::escape($invoice['notes'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Payment Summary</h4>
                </div>
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Subtotal:</span>
                        <strong><?= Helpers::formatCurrency($invoice['items_subtotal']) ?></strong>
                    </div>
                    
                    <?php if ($invoice['items_tax_total'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Items Tax:</span>
                            <span><?= Helpers::formatCurrency($invoice['items_tax_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($invoice['items_discount_total'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Items Discount:</span>
                            <span style="color: #28a745;">-<?= Helpers::formatCurrency($invoice['items_discount_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($invoice['tax_total'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Global Tax (<?= $invoice['global_tax_value'] ?><?= $invoice['global_tax_type'] === 'percent' ? '%' : '' ?>):</span>
                            <span><?= Helpers::formatCurrency($invoice['tax_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($invoice['discount_total'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Global Discount (<?= $invoice['global_discount_value'] ?><?= $invoice['global_discount_type'] === 'percent' ? '%' : '' ?>):</span>
                            <span style="color: #28a745;">-<?= Helpers::formatCurrency($invoice['discount_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 1.1rem;">
                        <strong>Grand Total:</strong>
                        <strong><?= Helpers::formatCurrency($invoice['grand_total']) ?></strong>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Paid Total:</span>
                        <span style="color: #28a745;"><?= Helpers::formatCurrency($invoice['paid_total']) ?></span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; font-size: 1.1rem;">
                        <strong>Balance Due:</strong>
                        <strong style="color: <?= $balance > 0 ? '#dc3545' : '#28a745' ?>;">
                            <?= Helpers::formatCurrency($balance) ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Items -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Invoice Items</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($items)): ?>
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
                                        <div>
                                            <strong><?= Helpers::escape($item['product_name']) ?></strong>
                                            <br><small class="text-muted"><?= Helpers::escape($item['product_code']) ?></small>
                                        </div>
                                    </td>
                                    <td><?= number_format($item['qty'], 2) ?></td>
                                    <td><?= Helpers::formatCurrency($item['price']) ?></td>
                                    <td>
                                        <?php if ($item['tax'] > 0): ?>
                                            <?= $item['tax'] ?><?= $item['tax_type'] === 'percent' ? '%' : '' ?>
                                            <br><small class="text-muted"><?= Helpers::formatCurrency($itemTax) ?></small>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['discount'] > 0): ?>
                                            <?= $item['discount'] ?><?= $item['discount_type'] === 'percent' ? '%' : '' ?>
                                            <br><small class="text-muted" style="color: #28a745;">-<?= Helpers::formatCurrency($itemDiscount) ?></small>
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
                <p>No items found for this invoice.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment History -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Payment History</h4>
            <?php if ($invoice['status'] !== 'void' && $balance > 0): ?>
                <button onclick="showPaymentModal()" class="btn btn-sm btn-success">Add Payment</button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (!empty($payments)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?= Helpers::formatDate($payment['created_at']) ?></td>
                                    <td><?= Helpers::formatCurrency($payment['amount']) ?></td>
                                    <td><?= Helpers::escape($payment['method']) ?></td>
                                    <td><?= Helpers::escape($payment['note'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No payments recorded for this invoice.</p>
                <?php if ($invoice['status'] !== 'void'): ?>
                    <button onclick="showPaymentModal()" class="btn btn-success">Record First Payment</button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<?php if ($invoice['status'] !== 'void' && $balance > 0): ?>
<div id="paymentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Add Payment</h4>
            <span class="modal-close" onclick="hidePaymentModal()">&times;</span>
        </div>
        <form method="POST" action="/invoices/<?= $invoice['id'] ?>/add-payment">
            <?= Helpers::csrfField() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="amount" class="form-label">Payment Amount</label>
                    <input type="number" 
                           class="form-control" 
                           id="amount" 
                           name="amount" 
                           step="0.01" 
                           max="<?= $balance ?>"
                           value="<?= $balance ?>"
                           required>
                    <small class="text-muted">Maximum: <?= Helpers::formatCurrency($balance) ?></small>
                </div>
                
                <div class="form-group">
                    <label for="method" class="form-label">Payment Method</label>
                    <select class="form-control" id="method" name="method" required>
                        <option value="">Select method...</option>
                        <option value="cash">Cash</option>
                        <option value="check">Check</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="note" class="form-label">Note (Optional)</label>
                    <textarea class="form-control" id="note" name="note" rows="3" placeholder="Payment reference, check number, etc."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hidePaymentModal()">Cancel</button>
                <button type="submit" class="btn btn-success">Record Payment</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<style>
/* Modal Styles */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h4 {
    margin: 0;
    color: #333;
}

.modal-close {
    font-size: 2rem;
    font-weight: bold;
    cursor: pointer;
    color: #aaa;
    transition: color 0.3s;
}

.modal-close:hover {
    color: #000;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

/* Payment status badges */
.badge-open { background-color: #007bff; }
.badge-partial { background-color: #ffc107; color: #000; }
.badge-paid { background-color: #28a745; }
.badge-void { background-color: #6c757d; }

/* RTL Support */
[dir="rtl"] .modal-footer {
    justify-content: flex-start;
}

[dir="rtl"] .modal-header {
    direction: rtl;
}
</style>

<script>
function showPaymentModal() {
    document.getElementById('paymentModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function hidePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('paymentModal');
    if (event.target === modal) {
        hidePaymentModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hidePaymentModal();
    }
});

// Validate payment amount
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            const maxAmount = parseFloat(this.getAttribute('max'));
            const currentAmount = parseFloat(this.value);
            
            if (currentAmount > maxAmount) {
                this.value = maxAmount;
            }
            
            if (currentAmount <= 0) {
                this.setCustomValidity('Amount must be greater than zero');
            } else if (currentAmount > maxAmount) {
                this.setCustomValidity('Amount cannot exceed the balance due');
            } else {
                this.setCustomValidity('');
            }
        });
    }
});

console.log('Invoice Show Page Loaded');
console.log('Invoice ID: <?= $invoice['id'] ?>');
console.log('Balance: <?= $balance ?>');
console.log('Status: <?= $invoice['status'] ?>');
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
