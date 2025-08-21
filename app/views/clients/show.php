<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = $client['name'] . ' - ' . I18n::t('navigation.clients') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title"><?= Helpers::escape($client['name']) ?></h1>
            <div>
                <a href="/clients/<?= $client['id'] ?>/edit" class="btn btn-primary"><?= I18n::t('actions.edit') ?></a>
                <a href="/clients" class="btn btn-secondary"><?= I18n::t('actions.back') ?></a>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Client Details -->
        <div class="row" style="margin-bottom: 2rem;">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong><?= I18n::t('common.type') ?>:</strong></td>
                        <td><?= Helpers::escape(ucfirst($client['type'])) ?></td>
                    </tr>
                    <tr>
                        <td><strong><?= I18n::t('common.email') ?>:</strong></td>
                        <td><?= Helpers::escape($client['email']) ?></td>
                    </tr>
                    <tr>
                        <td><strong><?= I18n::t('common.phone') ?>:</strong></td>
                        <td><?= Helpers::escape($client['phone']) ?></td>
                    </tr>
                    <tr>
                        <td><strong><?= I18n::t('common.created_at') ?>:</strong></td>
                        <td><?= Helpers::formatDate($client['created_at']) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <div>
                    <strong><?= I18n::t('common.address') ?>:</strong><br>
                    <?= Helpers::escape($client['address']) ?>
                </div>
                
                <div style="margin-top: 1rem;">
                    <strong>Financial Summary:</strong><br>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-top: 0.5rem;">
                        <div>Total Invoiced: <strong><?= Helpers::formatCurrency($balance['total_invoiced']) ?></strong></div>
                        <div>Total Paid: <strong><?= Helpers::formatCurrency($balance['total_paid']) ?></strong></div>
                        <div>Outstanding Balance: <strong style="color: <?= $balance['balance'] > 0 ? '#dc3545' : '#28a745' ?>">
                            <?= Helpers::formatCurrency($balance['balance']) ?>
                        </strong></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <div class="tab-navigation" style="border-bottom: 2px solid #eee; margin-bottom: 2rem;">
                <button class="tab-button active" onclick="showTab('quotes')"><?= I18n::t('navigation.quotes') ?> (<?= count($quotes) ?>)</button>
                <button class="tab-button" onclick="showTab('orders')"><?= I18n::t('navigation.sales_orders') ?> (<?= count($salesOrders) ?>)</button>
                <button class="tab-button" onclick="showTab('invoices')"><?= I18n::t('navigation.invoices') ?> (<?= count($invoices) ?>)</button>
                <button class="tab-button" onclick="showTab('payments')"><?= I18n::t('navigation.payments') ?> (<?= count($payments) ?>)</button>
            </div>

            <!-- Quotes Tab -->
            <div id="quotes-tab" class="tab-content active">
                <h3><?= I18n::t('navigation.quotes') ?></h3>
                <?php if (!empty($quotes)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><?= I18n::t('common.status') ?></th>
                                    <th><?= I18n::t('common.total') ?></th>
                                    <th><?= I18n::t('common.created_at') ?></th>
                                    <th><?= I18n::t('common.action') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($quotes as $quote): ?>
                                    <tr>
                                        <td>#<?= $quote['id'] ?></td>
                                        <td><span class="badge badge-<?= $quote['status'] ?>"><?= ucfirst($quote['status']) ?></span></td>
                                        <td><?= Helpers::formatCurrency($quote['grand_total']) ?></td>
                                        <td><?= Helpers::formatDate($quote['created_at']) ?></td>
                                        <td><a href="/quotes/<?= $quote['id'] ?>" class="btn btn-sm btn-primary"><?= I18n::t('actions.view') ?></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No quotes found for this client.</p>
                <?php endif; ?>
            </div>

            <!-- Sales Orders Tab -->
            <div id="orders-tab" class="tab-content">
                <h3><?= I18n::t('navigation.sales_orders') ?></h3>
                <?php if (!empty($salesOrders)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><?= I18n::t('common.status') ?></th>
                                    <th><?= I18n::t('common.total') ?></th>
                                    <th><?= I18n::t('common.created_at') ?></th>
                                    <th><?= I18n::t('common.action') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($salesOrders as $order): ?>
                                    <tr>
                                        <td>#<?= $order['id'] ?></td>
                                        <td><span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                        <td><?= Helpers::formatCurrency($order['grand_total']) ?></td>
                                        <td><?= Helpers::formatDate($order['created_at']) ?></td>
                                        <td><a href="/salesorders/<?= $order['id'] ?>" class="btn btn-sm btn-primary"><?= I18n::t('actions.view') ?></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No sales orders found for this client.</p>
                <?php endif; ?>
            </div>

            <!-- Invoices Tab -->
            <div id="invoices-tab" class="tab-content">
                <h3><?= I18n::t('navigation.invoices') ?></h3>
                <?php if (!empty($invoices)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><?= I18n::t('common.status') ?></th>
                                    <th><?= I18n::t('common.total') ?></th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th><?= I18n::t('common.created_at') ?></th>
                                    <th><?= I18n::t('common.action') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr>
                                        <td>#<?= $invoice['id'] ?></td>
                                        <td><span class="badge badge-<?= $invoice['status'] ?>"><?= ucfirst($invoice['status']) ?></span></td>
                                        <td><?= Helpers::formatCurrency($invoice['grand_total']) ?></td>
                                        <td><?= Helpers::formatCurrency($invoice['paid_total']) ?></td>
                                        <td><?= Helpers::formatCurrency($invoice['grand_total'] - $invoice['paid_total']) ?></td>
                                        <td><?= Helpers::formatDate($invoice['created_at']) ?></td>
                                        <td><a href="/invoices/<?= $invoice['id'] ?>" class="btn btn-sm btn-primary"><?= I18n::t('actions.view') ?></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No invoices found for this client.</p>
                <?php endif; ?>
            </div>

            <!-- Payments Tab -->
            <div id="payments-tab" class="tab-content">
                <h3><?= I18n::t('navigation.payments') ?></h3>
                <?php if (!empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th><?= I18n::t('common.amount') ?></th>
                                    <th>Method</th>
                                    <th><?= I18n::t('common.notes') ?></th>
                                    <th><?= I18n::t('common.created_at') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td>#<?= $payment['invoice_id'] ?></td>
                                        <td><?= Helpers::formatCurrency($payment['amount']) ?></td>
                                        <td><?= Helpers::escape($payment['method']) ?></td>
                                        <td><?= Helpers::escape($payment['note']) ?></td>
                                        <td><?= Helpers::formatDate($payment['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No payments found for this client.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.tab-navigation {
    display: flex;
    gap: 1rem;
}

.tab-button {
    padding: 0.5rem 1rem;
    border: none;
    background: #f8f9fa;
    cursor: pointer;
    border-radius: 5px 5px 0 0;
    transition: background-color 0.3s;
}

.tab-button.active {
    background: #667eea;
    color: white;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-size: 0.8rem;
    color: white;
}

.badge-sent { background-color: #6c757d; }
.badge-approved { background-color: #28a745; }
.badge-rejected { background-color: #dc3545; }
.badge-open { background-color: #007bff; }
.badge-delivered { background-color: #28a745; }
.badge-partial { background-color: #ffc107; color: #000; }
.badge-paid { background-color: #28a745; }
.badge-void { background-color: #6c757d; }

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
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
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
    
    .tab-navigation {
        flex-direction: column;
    }
}
</style>

<script>
function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(tab => tab.classList.remove('active'));
    
    // Remove active class from all buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => button.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
