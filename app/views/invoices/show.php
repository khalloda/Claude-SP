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
                                <strong><?= Helpers::escape($invoice['client_name'] ?? 'Unknown Client') ?></strong>
                                <?php if (!empty($invoice['client_type'])): ?>
                                    <br><small class="text-muted"><?= ucfirst($invoice['client_type']) ?></small>
                                <?php endif; ?>
                            </p>
                            
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
                            <h5>Invoice Information</h5>
                            <p><strong>Invoice #:</strong> <?= str_pad($invoice['id'], 4, '0', STR_PAD_LEFT) ?></p>
                            <p>
                                <strong>Status:</strong> 
                                <span class="badge badge-<?= $invoice['status'] ?>">
                                    <?= ucfirst($invoice['status']) ?>
                                </span>
                            </p>
                            <p><strong>Created:</strong> <?= Helpers::formatDate($invoice['created_at']) ?></p>
                            
                            <?php if (!empty($invoice['sales_order_id'])): ?>
                                <p><strong>Sales Order:</strong> 
                                    <a href="/salesorders/<?= $invoice['sales_order_id'] ?>">
                                        SO #<?= str_pad($invoice['sales_order_id'], 4, '0', STR_PAD_LEFT) ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($invoice['notes'])): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Notes</h6>
                                <p><?= nl2br(Helpers::escape($invoice['notes'])) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="card">
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
                                        // Calculate line subtotal
                                        $lineSubtotal = $item['qty'] * $item['price'];
                                        
                                        // Calculate tax amount
                                        $lineTax = $item['tax_type'] === 'percent' 
                                            ? ($lineSubtotal * $item['tax'] / 100)
                                            : $item['tax'];
                                        
                                        // Calculate discount amount
                                        $lineDiscount = $item['discount_type'] === 'percent'
                                            ? ($lineSubtotal * $item['discount'] / 100)
                                            : $item['discount'];
                                        
                                        // Calculate final line total
                                        $lineTotal = $lineSubtotal + $lineTax - $lineDiscount;
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
                                                <?php if ($item['tax'] > 0): ?>
                                                    <?= Helpers::formatCurrency($lineTax) ?>
                                                    <br><small class="text-muted">(<?= $item['tax'] ?><?= $item['tax_type'] === 'percent' ? '%' : '' ?>)</small>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($item['discount'] > 0): ?>
                                                    -<?= Helpers::formatCurrency($lineDiscount) ?>
                                                    <br><small class="text-muted">(<?= $item['discount'] ?><?= $item['discount_type'] === 'percent' ? '%' : '' ?>)</small>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?= Helpers::formatCurrency($lineTotal) ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <h5 class="text-muted">No items found</h5>
                            <p class="text-muted">This invoice does not have any items yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Invoice Summary -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>Invoice Summary</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Items Subtotal:</span>
                        <span><?= Helpers::formatCurrency($invoice['items_subtotal']) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Items Tax:</span>
                        <span><?= Helpers::formatCurrency($invoice['items_tax_total']) ?></span>
                    </div>
                    
                    <?php if ($invoice['tax_total'] > $invoice['items_tax_total']): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Global Tax:</span>
                            <span><?= Helpers::formatCurrency($invoice['tax_total'] - $invoice['items_tax_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($invoice['items_discount_total'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Items Discount:</span>
                            <span>-<?= Helpers::formatCurrency($invoice['items_discount_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($invoice['discount_total'] > $invoice['items_discount_total']): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Global Discount:</span>
                            <span>-<?= Helpers::formatCurrency($invoice['discount_total'] - $invoice['items_discount_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Grand Total:</strong>
                        <strong><?= Helpers::formatCurrency($invoice['grand_total']) ?></strong>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Paid Amount:</span>
                        <span class="text-success"><?= Helpers::formatCurrency($invoice['paid_total']) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <strong>Balance Due:</strong>
                        <strong class="<?= ($invoice['grand_total'] - $invoice['paid_total']) > 0 ? 'text-danger' : 'text-success' ?>">
                            <?= Helpers::formatCurrency($invoice['grand_total'] - $invoice['paid_total']) ?>
                        </strong>
                    </div>
                    
                    <?php if ($invoice['status'] === 'paid'): ?>
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle"></i> Invoice Paid
                            <br><small>This invoice has been fully paid.</small>
                        </div>
                    <?php elseif ($invoice['status'] === 'partial'): ?>
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-circle"></i> Partially Paid
                            <br><small>Balance due: <?= Helpers::formatCurrency($invoice['grand_total'] - $invoice['paid_total']) ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Section -->
    <?php if (!empty($payments)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Payment History</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Payment #</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td>#<?= $payment['id'] ?></td>
                                    <td><?= Helpers::formatCurrency($payment['amount']) ?></td>
                                    <td><?= ucfirst(str_replace('_', ' ', $payment['method'])) ?></td>
                                    <td><?= Helpers::formatDate($payment['created_at']) ?></td>
                                    <td><?= Helpers::escape($payment['note'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Payment Modal -->
<?php if (in_array($invoice['status'], ['open', 'partial'])): ?>
<div class="modal fade" id="paymentModal" tabindex="-1" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Payment</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="/payments/create">
                <?= Helpers::csrfField() ?>
                <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
                <input type="hidden" name="client_id" value="<?= $invoice['client_id'] ?>">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="amount">Payment Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" 
                               step="0.01" min="0.01" 
                               max="<?= $invoice['grand_total'] - $invoice['paid_total'] ?>" 
                               value="<?= $invoice['grand_total'] - $invoice['paid_total'] ?>" required>
                        <small class="form-text text-muted">
                            Outstanding balance: <?= Helpers::formatCurrency($invoice['grand_total'] - $invoice['paid_total']) ?>
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="method">Payment Method</label>
                        <select class="form-control" id="method" name="method" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="credit_card">Credit Card</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="note">Notes</label>
                        <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function showPaymentModal() {
    const modal = document.getElementById('paymentModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.classList.add('modal-open');
}

// Close modal when clicking outside or on close button
document.addEventListener('click', function(e) {
    const modal = document.getElementById('paymentModal');
    if (e.target === modal || e.target.classList.contains('close')) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
