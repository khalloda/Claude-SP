<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.salesorders') . ' - Sales Order #' . str_pad($salesOrder['id'], 4, '0', STR_PAD_LEFT);
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
                    <a href="/salesorders/<?= $salesOrder['id'] ?>/edit" class="btn btn-primary"><?= I18n::t('actions.edit') ?></a>
                    
                    <form method="POST" action="/salesorders/<?= $salesOrder['id'] ?>/deliver" style="display: inline;">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-success" 
                                onclick="return confirm('Mark this order as delivered? This will deduct stock from inventory.')">
                            Deliver
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if ($salesOrder['status'] === 'delivered'): ?>
                    <form method="POST" action="/invoices/create-from-so" style="display: inline;">
                        <?= Helpers::csrfField() ?>
                        <input type="hidden" name="sales_order_id" value="<?= $salesOrder['id'] ?>">
                        <button type="submit" class="btn btn-info">Convert to Invoice</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sales Order Information -->
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
                                <strong><?= Helpers::escape($salesOrder['client_name']) ?></strong>
                                <?php if (!empty($salesOrder['client_type'])): ?>
                                    <br><small class="text-muted"><?= ucfirst($salesOrder['client_type']) ?></small>
                                <?php endif; ?>
                            </p>
                            
                            <?php if (!empty($salesOrder['client_email'])): ?>
                                <p><strong>Email:</strong> <?= Helpers::escape($salesOrder['client_email']) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($salesOrder['client_phone'])): ?>
                                <p><strong>Phone:</strong> <?= Helpers::escape($salesOrder['client_phone']) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($salesOrder['client_address'])): ?>
                                <p><strong>Address:</strong><br><?= nl2br(Helpers::escape($salesOrder['client_address'])) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Order Information</h5>
                            <p><strong>Order #:</strong> <?= str_pad($salesOrder['id'], 4, '0', STR_PAD_LEFT) ?></p>
                            <p>
                                <strong>Status:</strong> 
                                <span class="badge badge-<?= $salesOrder['status'] ?>">
                                    <?= ucfirst($salesOrder['status']) ?>
                                </span>
                            </p>
                            <p><strong>Created:</strong> <?= Helpers::formatDate($salesOrder['created_at']) ?></p>
                            
                            <?php if (!empty($salesOrder['quote_id'])): ?>
                                <p><strong>Quote:</strong> 
                                    <a href="/quotes/<?= $salesOrder['quote_id'] ?>">
                                        Quote #<?= str_pad($salesOrder['quote_id'], 4, '0', STR_PAD_LEFT) ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($salesOrder['notes'])): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Notes</h6>
                                <p><?= nl2br(Helpers::escape($salesOrder['notes'])) ?></p>
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
                            <p class="text-muted">This sales order does not have any items yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>Order Summary</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Items Subtotal:</span>
                        <span><?= Helpers::formatCurrency($salesOrder['items_subtotal']) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Items Tax:</span>
                        <span><?= Helpers::formatCurrency($salesOrder['items_tax_total']) ?></span>
                    </div>
                    
                    <?php if ($salesOrder['tax_total'] > $salesOrder['items_tax_total']): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Global Tax:</span>
                            <span><?= Helpers::formatCurrency($salesOrder['tax_total'] - $salesOrder['items_tax_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($salesOrder['items_discount_total'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Items Discount:</span>
                            <span>-<?= Helpers::formatCurrency($salesOrder['items_discount_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($salesOrder['discount_total'] > $salesOrder['items_discount_total']): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Global Discount:</span>
                            <span>-<?= Helpers::formatCurrency($salesOrder['discount_total'] - $salesOrder['items_discount_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Grand Total:</strong>
                        <strong><?= Helpers::formatCurrency($salesOrder['grand_total']) ?></strong>
                    </div>
                    
                    <?php if ($salesOrder['status'] === 'delivered'): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Order Delivered
                            <br><small>This order has been successfully delivered to the client.</small>
                        </div>
                    <?php elseif ($salesOrder['status'] === 'open'): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-clock"></i> Order Open
                            <br><small>This order is open and ready for delivery.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stock Information -->
            <?php if (!empty($items) && $salesOrder['status'] === 'open'): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Stock Information</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <small><?= Helpers::escape($item['product_code']) ?></small>
                                    <br><?= Helpers::escape($item['product_name']) ?>
                                </div>
                                <div class="text-right">
                                    <span class="badge <?= ($item['available_qty'] ?? 0) >= $item['qty'] ? 'badge-success' : 'badge-warning' ?>">
                                        <?= number_format($item['available_qty'] ?? 0, 2) ?> available
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Documents -->
    <?php if (!empty($invoices)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Related Invoices</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td>#<?= str_pad($invoice['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td><span class="badge badge-<?= $invoice['status'] ?>"><?= ucfirst($invoice['status']) ?></span></td>
                                    <td><?= Helpers::formatCurrency($invoice['grand_total']) ?></td>
                                    <td><?= Helpers::formatCurrency($invoice['paid_total']) ?></td>
                                    <td><?= Helpers::formatCurrency($invoice['grand_total'] - $invoice['paid_total']) ?></td>
                                    <td><?= Helpers::formatDate($invoice['created_at']) ?></td>
                                    <td><a href="/invoices/<?= $invoice['id'] ?>" class="btn btn-sm btn-primary">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
