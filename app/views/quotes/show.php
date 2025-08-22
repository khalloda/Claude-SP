<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = 'Quote #' . str_pad($quote['id'], 4, '0', STR_PAD_LEFT) . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 class="card-title">Quote #<?= str_pad($quote['id'], 4, '0', STR_PAD_LEFT) ?></h1>
                <div style="margin-top: 0.5rem;">
                    <span class="badge badge-<?= $quote['status'] ?>" style="font-size: 0.9rem;">
                        <?= ucfirst($quote['status']) ?>
                    </span>
                    <span style="margin-left: 1rem; color: #666;">
                        Created: <?= Helpers::formatDate($quote['created_at']) ?>
                    </span>
                </div>
            </div>
            <div>
                <?php if ($quote['status'] === 'sent'): ?>
                    <a href="/quotes/<?= $quote['id'] ?>/edit" class="btn btn-primary">Edit Quote</a>
                    
                    <form method="POST" action="/quotes/<?= $quote['id'] ?>/approve" style="display: inline; margin-left: 0.5rem;" 
                          onsubmit="return confirm('Approve this quote?')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-success">Approve</button>
                    </form>
                    
                    <form method="POST" action="/quotes/<?= $quote['id'] ?>/reject" style="display: inline; margin-left: 0.5rem;" 
                          onsubmit="return confirm('Reject this quote?')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </form>
                <?php endif; ?>
                
                <?php if ($quote['status'] === 'approved'): ?>
                    <form method="POST" action="/quotes/<?= $quote['id'] ?>/convert-to-order" style="display: inline;" 
                          onsubmit="return confirm('Convert this quote to a sales order?')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-success">Convert to Sales Order</button>
                    </form>
                <?php endif; ?>
                
                <a href="/quotes" class="btn btn-secondary" style="margin-left: 0.5rem;">Back to Quotes</a>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Client Information -->
        <div class="row" style="margin-bottom: 2rem;">
            <div class="col-md-6">
                <h3>Client Information</h3>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                    <strong><?= Helpers::escape($quote['client_name']) ?></strong><br>
                    <span style="color: #666;"><?= ucfirst($quote['client_type']) ?></span><br>
                    <?php if ($quote['client_email']): ?>
                        Email: <?= Helpers::escape($quote['client_email']) ?><br>
                    <?php endif; ?>
                    <?php if ($quote['client_phone']): ?>
                        Phone: <?= Helpers::escape($quote['client_phone']) ?><br>
                    <?php endif; ?>
                    <?php if ($quote['client_address']): ?>
                        <div style="margin-top: 0.5rem;">
                            <strong>Address:</strong><br>
                            <?= Helpers::escape($quote['client_address']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <h3>Quote Summary</h3>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                    <table style="width: 100%;">
                        <tr>
                            <td>Items Subtotal:</td>
                            <td style="text-align: right;"><?= Helpers::formatCurrency($quote['items_subtotal']) ?></td>
                        </tr>
                        <tr>
                            <td>Items Tax:</td>
                            <td style="text-align: right;"><?= Helpers::formatCurrency($quote['items_tax_total']) ?></td>
                        </tr>
                        <tr>
                            <td>Items Discount:</td>
                            <td style="text-align: right;">-<?= Helpers::formatCurrency($quote['items_discount_total']) ?></td>
                        </tr>
                        <tr>
                            <td>Global Tax:</td>
                            <td style="text-align: right;"><?= Helpers::formatCurrency($quote['tax_total'] - $quote['items_tax_total']) ?></td>
                        </tr>
                        <tr>
                            <td>Global Discount:</td>
                            <td style="text-align: right;">-<?= Helpers::formatCurrency($quote['discount_total'] - $quote['items_discount_total']) ?></td>
                        </tr>
                        <tr style="font-weight: bold; font-size: 1.1rem; border-top: 2px solid #667eea;">
                            <td>Grand Total:</td>
                            <td style="text-align: right;"><?= Helpers::formatCurrency($quote['grand_total']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quote Items -->
        <div style="margin-bottom: 2rem;">
            <h3>Quote Items</h3>
            <?php if (!empty($items)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Tax</th>
                                <th>Discount</th>
                                <th>Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <?php
                                $lineSubtotal = $item['qty'] * $item['price'];
                                $lineTax = $item['tax_type'] === 'percent' 
                                    ? ($lineSubtotal * $item['tax'] / 100)
                                    : $item['tax'];
                                $lineDiscount = $item['discount_type'] === 'percent'
                                    ? ($lineSubtotal * $item['discount'] / 100)
                                    : $item['discount'];
                                $lineTotal = $lineSubtotal + $lineTax - $lineDiscount;
                                ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?= Helpers::escape($item['product_code']) ?></strong><br>
                                            <?= Helpers::escape($item['product_name']) ?><br>
                                            <small style="color: #666;"><?= Helpers::escape($item['classification']) ?></small>
                                        </div>
                                    </td>
                                    <td><?= number_format($item['qty'], 2) ?></td>
                                    <td><?= Helpers::formatCurrency($item['price']) ?></td>
                                    <td>
                                        <?php if ($item['tax'] > 0): ?>
                                            <?= $item['tax'] ?><?= $item['tax_type'] === 'percent' ? '%' : '' ?>
                                            <br><small style="color: #666;"><?= Helpers::formatCurrency($lineTax) ?></small>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['discount'] > 0): ?>
                                            <?= $item['discount'] ?><?= $item['discount_type'] === 'percent' ? '%' : '' ?>
                                            <br><small style="color: #666;">-<?= Helpers::formatCurrency($lineDiscount) ?></small>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= Helpers::formatCurrency($lineTotal) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background-color: #f8f9fa;">
                                <td colspan="5">Items Subtotal:</td>
                                <td><?= Helpers::formatCurrency($quote['items_subtotal']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: #666;">No items in this quote.</p>
            <?php endif; ?>
        </div>

        <!-- Global Tax & Discount Details -->
        <?php if ($quote['global_tax_value'] > 0 || $quote['global_discount_value'] > 0): ?>
            <div style="margin-bottom: 2rem;">
                <h3>Global Adjustments</h3>
                <div class="row">
                    <?php if ($quote['global_tax_value'] > 0): ?>
                        <div class="col-md-6">
                            <div style="background: #e8f5e8; padding: 1rem; border-radius: 5px;">
                                <strong>Global Tax</strong><br>
                                <?= $quote['global_tax_value'] ?><?= $quote['global_tax_type'] === 'percent' ? '%' : '' ?>
                                <span style="float: right;">
                                    <?= Helpers::formatCurrency($quote['tax_total'] - $quote['items_tax_total']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($quote['global_discount_value'] > 0): ?>
                        <div class="col-md-6">
                            <div style="background: #fff3cd; padding: 1rem; border-radius: 5px;">
                                <strong>Global Discount</strong><br>
                                <?= $quote['global_discount_value'] ?><?= $quote['global_discount_type'] === 'percent' ? '%' : '' ?>
                                <span style="float: right;">
                                    -<?= Helpers::formatCurrency($quote['discount_total'] - $quote['items_discount_total']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Notes -->
        <?php if ($quote['notes']): ?>
            <div style="margin-bottom: 2rem;">
                <h3>Notes</h3>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                    <?= nl2br(Helpers::escape($quote['notes'])) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quote Status History -->
        <div style="margin-bottom: 2rem;">
            <h3>Quote Status</h3>
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span class="badge badge-<?= $quote['status'] ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                        <?= ucfirst($quote['status']) ?>
                    </span>
                    
                    <?php if ($quote['status'] === 'sent'): ?>
                        <span style="color: #666;">Waiting for approval</span>
                    <?php elseif ($quote['status'] === 'approved'): ?>
                        <span style="color: #28a745;">✅ Approved - Ready to convert to Sales Order</span>
                    <?php elseif ($quote['status'] === 'rejected'): ?>
                        <span style="color: #dc3545;">❌ Rejected</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($quote['status'] === 'approved'): ?>
                    <div style="margin-top: 1rem; padding: 1rem; background: #d4edda; border-radius: 5px;">
                        <strong>Next Steps:</strong><br>
                        This quote has been approved and can now be converted to a Sales Order. 
                        Click the "Convert to Sales Order" button above to proceed with the order process.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons at Bottom -->
        <div style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 10px;">
            <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                <?php if ($quote['status'] === 'sent'): ?>
                    <a href="/quotes/<?= $quote['id'] ?>/edit" class="btn btn-primary">
                        📝 Edit Quote
                    </a>
                    
                    <form method="POST" action="/quotes/<?= $quote['id'] ?>/approve" style="display: inline;" 
                          onsubmit="return confirm('Approve this quote? This action cannot be undone.')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-success">
                            ✅ Approve Quote
                        </button>
                    </form>
                    
                    <form method="POST" action="/quotes/<?= $quote['id'] ?>/reject" style="display: inline;" 
                          onsubmit="return confirm('Reject this quote? This will release reserved stock.')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-danger">
                            ❌ Reject Quote
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if ($quote['status'] === 'approved'): ?>
                    <form method="POST" action="/quotes/<?= $quote['id'] ?>/convert-to-order" style="display: inline;" 
                          onsubmit="return confirm('Convert this quote to a sales order? This will create a new sales order and transfer stock reservations.')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-success" style="font-size: 1.1rem; padding: 0.75rem 1.5rem;">
                            🚀 Convert to Sales Order
                        </button>
                    </form>
                <?php endif; ?>
                
                <a href="/quotes" class="btn btn-secondary">
                    📋 Back to Quotes
                </a>
                
                <button onclick="window.print()" class="btn btn-secondary">
                    🖨️ Print Quote
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-size: 0.8rem;
    font-weight: 500;
    color: white;
}

.badge-sent { background-color: #6c757d; }
.badge-approved { background-color: #28a745; }
.badge-rejected { background-color: #dc3545; }

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
    color: #495057;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: -0.5rem;
}

.col-md-6 {
    flex: 0 0 50%;
    padding: 0.5rem;
}

@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .card-header > div {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn {
        margin: 0.25rem;
    }
}

/* Print styles */
@media print {
    .btn, .card-header form {
        display: none;
    }
    
    .card {
        box-shadow: none;
        border: 1px solid #000;
    }
    
    .card-header {
        background: white !important;
        color: black !important;
    }
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
