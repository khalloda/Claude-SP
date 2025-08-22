<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.payments');

ob_start();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= I18n::t('navigation.payments') ?></h1>
        <div style="display: flex; gap: 1rem;">
            <a href="/payments/create" class="btn btn-primary">
                + <?= I18n::t('actions.create') ?> Payment
            </a>
            <a href="/payments/export<?= !empty($_GET) ? '?' . http_build_query($_GET) : '' ?>" class="btn btn-secondary">
                Export CSV
            </a>
        </div>
    </div>

    <!-- Payment Summary Cards -->
    <?php if (!empty($summary)): ?>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-primary"><?= number_format($summary['summary']['total_payments']) ?></h3>
                        <p class="text-muted">Total Payments (30 days)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-success"><?= Helpers::formatCurrency($summary['summary']['total_amount']) ?></h3>
                        <p class="text-muted">Total Amount</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-info"><?= Helpers::formatCurrency($summary['summary']['average_amount']) ?></h3>
                        <p class="text-muted">Average Payment</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-warning"><?= number_format($summary['summary']['unique_clients']) ?></h3>
                        <p class="text-muted">Paying Clients</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/payments">
                <div class="row">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="Client name, note, method..."
                               value="<?= Helpers::escape($search ?? '') ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="client_id" class="form-label">Client</label>
                        <select class="form-control" id="client_id" name="client_id">
                            <option value="">All Clients</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>" 
                                        <?= ($clientId ?? '') == $client['id'] ? 'selected' : '' ?>>
                                    <?= Helpers::escape($client['name']) ?> (<?= ucfirst($client['type']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="method" class="form-label">Method</label>
                        <select class="form-control" id="method" name="method">
                            <option value="">All Methods</option>
                            <?php foreach ($paymentMethods as $paymentMethod): ?>
                                <option value="<?= $paymentMethod ?>" 
                                        <?= ($method ?? '') === $paymentMethod ? 'selected' : '' ?>>
                                    <?= ucfirst(str_replace('_', ' ', $paymentMethod)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_from" 
                               name="date_from" 
                               value="<?= Helpers::input('date_from', '') ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_to" 
                               name="date_to" 
                               value="<?= Helpers::input('date_to', '') ?>">
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-secondary">Filter</button>
                        <a href="/payments" class="btn btn-outline-secondary ml-2">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header">
            <h4>Payment Records</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($payments['data'])): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Payment #</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Invoice #</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Note</th>
                                <th><?= I18n::t('common.action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments['data'] as $payment): ?>
                                <tr>
                                    <td>
                                        <a href="/payments/<?= $payment['id'] ?>" style="text-decoration: none; color: #667eea;">
                                            #<?= str_pad($payment['id'], 4, '0', STR_PAD_LEFT) ?>
                                        </a>
                                    </td>
                                    <td><?= Helpers::formatDate($payment['created_at']) ?></td>
                                    <td>
                                        <div>
                                            <strong><?= Helpers::escape($payment['client_name'] ?? 'Unknown') ?></strong>
                                            <small style="display: block; color: #666;">
                                                <?= ucfirst($payment['client_type'] ?? 'company') ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="/invoices/<?= $payment['invoice_id'] ?>" style="text-decoration: none; color: #667eea;">
                                            #<?= str_pad($payment['invoice_id'], 4, '0', STR_PAD_LEFT) ?>
                                        </a>
                                        <?php if (!empty($payment['invoice_total'])): ?>
                                            <br><small style="color: #666;">
                                                Total: <?= Helpers::formatCurrency($payment['invoice_total']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color: #28a745; font-weight: bold;">
                                            <?= Helpers::formatCurrency($payment['amount']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?= ucfirst(str_replace('_', ' ', $payment['method'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($payment['note'])): ?>
                                            <span title="<?= Helpers::escape($payment['note']) ?>">
                                                <?= Helpers::escape(strlen($payment['note']) > 30 ? substr($payment['note'], 0, 30) . '...' : $payment['note']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                            <a href="/payments/<?= $payment['id'] ?>" class="btn btn-sm btn-secondary">
                                                <?= I18n::t('actions.view') ?>
                                            </a>
                                            
                                            <?php 
                                            // Check if payment is recent enough to edit (24 hours)
                                            $paymentTime = strtotime($payment['created_at']);
                                            $twentyFourHoursAgo = time() - (24 * 60 * 60);
                                            $oneHourAgo = time() - (60 * 60);
                                            ?>
                                            
                                            <?php if ($paymentTime > $twentyFourHoursAgo): ?>
                                                <a href="/payments/<?= $payment['id'] ?>/edit" class="btn btn-sm btn-primary">
                                                    <?= I18n::t('actions.edit') ?>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($paymentTime > $oneHourAgo): ?>
                                                <form method="POST" action="/payments/<?= $payment['id'] ?>/delete" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to reverse this payment? This will update the invoice balance.')">
                                                    <?= Helpers::csrfField() ?>
                                                    <button type="submit" class="btn btn-sm btn-danger">Reverse</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($payments['last_page']) && $payments['last_page'] > 1): ?>
                    <div class="pagination-wrapper">
                        <?php
                        $currentPage = $payments['current_page'];
                        $lastPage = $payments['last_page'];
                        $queryParams = $_GET;
                        ?>
                        
                        <?php if ($currentPage > 1): ?>
                            <?php 
                            $queryParams['page'] = $currentPage - 1;
                            ?>
                            <a href="/payments?<?= http_build_query($queryParams) ?>" class="btn btn-sm btn-secondary">Previous</a>
                        <?php endif; ?>
                        
                        <span class="mx-3">
                            Page <?= $currentPage ?> of <?= $lastPage ?>
                            (<?= $payments['from'] ?>-<?= $payments['to'] ?> of <?= $payments['total'] ?> payments)
                        </span>
                        
                        <?php if ($currentPage < $lastPage): ?>
                            <?php 
                            $queryParams['page'] = $currentPage + 1;
                            ?>
                            <a href="/payments?<?= http_build_query($queryParams) ?>" class="btn btn-sm btn-secondary">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <h4>No payments found</h4>
                    <p>No payment records match your current filters.</p>
                    <a href="/payments/create" class="btn btn-primary">Record First Payment</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment Methods Summary -->
    <?php if (!empty($summary['methods'])): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h4>Payment Methods Summary (Last 30 Days)</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($summary['methods'] as $methodSummary): ?>
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3" style="border: 1px solid #dee2e6; border-radius: 0.375rem;">
                                <h5><?= ucfirst(str_replace('_', ' ', $methodSummary['method'])) ?></h5>
                                <p class="mb-1">
                                    <strong><?= Helpers::formatCurrency($methodSummary['total']) ?></strong>
                                </p>
                                <small class="text-muted"><?= $methodSummary['count'] ?> payments</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Additional styles for payments */
.badge-secondary {
    background-color: #6c757d;
}

.pagination-wrapper {
    margin-top: 1.5rem;
    text-align: center;
    padding: 1rem;
    border-top: 1px solid #dee2e6;
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

[dir="rtl"] .text-center {
    text-align: center !important;
}

[dir="rtl"] .table th,
[dir="rtl"] .table td {
    text-align: right;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Payments Index Loaded');
    console.log('Total Payments:', <?= count($payments['data'] ?? []) ?>);
    
    // Auto-submit form on filter change (optional)
    const filterInputs = document.querySelectorAll('#client_id, #method, #date_from, #date_to');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Optionally auto-submit the form
            // this.form.submit();
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
