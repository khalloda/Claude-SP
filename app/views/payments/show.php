<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.payments') . ' - Payment #' . str_pad($payment['id'], 4, '0', STR_PAD_LEFT);

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1>Payment #<?= str_pad($payment['id'], 4, '0', STR_PAD_LEFT) ?></h1>
            <p class="text-muted">
                <?= Helpers::formatDate($payment['created_at']) ?>
            </p>
        </div>
        
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <span class="badge badge-success" style="font-size: 1rem; padding: 0.5rem 1rem;">
                <?= Helpers::formatCurrency($payment['amount']) ?>
            </span>
            
            <div style="display: flex; gap: 0.5rem;">
                <a href="/payments" class="btn btn-secondary"><?= I18n::t('actions.back') ?></a>
                
                <?php 
                // Check if payment is recent enough to edit/delete
                $paymentTime = strtotime($payment['created_at']);
                $twentyFourHoursAgo = time() - (24 * 60 * 60);
                $oneHourAgo = time() - (60 * 60);
                ?>
                
                <?php if ($paymentTime > $twentyFourHoursAgo): ?>
                    <a href="/payments/<?= $payment['id'] ?>/edit" class="btn btn-primary">
                        <?= I18n::t('actions.edit') ?>
                    </a>
                <?php endif; ?>
                
                <?php if ($paymentTime > $oneHourAgo): ?>
                    <form method="POST" action="/payments/<?= $payment['id'] ?>/delete" style="display: inline;" 
                          onsubmit="return confirm('Are you sure you want to reverse this payment? This will update the invoice balance and cannot be undone.')">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="btn btn-danger">Reverse Payment</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Payment Details -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Payment Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Payment Details</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Payment ID:</strong></td>
                                    <td>#<?= str_pad($payment['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td style="color: #28a745; font-weight: bold; font-size: 1.2rem;">
                                        <?= Helpers::formatCurrency($payment['amount']) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?= ucfirst(str_replace('_', ' ', $payment['method'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Date:</strong></td>
                                    <td><?= Helpers::formatDate($payment['created_at']) ?></td>
                                </tr>
                                <?php if (!empty($payment['note'])): ?>
                                <tr>
                                    <td><strong>Note:</strong></td>
                                    <td><?= nl2br(Helpers::escape($payment['note'])) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Related Invoice</h5>
                            <div class="p-3" style="background-color: #f8f9fa; border-radius: 0.375rem;">
                                <p>
                                    <strong>Invoice:</strong> 
                                    <a href="/invoices/<?= $payment['invoice_id'] ?>" style="text-decoration: none; color: #667eea;">
                                        #<?= str_pad($payment['invoice_id'], 4, '0', STR_PAD_LEFT) ?>
                                    </a>
                                </p>
                                <p><strong>Invoice Date:</strong> <?= Helpers::formatDate($payment['invoice_date']) ?></p>
                                <p><strong>Invoice Total:</strong> <?= Helpers::formatCurrency($payment['invoice_total']) ?></p>
                                <p><strong>Amount Paid:</strong> <?= Helpers::formatCurrency($payment['invoice_paid']) ?></p>
                                <p>
                                    <strong>Invoice Status:</strong> 
                                    <span class="badge badge-<?= $payment['invoice_status'] ?>">
                                        <?= ucfirst($payment['invoice_status']) ?>
                                    </span>
                                </p>
                                <p>
                                    <strong>Remaining Balance:</strong>
                                    <?php $balance = $payment['invoice_total'] - $payment['invoice_paid']; ?>
                                    <span style="color: <?= $balance > 0 ? '#dc3545' : '#28a745' ?>; font-weight: bold;">
                                        <?= Helpers::formatCurrency($balance) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Client Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><?= Helpers::escape($payment['client_name']) ?></h5>
                            <p class="text-muted"><?= ucfirst($payment['client_type']) ?></p>
                            
                            <?php if (!empty($payment['client_email'])): ?>
                                <p><strong>Email:</strong> 
                                    <a href="mailto:<?= Helpers::escape($payment['client_email']) ?>">
                                        <?= Helpers::escape($payment['client_email']) ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($payment['client_phone'])): ?>
                                <p><strong>Phone:</strong> 
                                    <a href="tel:<?= Helpers::escape($payment['client_phone']) ?>">
                                        <?= Helpers::escape($payment['client_phone']) ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($payment['client_address'])): ?>
                                <p><strong>Address:</strong><br>
                                    <?= nl2br(Helpers::escape($payment['client_address'])) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="text-center">
                                <a href="/clients/<?= $payment['client_id'] ?>" class="btn btn-outline-primary">
                                    View Client Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Payment Actions -->
            <?php if ($paymentTime > $twentyFourHoursAgo || $paymentTime > $oneHourAgo): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Payment Actions</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($paymentTime > $twentyFourHoursAgo): ?>
                            <a href="/payments/<?= $payment['id'] ?>/edit" class="btn btn-primary btn-block mb-2">
                                Edit Payment Details
                            </a>
                            <small class="text-muted d-block mb-3">
                                You can edit payment method and notes within 24 hours of creation.
                            </small>
                        <?php endif; ?>
                        
                        <?php if ($paymentTime > $oneHourAgo): ?>
                            <form method="POST" action="/payments/<?= $payment['id'] ?>/delete" 
                                  onsubmit="return confirm('Are you sure you want to reverse this payment? This action will:\n\n• Remove this payment record\n• Reduce the invoice paid amount\n• Update the invoice status\n\nThis cannot be undone.')">
                                <?= Helpers::csrfField() ?>
                                <button type="submit" class="btn btn-danger btn-block">
                                    Reverse Payment
                                </button>
                            </form>
                            <small class="text-muted d-block mt-2">
                                Payments can only be reversed within 1 hour of creation.
                            </small>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <small>This payment is too old to be edited or reversed. Contact system administrator for payment adjustments.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Related Payments for Same Invoice -->
            <?php if (!empty($relatedPayments) && count($relatedPayments) > 1): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Other Payments for This Invoice</h4>
                    </div>
                    <div class="card-body">
                        <?php foreach ($relatedPayments as $relatedPayment): ?>
                            <?php if ($relatedPayment['id'] != $payment['id']): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2" 
                                     style="background-color: #f8f9fa; border-radius: 0.375rem;">
                                    <div>
                                        <a href="/payments/<?= $relatedPayment['id'] ?>" style="text-decoration: none;">
                                            #<?= str_pad($relatedPayment['id'], 4, '0', STR_PAD_LEFT) ?>
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            <?= Helpers::formatDate($relatedPayment['created_at']) ?>
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <strong style="color: #28a745;">
                                            <?= Helpers::formatCurrency($relatedPayment['amount']) ?>
                                        </strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= ucfirst(str_replace('_', ' ', $relatedPayment['method'])) ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Client Payments -->
            <?php if (!empty($clientPayments) && count($clientPayments) > 1): ?>
                <div class="card">
                    <div class="card-header">
                        <h4>Recent Client Payments</h4>
                    </div>
                    <div class="card-body">
                        <?php $count = 0; ?>
                        <?php foreach ($clientPayments as $clientPayment): ?>
                            <?php if ($clientPayment['id'] != $payment['id'] && $count < 4): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2" 
                                     style="background-color: #f8f9fa; border-radius: 0.375rem;">
                                    <div>
                                        <a href="/payments/<?= $clientPayment['id'] ?>" style="text-decoration: none;">
                                            #<?= str_pad($clientPayment['id'], 4, '0', STR_PAD_LEFT) ?>
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            Invoice #<?= $clientPayment['invoice_id'] ?>
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <strong style="color: #28a745;">
                                            <?= Helpers::formatCurrency($clientPayment['amount']) ?>
                                        </strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= Helpers::formatDate($clientPayment['created_at']) ?>
                                        </small>
                                    </div>
                                </div>
                                <?php $count++; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <?php if (count($clientPayments) > 5): ?>
                            <div class="text-center mt-3">
                                <a href="/clients/<?= $payment['client_id'] ?>" class="btn btn-sm btn-outline-primary">
                                    View All Client Payments
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Status badges */
.badge-open { background-color: #007bff; }
.badge-partial { background-color: #ffc107; color: #000; }
.badge-paid { background-color: #28a745; }
.badge-void { background-color: #6c757d; }
.badge-secondary { background-color: #6c757d; }

.table-borderless td {
    border: none;
    padding: 0.5rem 0;
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

[dir="rtl"] .text-right {
    text-align: left !important;
}

[dir="rtl"] .table td {
    text-align: right;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Payment Show Page Loaded');
    console.log('Payment ID: <?= $payment['id'] ?>');
    console.log('Amount: <?= $payment['amount'] ?>');
    console.log('Method: <?= $payment['method'] ?>');
    
    // Check payment age for action availability
    const paymentTime = new Date('<?= $payment['created_at'] ?>').getTime();
    const now = new Date().getTime();
    const hoursSincePayment = (now - paymentTime) / (1000 * 60 * 60);
    
    console.log('Hours since payment:', hoursSincePayment.toFixed(2));
    
    if (hoursSincePayment > 24) {
        console.log('Payment too old to edit');
    }
    
    if (hoursSincePayment > 1) {
        console.log('Payment too old to reverse');
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
