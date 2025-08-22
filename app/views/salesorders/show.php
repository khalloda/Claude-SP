<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.sales_orders') . ' - Sales Order #' . str_pad($salesOrder['id'], 4, '0', STR_PAD_LEFT);

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
                    
                <?php elseif ($salesOrder['status'] === 'rejected'): ?>
                    <form method="POST" action="/salesorders/<?= $salesOrder['id'] ?>/delete" style="display: inline;" 
                          onsubmit="return confirm('Are you sure you want to delete this rejected sales order?')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-danger">Delete Order</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Client & Order Info -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4><?= I18n::t('navigation.client') ?> Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><?= Helpers::escape($salesOrder['client_name']) ?></h5>
                            <p class="text-muted"><?= ucfirst($salesOrder['client_type']) ?></p>
                            <?php if (!empty($salesOrder['client_email'])): ?>
                                <p><strong>Email:</strong> 
                                    <a href="mailto:<?= Helpers::escape($salesOrder['client_email']) ?>">
                                        <?= Helpers::escape($salesOrder['client_email']) ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($salesOrder['client_phone'])): ?>
                                <p><strong>Phone:</strong> 
                                    <a href="tel:<?= Helpers::escape($salesOrder['client_phone']) ?>">
                                        <?= Helpers::escape($salesOrder['client_phone']) ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($salesOrder['client_address'])): ?>
                                <p><strong>Address:</strong><br><?= nl2br(Helpers::escape($salesOrder['client_address'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Order Date:</strong> <?= Helpers::formatDate($salesOrder['created_at']) ?></p>
                            <?php if (!empty($salesOrder['quote_id'])): ?>
                                <p><strong>Source Quote:</strong> 
                                    <a href="/quotes/<?= $salesOrder['quote_id'] ?>" style="text-decoration: none; color: #667eea;">
                                        Quote #<?= str_pad($salesOrder['quote_id'], 4, '0', STR_PAD_LEFT) ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($salesOrder['notes'])): ?>
                                <p><strong>Notes:</strong><br><?= nl2br(Helpers::escape($salesOrder['notes'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Order Summary</h4>
                </div>
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Subtotal:</span>
                        <strong><?= Helpers::formatCurrency($salesOrder['items_subtotal']) ?></strong>
                    </div>
                    
                    <?php if ($salesOrder['items_tax_total'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Items Tax:</span>
                            <span><?= Helpers::formatCurrency($salesOrder['items_tax_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($salesOrder['items_discount_total'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Items Discount:</span>
                            <span style="color: #28a745;">-<?= Helpers::formatCurrency($salesOrder['items_discount_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($salesOrder['tax_total'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Global Tax (<?= $salesOrder['global_tax_value'] ?><?= $salesOrder['global_tax_type'] === 'percent' ? '%' : '' ?>):</span>
                            <span><?= Helpers::formatCurrency($salesOrder['tax_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($salesOrder['discount_total'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Global Discount (<?= $salesOrder['global_discount_value'] ?><?= $salesOrder['global_discount_type'] === 'percent' ? '%' : '' ?>):</span>
                            <span style="color: #28a745;">-<?= Helpers::formatCurrency($salesOrder['discount_total']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold;">
                        <span>Grand Total:</span>
                        <span><?= Helpers::formatCurrency($salesOrder['grand_total']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Order Items</h4>
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
                                <th>Stock Status</th>
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
                                            <span class="badge badge-secondary">N/A</span>
                                        <?php endif; ?>
                                    </td>
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

    <!-- Order Status Timeline -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Order Status Timeline</h4>
        </div>
        <div class="card-body">
            <div class="timeline">
                <div class="timeline-item <?= in_array($salesOrder['status'], ['open', 'delivered', 'rejected']) ? 'completed' : '' ?>">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <h6>Order Created</h6>
                        <p class="text-muted"><?= Helpers::formatDate($salesOrder['created_at']) ?></p>
                        <?php if (!empty($salesOrder['quote_id'])): ?>
                            <small>Converted from Quote #<?= str_pad($salesOrder['quote_id'], 4, '0', STR_PAD_LEFT) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($salesOrder['status'] === 'delivered'): ?>
                    <div class="timeline-item completed">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6>Order Delivered</h6>
                            <p class="text-muted">Products delivered to client</p>
                            <small class="text-success">✓ Stock levels updated</small>
                        </div>
                    </div>
                <?php elseif ($salesOrder['status'] === 'rejected'): ?>
                    <div class="timeline-item rejected">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6>Order Rejected</h6>
                            <p class="text-muted">Order was rejected</p>
                            <small class="text-danger">✓ Reserved stock released</small>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="timeline-item pending">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6>Awaiting Delivery</h6>
                            <p class="text-muted">Order is ready for delivery</p>
                            <small class="text-info">Stock reserved for this order</small>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="timeline-item <?= $salesOrder['status'] === 'delivered' ? 'pending' : 'future' ?>">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <h6>Invoice Generation</h6>
                        <p class="text-muted">Convert to invoice for payment processing</p>
                        <?php if ($salesOrder['status'] === 'delivered'): ?>
                            <small class="text-info">Ready to convert to invoice</small>
                        <?php else: ?>
                            <small class="text-muted">Pending delivery completion</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Documents -->
    <div class="card">
        <div class="card-header">
            <h4>Related Documents</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <?php if (!empty($salesOrder['quote_id'])): ?>
                    <div class="col-md-4">
                        <div class="p-3" style="background-color: #f8f9fa; border-radius: 0.375rem; text-center;">
                            <h6>Source Quote</h6>
                            <a href="/quotes/<?= $salesOrder['quote_id'] ?>" class="btn btn-outline-primary">
                                View Quote #<?= str_pad($salesOrder['quote_id'], 4, '0', STR_PAD_LEFT) ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="col-md-4">
                    <div class="p-3" style="background-color: #f8f9fa; border-radius: 0.375rem; text-center;">
                        <h6>Client Profile</h6>
                        <a href="/clients/<?= $salesOrder['client_id'] ?>" class="btn btn-outline-primary">
                            View Client Profile
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="p-3" style="background-color: #f8f9fa; border-radius: 0.375rem; text-center;">
                        <h6>Related Invoices</h6>
                        <?php 
                        // Note: This would typically query for invoices created from this sales order
                        // For now, we'll show a placeholder button
                        ?>
                        <button class="btn btn-outline-secondary" disabled>
                            No Invoices Yet
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Status badges */
.badge-open { background-color: #007bff; }
.badge-delivered { background-color: #28a745; }
.badge-rejected { background-color: #dc3545; }
.badge-success { background-color: #28a745; }
.badge-warning { background-color: #ffc107; color: #000; }
.badge-secondary { background-color: #6c757d; }

/* Timeline styles */
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
    padding-left: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background: #dee2e6;
    border: 2px solid #fff;
}

.timeline-item.completed .timeline-marker {
    background: #28a745;
}

.timeline-item.rejected .timeline-marker {
    background: #dc3545;
}

.timeline-item.pending .timeline-marker {
    background: #ffc107;
}

.timeline-item.future .timeline-marker {
    background: #e9ecef;
}

.timeline-content h6 {
    margin-bottom: 0.5rem;
    color: #495057;
}

.timeline-content p {
    margin-bottom: 0.25rem;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #e3e6f0;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

/* RTL Support */
[dir="rtl"] .d-flex.justify-content-between {
    flex-direction: row-reverse;
}

[dir="rtl"] .timeline {
    padding-left: 0;
    padding-right: 2rem;
}

[dir="rtl"] .timeline::before {
    left: auto;
    right: 1rem;
}

[dir="rtl"] .timeline-item {
    padding-left: 0;
    padding-right: 2rem;
}

[dir="rtl"] .timeline-marker {
    left: auto;
    right: -2rem;
}

[dir="rtl"] .table th,
[dir="rtl"] .table td {
    text-align: right;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sales Order Show Page Loaded');
    console.log('Sales Order ID: <?= $salesOrder['id'] ?>');
    console.log('Status: <?= $salesOrder['status'] ?>');
    console.log('Grand Total: <?= $salesOrder['grand_total'] ?>');
    
    // Check stock status for items
    const items = <?= json_encode($items) ?>;
    let lowStockItems = 0;
    let outOfStockItems = 0;
    
    items.forEach(item => {
        if (item.available_qty !== undefined) {
            if (item.available_qty < item.qty) {
                outOfStockItems++;
            } else if (item.available_qty < (item.qty * 1.5)) {
                lowStockItems++;
            }
        }
    });
    
    console.log('Stock Status Summary:');
    console.log('- Low stock items:', lowStockItems);
    console.log('- Out of stock items:', outOfStockItems);
    
    if (outOfStockItems > 0) {
        console.warn('Warning: Some items may not have sufficient stock');
    }
    
    // Status-specific information
    const status = '<?= $salesOrder['status'] ?>';
    switch(status) {
        case 'open':
            console.log('Actions available: Deliver, Convert to Invoice, Reject');
            break;
        case 'delivered':
            console.log('Actions available: Convert to Invoice');
            break;
        case 'rejected':
            console.log('Actions available: Delete');
            break;
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?> 
