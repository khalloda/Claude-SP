<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.invoices') . ' - Invoice #' . str_pad($invoice['id'], 4, '0', STR_PAD_LEFT);
$showNav = true;

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1>Invoice #<?= str_pad($invoice['id'], 4, '0', STR_PAD_LEFT) ?></h1>
            <?php if (!empty($invoice['sales_order_id'])): ?>
                <p class="text-muted">
                    Generated from <a href="/salesorders/<?= $invoice['sales_order_id'] ?>">Sales Order #<?= str_pad($invoice['sales_order_id'], 4, '0', STR_PAD_LEFT) ?></a>
                </p>
            <?php else: ?>
                <p class="text-muted">Direct invoice</p>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <span class="badge badge-<?= $invoice['status'] ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                <?= ucfirst($invoice['status']) ?>
            </span>
            
            <div style="display: flex; gap: 0.5rem;">
                <a href="/invoices" class="btn btn-secondary"><?= I18n::t('actions.back') ?></a>
                
                <?php if (!in_array($invoice['status'], ['paid', 'partial'])): ?>
                    <a href="/invoices/<?= $invoice['id'] ?>/edit" class="btn btn-primary"><?= I18n::t('actions.edit') ?></a>
                <?php endif; ?>
                
                <?php if (in_array($invoice['status'], ['open', 'partial'])): ?>
                    <button type="button" class="btn btn-success" onclick="showPaymentModal()">
                        Add Payment
                    </button>
                <?php endif; ?>
                
                <?php if ($invoice['status'] === 'open'): ?>
                    <form method="POST" action="/invoices/<?= $invoice['id'] ?>/void" style="display: inline;" 
                          onsubmit="return confirm('Are you sure you want to void this invoice? This action cannot be undone.')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-danger">Void Invoice</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Invoice Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Invoice Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Client Information</h5>
                            <p>
                                <strong><?= Helpers::escape($invoice['client_name'] ?? 'Unknown') ?></strong><br>
                                <small class="text-muted"><?= ucfirst($invoice['client_type'] ?? 'unknown') ?></small>
                            </p>
                            
                            <?php if (!empty($invoice['client_email'])): ?>
                                <p><strong>Email:</strong> <?= Helpers::escape($invoice['client_email']) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($invoice['client_phone'])): ?>
                                <p><strong>Phone:</strong> <?= Helpers::escape($invoice['client_phone']) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($invoice['client_address'])): ?>
                                <p><strong>Address:</strong> <?= Helpers::escape($invoice['client_address']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Invoice Information</h5>
                            <p><strong>Invoice #:</strong> <?= str_pad($invoice['id'], 4, '0', STR_PAD_LEFT) ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge badge-<?= $invoice['status'] ?>">
                                    <?= ucfirst($invoice['status']) ?>
                                </span>
                            </p>
                            <p><strong>Created:</strong> <?= Helpers::formatDate($invoice['created_at']) ?></p>
                            
                            <?php if (!empty($invoice['sales_order_id'])): ?>
                                <p><strong>Sales Order:</strong> 
                                    <a href="/salesorders/<?= $invoice['sales_order_id'] ?>">
                                        SO #<?= $invoice['sales_order_id'] ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($invoice['notes'])): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Notes</h6>
                                <p><?= Helpers::escape($invoice['notes']) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Invoice Items</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($items)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Code</th>
                                        <th>Qty</th>
                                        <th>Unit Price</th>
                                        <th>Tax</th>
                                        <th>Discount</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach ($items as $item): 
                                        $lineTotal = ($item['qty'] * $item['price']) + $item['tax'] - $item['discount'];
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?= Helpers::escape($item['product_name'] ?? 'Unknown Product') ?></strong>
                                                <br><small class="text-muted"><?= Helpers::escape($item['classification'] ?? '') ?></small>
                                            </td>
                                            <td><?= Helpers::escape($item['product_code'] ?? 'N/A') ?></td>
                                            <td><?= number_format($item['qty'], 2) ?></td>
                                            <td><?= Helpers::formatCurrency($item['price']) ?></td>
                                            <td>
                                                <?php 
                                                $lineSubtotal = $item['qty'] * $item['price'];
                                                if ($item['tax_type'] === 'percent') {
                                                    $lineTax = $lineSubtotal * $item['tax'] / 100;
                                                    echo Helpers::formatCurrency($lineTax);
                                                    echo ' <small class="text-muted">(' . $item['tax'] . '%)</small>';
                                                } else {
                                                    echo Helpers::formatCurrency($item['tax']);
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($item['discount_type'] === 'percent') {
                                                    $lineDiscount = $lineSubtotal * $item['discount'] / 100;
                                                    echo Helpers::formatCurrency($lineDiscount);
                                                    echo ' <small class="text-muted">(' . $item['discount'] . '%)</small>';
                                                } else {
                                                    echo Helpers::formatCurrency($item['discount']);
                                                }
                                                ?>
                                            </td>
                                            <td><strong><?= Helpers::formatCurrency($lineTotal) ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No items found for this invoice.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment History -->
            <?php if (!empty($payments)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Payment History</h3>
                    </div>
                    <div class="card-body">
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
                                            <td><strong style="color: #28a745;"><?= Helpers::formatCurrency($payment['amount']) ?></strong></td>
                                            <td>
                                                <span class="badge badge-method badge-<?= str_replace('_', '-', $payment['method']) ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $payment['method'])) ?>
                                                </span>
                                            </td>
                                            <td><?= Helpers::escape($payment['note'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Invoice Summary -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h4>Invoice Summary</h4>
                </div>
                <div class="card-body">
                    <div class="summary-row">
                        <span>Items Subtotal:</span>
                        <span><?= Helpers::formatCurrency($invoice['items_subtotal']) ?></span>
                    </div>
                    
                    <?php if ($invoice['items_discount_total'] > 0): ?>
                        <div class="summary-row">
                            <span>Items Discount:</span>
                            <span class="text-success">-<?= Helpers::formatCurrency($invoice['items_discount_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($invoice['global_discount_value'] > 0): ?>
                        <div class="summary-row">
                            <span>Global Discount:</span>
                            <span class="text-success">
                                -<?= Helpers::formatCurrency($invoice['discount_total'] - $invoice['items_discount_total']) ?>
                                <?php if ($invoice['global_discount_type'] === 'percent'): ?>
                                    <small>(<?= $invoice['global_discount_value'] ?>%)</small>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($invoice['items_tax_total'] > 0): ?>
                        <div class="summary-row">
                            <span>Items Tax:</span>
                            <span><?= Helpers::formatCurrency($invoice['items_tax_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($invoice['global_tax_value'] > 0): ?>
                        <div class="summary-row">
                            <span>Global Tax:</span>
                            <span>
                                <?= Helpers::formatCurrency($invoice['tax_total'] - $invoice['items_tax_total']) ?>
                                <?php if ($invoice['global_tax_type'] === 'percent'): ?>
                                    <small>(<?= $invoice['global_tax_value'] ?>%)</small>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    <div class="summary-row">
                        <span><strong>Grand Total:</strong></span>
                        <span><strong><?= Helpers::formatCurrency($invoice['grand_total']) ?></strong></span>
                    </div>
                    <div class="summary-row">
                        <span><strong>Paid Amount:</strong></span>
                        <span><strong style="color: #28a745;"><?= Helpers::formatCurrency($invoice['paid_total']) ?></strong></span>
                    </div>
                    <div class="summary-row final-balance">
                        <span><strong>Balance Due:</strong></span>
                        <span><strong style="color: <?= ($balance['balance'] ?? 0) > 0 ? '#dc3545' : '#28a745' ?>;">
                            <?= Helpers::formatCurrency($balance['balance'] ?? ($invoice['grand_total'] - $invoice['paid_total'])) ?>
                        </strong></span>
                    </div>
                </div>
            </div>
            
            <?php if ($invoice['status'] === 'paid'): ?>
                <div class="card" style="border-left: 4px solid #28a745;">
                    <div class="card-body">
                        <h5 style="color: #28a745;">✅ Invoice Paid</h5>
                        <p class="text-muted mb-0">This invoice has been fully paid.</p>
                    </div>
                </div>
            <?php elseif ($invoice['status'] === 'partial'): ?>
                <div class="card" style="border-left: 4px solid #ffc107;">
                    <div class="card-body">
                        <h5 style="color: #ffc107;">⏳ Partially Paid</h5>
                        <p class="text-muted mb-0">This invoice has partial payment. Balance due: <strong><?= Helpers::formatCurrency($balance['balance'] ?? 0) ?></strong></p>
                    </div>
                </div>
            <?php elseif ($invoice['status'] === 'void'): ?>
                <div class="card" style="border-left: 4px solid #6c757d;">
                    <div class="card-body">
                        <h5 style="color: #6c757d;">❌ Invoice Voided</h5>
                        <p class="text-muted mb-0">This invoice has been voided and is no longer valid.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card" style="border-left: 4px solid #007bff;">
                    <div class="card-body">
                        <h5 style="color: #007bff;">📄 Invoice Open</h5>
                        <p class="text-muted mb-0">This invoice is awaiting payment.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closePaymentModal()">&times;</span>
        <h3>Add Payment</h3>
        <form id="paymentForm" method="POST" action="/invoices/<?= $invoice['id'] ?>/add-payment">
            <?= Helpers::csrfField() ?>
            <div class="form-group">
                <label for="amount" class="form-label">Amount *</label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount" 
                       max="<?= $balance['balance'] ?? 0 ?>" 
                       placeholder="Max: <?= Helpers::formatCurrency($balance['balance'] ?? 0) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="method" class="form-label">Payment Method *</label>
                <select class="form-control" id="method" name="method" required>
                    <option value="">Select Method</option>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="check">Check</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="note" class="form-label">Note</label>
                <textarea class="form-control" id="note" name="note" rows="3" 
                          placeholder="Optional payment note"></textarea>
            </div>
            
            <div style="text-align: right; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Payment</button>
            </div>
        </form>
    </div>
</div>

<style>
.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-size: 0.8rem;
    color: white;
}

.badge-open { background-color: #007bff; }
.badge-partial { background-color: #ffc107; color: #000; }
.badge-paid { background-color: #28a745; }
.badge-void { background-color: #6c757d; }

.badge-method {
    font-size: 0.7rem;
}

.badge-cash { background-color: #28a745; }
.badge-bank-transfer { background-color: #007bff; }
.badge-check { background-color: #ffc107; color: #000; }
.badge-credit-card { background-color: #17a2b8; }
.badge-other { background-color: #6c757d; }

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: none;
    margin-bottom: 1rem;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.25rem;
}

.card-header h3, .card-header h4 {
    margin: 0;
    color: #495057;
}

.card-body {
    padding: 1.25rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.final-balance {
    font-size: 1.1rem;
    padding-top: 0.5rem;
    border-top: 1px solid #dee2e6;
    margin-top: 0.5rem;
}

.text-success {
    color: #28a745 !important;
}

.text-muted {
    color: #6c757d !important;
}

/* Modal styles */
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
    background-color: white;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    position: relative;
}

.close {
    position: absolute;
    right: 1rem;
    top: 1rem;
    font-size: 1.5rem;
    cursor: pointer;
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.d-flex {
    display: flex;
}

.justify-content-between {
    justify-content: space-between;
}

.align-items-start {
    align-items: flex-start;
}

.mb-4 {
    margin-bottom: 2rem;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: -0.5rem;
}

.col-md-8 {
    flex: 0 0 66.666%;
    padding: 0.5rem;
}

.col-md-6 {
    flex: 0 0 50%;
    padding: 0.5rem;
}

.col-md-4 {
    flex: 0 0 33.333%;
    padding: 0.5rem;
}

.col-12 {
    flex: 0 0 100%;
    padding: 0.5rem;
}

@media (max-width: 768px) {
    .col-md-8, .col-md-6, .col-md-4 {
        flex: 0 0 100%;
    }
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.table th,
.table td {
    padding: 0.75rem;
    border-bottom: 1px solid #dee2e6;
    text-align: left;
    vertical-align: top;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.table-responsive {
    overflow-x: auto;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    cursor: pointer;
    font-size: 0.9rem;
    margin-right: 0.5rem;
}

.btn-primary { background-color: #007bff; color: white; }
.btn-secondary { background-color: #6c757d; color: white; }
.btn-success { background-color: #28a745; color: white; }
.btn-danger { background-color: #dc3545; color: white; }
</style>

<script>
function showPaymentModal() {
    const modal = document.getElementById('paymentModal');
    modal.style.display = 'block';
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    modal.style.display = 'none';
    // Reset form
    document.getElementById('paymentForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('paymentModal');
    if (event.target === modal) {
        closePaymentModal();
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
