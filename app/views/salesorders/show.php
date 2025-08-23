<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.sales_orders') . ' - Sales Order #' . str_pad($salesOrder['id'], 4, '0', STR_PAD_LEFT);
$showNav = true;

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1>Sales Order #<?= str_pad($salesOrder['id'], 4, '0', STR_PAD_LEFT) ?></h1>
            <?php if (!empty($salesOrder['quote_id'])): ?>
                <p class="text-muted">
                    Converted from <a href="/quotes/<?= $salesOrder['quote_id'] ?>">Quote #<?= str_pad($salesOrder['quote_id'], 4, '0', STR_PAD_LEFT) ?></a>
                </p>
            <?php else: ?>
                <p class="text-muted">Direct sales order</p>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <span class="badge badge-<?= $salesOrder['status'] ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                <?= ucfirst($salesOrder['status']) ?>
            </span>
            
            <div style="display: flex; gap: 0.5rem;">
                <a href="/salesorders" class="btn btn-secondary"><?= I18n::t('actions.back') ?></a>
                
                <?php if ($salesOrder['status'] === 'open'): ?>
                    <form method="POST" action="/salesorders/<?= $salesOrder['id'] ?>/deliver" style="display: inline;" 
                          onsubmit="return confirm('Mark this sales order as delivered? This will update stock levels.')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-success">Mark as Delivered</button>
                    </form>
                    
                    <form method="POST" action="/salesorders/<?= $salesOrder['id'] ?>/convert-to-invoice" style="display: inline;" 
                          onsubmit="return confirm('Convert this sales order to an invoice?')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-primary">Convert to Invoice</button>
                    </form>
                    
                    <form method="POST" action="/salesorders/<?= $salesOrder['id'] ?>/reject" style="display: inline;" 
                          onsubmit="return confirm('Reject this sales order? This will release reserved stock.')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-danger">Reject Order</button>
                    </form>
                    
                <?php elseif ($salesOrder['status'] === 'delivered'): ?>
                    <form method="POST" action="/salesorders/<?= $salesOrder['id'] ?>/convert-to-invoice" style="display: inline;" 
                          onsubmit="return confirm('Convert this delivered order to an invoice?')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-primary">Convert to Invoice</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Order Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Client Information</h5>
                            <p>
                                <strong><?= Helpers::escape($salesOrder['client_name'] ?? 'Unknown') ?></strong><br>
                                <small class="text-muted"><?= ucfirst($salesOrder['client_type'] ?? 'unknown') ?></small>
                            </p>
                            
                            <?php if (!empty($salesOrder['client_email'])): ?>
                                <p><strong>Email:</strong> <?= Helpers::escape($salesOrder['client_email']) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($salesOrder['client_phone'])): ?>
                                <p><strong>Phone:</strong> <?= Helpers::escape($salesOrder['client_phone']) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($salesOrder['client_address'])): ?>
                                <p><strong>Address:</strong> <?= Helpers::escape($salesOrder['client_address']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Order Information</h5>
                            <p><strong>Order #:</strong> <?= str_pad($salesOrder['id'], 4, '0', STR_PAD_LEFT) ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge badge-<?= $salesOrder['status'] ?>">
                                    <?= ucfirst($salesOrder['status']) ?>
                                </span>
                            </p>
                            <p><strong>Created:</strong> <?= Helpers::formatDate($salesOrder['created_at']) ?></p>
                            
                            <?php if (!empty($salesOrder['quote_id'])): ?>
                                <p><strong>Quote:</strong> 
                                    <a href="/quotes/<?= $salesOrder['quote_id'] ?>">
                                        Quote #<?= $salesOrder['quote_id'] ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($salesOrder['notes'])): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Notes</h6>
                                <p><?= Helpers::escape($salesOrder['notes']) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card">
                <div class="card-header">
                    <h3>Order Items</h3>
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
                                    $itemsSubtotal = 0;
                                    foreach ($items as $item): 
                                        $lineTotal = ($item['qty'] * $item['price']) + $item['tax'] - $item['discount'];
                                        $itemsSubtotal += $lineTotal;
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
                        <p class="text-muted">No items found for this sales order.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Order Summary</h4>
                </div>
                <div class="card-body">
                    <div class="summary-row">
                        <span>Items Subtotal:</span>
                        <span><?= Helpers::formatCurrency($salesOrder['items_subtotal']) ?></span>
                    </div>
                    
                    <?php if ($salesOrder['items_discount_total'] > 0): ?>
                        <div class="summary-row">
                            <span>Items Discount:</span>
                            <span class="text-success">-<?= Helpers::formatCurrency($salesOrder['items_discount_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($salesOrder['global_discount_value'] > 0): ?>
                        <div class="summary-row">
                            <span>Global Discount:</span>
                            <span class="text-success">
                                -<?= Helpers::formatCurrency($salesOrder['discount_total'] - $salesOrder['items_discount_total']) ?>
                                <?php if ($salesOrder['global_discount_type'] === 'percent'): ?>
                                    <small>(<?= $salesOrder['global_discount_value'] ?>%)</small>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($salesOrder['items_tax_total'] > 0): ?>
                        <div class="summary-row">
                            <span>Items Tax:</span>
                            <span><?= Helpers::formatCurrency($salesOrder['items_tax_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($salesOrder['global_tax_value'] > 0): ?>
                        <div class="summary-row">
                            <span>Global Tax:</span>
                            <span>
                                <?= Helpers::formatCurrency($salesOrder['tax_total'] - $salesOrder['items_tax_total']) ?>
                                <?php if ($salesOrder['global_tax_type'] === 'percent'): ?>
                                    <small>(<?= $salesOrder['global_tax_value'] ?>%)</small>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    <div class="summary-row final-total">
                        <span><strong>Grand Total:</strong></span>
                        <span><strong><?= Helpers::formatCurrency($salesOrder['grand_total']) ?></strong></span>
                    </div>
                </div>
            </div>
            
            <?php if ($salesOrder['status'] === 'delivered'): ?>
                <div class="card mt-3" style="border-left: 4px solid #28a745;">
                    <div class="card-body">
                        <h5 style="color: #28a745;">✅ Order Delivered</h5>
                        <p class="text-muted mb-0">This order has been successfully delivered to the client.</p>
                    </div>
                </div>
            <?php elseif ($salesOrder['status'] === 'rejected'): ?>
                <div class="card mt-3" style="border-left: 4px solid #dc3545;">
                    <div class="card-body">
                        <h5 style="color: #dc3545;">❌ Order Rejected</h5>
                        <p class="text-muted mb-0">This order has been rejected and stock has been released.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card mt-3" style="border-left: 4px solid #007bff;">
                    <div class="card-body">
                        <h5 style="color: #007bff;">📦 Order Open</h5>
                        <p class="text-muted mb-0">This order is ready for processing and delivery.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
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
.badge-delivered { background-color: #28a745; }
.badge-rejected { background-color: #dc3545; }

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

.final-total {
    font-size: 1.1rem;
    padding-top: 0.5rem;
}

.text-success {
    color: #28a745 !important;
}

.text-muted {
    color: #6c757d !important;
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
