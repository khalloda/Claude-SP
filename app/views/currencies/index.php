<?php
// Create this file: app/views/currencies/index.php

use App\Core\I18n;
use App\Core\Helpers;

$title = 'Currency Management - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: between; align-items: center;">
            <h1 class="card-title">Currency Management</h1>
            <div>
                <button onclick="updateAllRates()" class="btn btn-info">Update Rates</button>
                <a href="/currencies/create" class="btn btn-primary">Add New Currency</a>
            </div>
        </div>
    </div>

    <?php if (!empty($stats)): ?>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total'] ?></div>
                    <div class="stat-label">Total Currencies</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['active'] ?></div>
                    <div class="stat-label">Active Currencies</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['primary'] ?></div>
                    <div class="stat-label">Primary Currency</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= date('Y-m-d', strtotime($stats['last_updated'] ?? 'now')) ?></div>
                    <div class="stat-label">Last Updated</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card-body">
        <?php if (!empty($currencies['items'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Symbol</th>
                            <th>Exchange Rate</th>
                            <th>Decimal Places</th>
                            <th>Status</th>
                            <th>Primary</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($currencies['items'] as $currency): ?>
                            <tr class="<?= $currency['is_primary'] ? 'table-warning' : '' ?>">
                                <td>
                                    <strong><?= Helpers::escape($currency['code']) ?></strong>
                                    <?php if ($currency['is_primary']): ?>
                                        <span class="badge badge-primary ms-1">Primary</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= Helpers::escape($currency['name']) ?></td>
                                <td><span class="currency-symbol"><?= Helpers::escape($currency['symbol']) ?></span></td>
                                <td>
                                    <span class="exchange-rate" data-currency="<?= $currency['code'] ?>"><?= number_format($currency['exchange_rate'], 6) ?></span>
                                    <?php if (!$currency['is_primary']): ?>
                                        <input type="hidden" class="rate-input" name="rates[<?= $currency['code'] ?>]" value="<?= $currency['exchange_rate'] ?>">
                                    <?php endif; ?>
                                </td>
                                <td><?= $currency['decimal_places'] ?></td>
                                <td>
                                    <span class="badge badge-<?= $currency['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $currency['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!$currency['is_primary']): ?>
                                        <form method="POST" action="/currencies/<?= $currency['code'] ?>/set-primary" style="display: inline;">
                                            <?= Helpers::csrfField() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-primary" 
                                                    onclick="return confirm('Set <?= $currency['code'] ?> as primary currency?')">
                                                Set Primary
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">Current Primary</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/currencies/<?= $currency['code'] ?>" class="btn btn-outline-info">View</a>
                                        <a href="/currencies/<?= $currency['code'] ?>/edit" class="btn btn-outline-primary">Edit</a>
                                        <?php if (!$currency['is_primary']): ?>
                                            <form method="POST" action="/currencies/<?= $currency['code'] ?>/delete" style="display: inline;">
                                                <?= Helpers::csrfField() ?>
                                                <button type="submit" class="btn btn-outline-danger" 
                                                        onclick="return confirm('Delete currency <?= $currency['code'] ?>?')">
                                                    Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Bulk Update Exchange Rates Form -->
            <div class="mt-4">
                <form method="POST" action="/currencies/update-rates" id="bulk-rates-form">
                    <?= Helpers::csrfField() ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>Update Exchange Rates</h5>
                        <button type="submit" class="btn btn-success">Save Rate Changes</button>
                    </div>
                    <p class="text-muted">Modify exchange rates above and click "Save Rate Changes" to update multiple rates at once.</p>
                </form>
            </div>

            <!-- Pagination -->
            <?php if ($currencies['pagination']['total_pages'] > 1): ?>
                <nav aria-label="Currency pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $currencies['pagination']['total_pages']; $i++): ?>
                            <li class="page-item <?= $i == $currencies['pagination']['current_page'] ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5">
                <h3 class="text-muted">No Currencies Found</h3>
                <p>Start by adding your first currency to the system.</p>
                <a href="/currencies/create" class="btn btn-primary btn-lg">Add First Currency</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 10px;
    text-align: center;
}
.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}
.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}
.currency-symbol {
    font-family: monospace;
    font-weight: bold;
    font-size: 1.1em;
}
.exchange-rate {
    font-family: monospace;
}
</style>

<script>
// Make exchange rates editable inline
document.addEventListener('DOMContentLoaded', function() {
    // Make exchange rates clickable to edit
    document.querySelectorAll('.exchange-rate').forEach(function(element) {
        if (element.dataset.currency) {
            element.addEventListener('click', function() {
                makeRateEditable(this);
            });
        }
    });
});

function makeRateEditable(element) {
    if (element.querySelector('input')) return; // Already editing
    
    const currentValue = element.textContent.replace(/,/g, '');
    const currency = element.dataset.currency;
    
    element.innerHTML = `<input type="number" step="0.000001" value="${currentValue}" 
                        class="form-control form-control-sm d-inline-block" 
                        style="width: 120px;" 
                        onblur="saveRate(this, '${currency}')"
                        onkeypress="if(event.key==='Enter') saveRate(this, '${currency}')">`;
    element.querySelector('input').focus();
}

function saveRate(input, currency) {
    const newRate = parseFloat(input.value);
    const parent = input.parentElement;
    
    if (isNaN(newRate) || newRate <= 0) {
        alert('Please enter a valid exchange rate');
        input.focus();
        return;
    }
    
    // Update the display
    parent.innerHTML = number_format(newRate, 6);
    
    // Update the hidden form input
    const hiddenInput = document.querySelector(`input[name="rates[${currency}]"]`);
    if (hiddenInput) {
        hiddenInput.value = newRate;
    }
}

function updateAllRates() {
    if (confirm('Fetch latest exchange rates from external API?')) {
        // TODO: Implement API call to update rates
        alert('Feature coming soon! For now, update rates manually.');
    }
}

function number_format(number, decimals) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
