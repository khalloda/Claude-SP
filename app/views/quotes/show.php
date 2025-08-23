<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.quotes') . ' - Quote #' . str_pad($quote['id'], 4, '0', STR_PAD_LEFT);
$showNav = true;

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1>Quote #<?= str_pad($quote['id'], 4, '0', STR_PAD_LEFT) ?></h1>
        </div>
        
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <span class="badge badge-<?= $quote['status'] ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                <?= ucfirst($quote['status']) ?>
            </span>
            
            <div style="display: flex; gap: 0.5rem;">
                <a href="/quotes" class="btn btn-secondary"><?= I18n::t('actions.back') ?></a>
                
                <?php if ($quote['status'] === 'sent'): ?>
                    <a href="/quotes/<?= $quote['id'] ?>/edit" class="btn btn-primary"><?= I18n::t('actions.edit') ?></a>
                    
                    <form method="POST" action="/quotes/<?= $quote['id'] ?>/approve" style="display: inline;">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-success">
                            Approve
                        </button>
                    </form>
                    
                    <form method="POST" action="/quotes/<?= $quote['id'] ?>/reject" style="display: inline;">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to reject this quote?')">
                            Reject
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if ($quote['status'] === 'approved'): ?>
                    <form method="POST" action="/salesorders/create-from-quote" style="display: inline;">
                        <?= Helpers::csrfField() ?>
                        <input type="hidden" name="quote_id" value="<?= $quote['id'] ?>">
                        <button type="submit" class="btn btn-info">Convert to Sales Order</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quote Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Quote Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Client Information</h5>
                            <p>
                                <strong><?= Helpers::escape($quote['client_name']) ?></strong>
                                <?php if (!empty($quote['client_type'])): ?>
                                    <br><small class="text-muted"><?= ucfirst($quote['client_type']) ?></small>
                                <?php endif; ?>
                            </p>
                            
                            <?php if (!empty($quote['client_email'])): ?>
                                <p><strong>Email:</strong> <?= Helpers::escape($quote['client_email']) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($quote['client_phone'])): ?>
                                <p><strong>Phone:</strong> <?= Helpers::escape($quote['client_phone']) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($quote['client_address'])): ?>
                                <p><strong>Address:</strong><br><?= nl2br(Helpers::escape($quote['client_address'])) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Quote Information</h5>
                            <p><strong>Quote #:</strong> <?= str_pad($quote['id'], 4, '0', STR_PAD_LEFT) ?></p>
                            <p>
                                <strong>Status:</strong> 
                                <span class="badge badge-<?= $quote['status'] ?>">
                                    <?= ucfirst($quote['status']) ?>
                                </span>
                            </p>
                            <p><strong>Created:</strong> <?= Helpers::formatDate($quote['created_at']) ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($quote['notes'])): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Notes</h6>
                                <p><?= nl2br(Helpers::escape($quote['notes'])) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quote Items -->
            <div class="card">
                <div class="card-header">
                    <h3>Quote Items</h3>
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
                                                <strong><?= Helpers::escape($item['product_name']) ?></strong>
                                                <br><small class="text-muted"><?= Helpers::escape($item['classification']) ?></small>
                                            </td>
                                            <td><?= Helpers::escape($item['product_code']) ?></td>
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
                            <p class="text-muted">This quote does not have any items yet.</p>
                        </div>
                    <?php endif; ?>
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
                    <div class="d-flex justify-content-between mb-2">
                        <span>Items Subtotal:</span>
                        <span><?= Helpers::formatCurrency($quote['items_subtotal']) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Items Tax:</span>
                        <span><?= Helpers::formatCurrency($quote['items_tax_total']) ?></span>
                    </div>
                    
                    <?php if ($quote['tax_total'] > $quote['items_tax_total']): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Global Tax:</span>
                            <span><?= Helpers::formatCurrency($quote['tax_total'] - $quote['items_tax_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($quote['items_discount_total'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Items Discount:</span>
                            <span>-<?= Helpers::formatCurrency($quote['items_discount_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($quote['discount_total'] > $quote['items_discount_total']): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Global Discount:</span>
                            <span>-<?= Helpers::formatCurrency($quote['discount_total'] - $quote['items_discount_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Grand Total:</strong>
                        <strong><?= Helpers::formatCurrency($quote['grand_total']) ?></strong>
                    </div>
                    
                    <?php if ($quote['status'] === 'approved'): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Quote Approved
                            <br><small>This quote has been approved and can be converted to a sales order.</small>
                        </div>
                    <?php elseif ($quote['status'] === 'rejected'): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i> Quote Rejected
                            <br><small>This quote has been rejected.</small>
                        </div>
                    <?php elseif ($quote['status'] === 'sent'): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-clock"></i> Pending Response
                            <br><small>This quote is awaiting client approval.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stock Information -->
            <?php if (!empty($items) && $quote['status'] === 'approved'): ?>
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
    <?php if (!empty($salesOrders)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Related Sales Orders</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sales Order #</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salesOrders as $salesOrder): ?>
                                <tr>
                                    <td>#<?= str_pad($salesOrder['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td><span class="badge badge-<?= $salesOrder['status'] ?>"><?= ucfirst($salesOrder['status']) ?></span></td>
                                    <td><?= Helpers::formatCurrency($salesOrder['grand_total']) ?></td>
                                    <td><?= Helpers::formatDate($salesOrder['created_at']) ?></td>
                                    <td><a href="/salesorders/<?= $salesOrder['id'] ?>" class="btn btn-sm btn-primary">View</a></td>
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
