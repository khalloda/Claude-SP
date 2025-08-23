<?php
// Create this file: app/views/currencies/show.php

use App\Core\I18n;
use App\Core\Helpers;

$title = $currency['name'] . ' (' . $currency['code'] . ') - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title">
                <?= Helpers::escape($currency['name']) ?> (<?= Helpers::escape($currency['code']) ?>)
                <?php if ($currency['is_primary']): ?>
                    <span class="badge badge-primary ms-2">Primary Currency</span>
                <?php endif; ?>
            </h1>
            <div>
                <a href="/currencies/<?= $currency['code'] ?>/edit" class="btn btn-primary">Edit Currency</a>
                <a href="/currencies" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <!-- Currency Details -->
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Currency Code:</strong></td>
                        <td><?= Helpers::escape($currency['code']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Currency Name:</strong></td>
                        <td><?= Helpers::escape($currency['name']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Symbol:</strong></td>
                        <td><span class="currency-symbol"><?= Helpers::escape($currency['symbol']) ?></span></td>
                    </tr>
                    <tr>
                        <td><strong>Decimal Places:</strong></td>
                        <td><?= $currency['decimal_places'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Exchange Rate:</strong></td>
                        <td class="exchange-rate"><?= number_format($currency['exchange_rate'], 6) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span class="badge badge-<?= $currency['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $currency['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Primary Currency:</strong></td>
                        <td>
                            <?php if ($currency['is_primary']): ?>
                                <span class="badge badge-primary">Yes</span>
                            <?php else: ?>
                                <span class="text-muted">No</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td><?= Helpers::formatDate($currency['created_at']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Last Updated:</strong></td>
                        <td><?= Helpers::formatDate($currency['updated_at']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Conversion Examples -->
        <div class="row mb-4">
            <div class="col-12">
                <h3>Conversion Examples</h3>
                <div class="conversion-examples">
                    <div class="example-box">
                        <div class="example-title">From Primary Currency:</div>
                        <div class="example-conversion">
                            1.00 Primary = <?= number_format(1 / $currency['exchange_rate'], $currency['decimal_places']) ?> <?= $currency['code'] ?>
                        </div>
                        <div class="example-conversion">
                            100.00 Primary = <?= number_format(100 / $currency['exchange_rate'], $currency['decimal_places']) ?> <?= $currency['code'] ?>
                        </div>
                    </div>
                    
                    <div class="example-box">
                        <div class="example-title">To Primary Currency:</div>
                        <div class="example-conversion">
                            1 <?= $currency['code'] ?> = <?= number_format($currency['exchange_rate'], 6) ?> Primary
                        </div>
                        <div class="example-conversion">
                            100 <?= $currency['code'] ?> = <?= number_format(100 * $currency['exchange_rate'], 6) ?> Primary
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchange Rate History -->
        <?php if (!empty($history)): ?>
            <div class="row">
                <div class="col-12">
                    <h3>Exchange Rate History</h3>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Old Rate</th>
                                    <th>New Rate</th>
                                    <th>Change</th>
                                    <th>Updated By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $record): ?>
                                    <tr>
                                        <td><?= Helpers::formatDate($record['created_at']) ?></td>
                                        <td><?= $record['old_rate'] ? number_format($record['old_rate'], 6) : '-' ?></td>
                                        <td><?= number_format($record['new_rate'], 6) ?></td>
                                        <td>
                                            <?php if ($record['old_rate']): ?>
                                                <?php 
                                                $change = (($record['new_rate'] - $record['old_rate']) / $record['old_rate']) * 100;
                                                $changeClass = $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : 'text-muted');
                                                ?>
                                                <span class="<?= $changeClass ?>">
                                                    <?= $change > 0 ? '+' : '' ?><?= number_format($change, 2) ?>%
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Initial</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= Helpers::escape($record['updated_by_name'] ?? 'System') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <h3>Actions</h3>
                <div class="btn-group">
                    <a href="/currencies/<?= $currency['code'] ?>/edit" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Currency
                    </a>
                    
                    <?php if (!$currency['is_primary']): ?>
                        <form method="POST" action="/currencies/<?= $currency['code'] ?>/set-primary" style="display: inline;">
                            <?= Helpers::csrfField() ?>
                            <button type="submit" class="btn btn-warning" 
                                    onclick="return confirm('Set <?= $currency['code'] ?> as the primary currency?\n\nThis will affect all existing prices and calculations.')">
                                <i class="fas fa-star me-2"></i>Set as Primary
                            </button>
                        </form>
                        
                        <form method="POST" action="/currencies/<?= $currency['code'] ?>/delete" style="display: inline;">
                            <?= Helpers::csrfField() ?>
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Are you sure you want to delete currency <?= $currency['code'] ?>?\n\nThis action cannot be undone and will fail if the currency is being used.')">
                                <i class="fas fa-trash me-2"></i>Delete Currency
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.currency-symbol {
    font-family: monospace;
    font-weight: bold;
    font-size: 1.2em;
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.exchange-rate {
    font-family: monospace;
    font-weight: bold;
}

.conversion-examples {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.example-box {
    flex: 1;
    min-width: 300px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 10px;
}

.example-title {
    font-weight: bold;
    font-size: 1.1em;
    margin-bottom: 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.3);
    padding-bottom: 0.5rem;
}

.example-conversion {
    font-family: monospace;
    font-size: 1em;
    margin: 0.5rem 0;
    background: rgba(255,255,255,0.1);
    padding: 0.5rem;
    border-radius: 4px;
}

.badge {
    font-size: 0.8em;
}

@media (max-width: 768px) {
    .conversion-examples {
        flex-direction: column;
    }
    
    .example-box {
        min-width: 100%;
    }
}
</style>

<script>
// Add some interactivity for better UX
document.addEventListener('DOMContentLoaded', function() {
    // Highlight conversion examples on hover
    document.querySelectorAll('.example-conversion').forEach(function(element) {
        element.addEventListener('mouseenter', function() {
            this.style.background = 'rgba(255,255,255,0.2)';
        });
        
        element.addEventListener('mouseleave', function() {
            this.style.background = 'rgba(255,255,255,0.1)';
        });
    });
    
    // Add copy functionality to exchange rate
    const exchangeRate = document.querySelector('.exchange-rate');
    if (exchangeRate) {
        exchangeRate.style.cursor = 'pointer';
        exchangeRate.title = 'Click to copy exchange rate';
        
        exchangeRate.addEventListener('click', function() {
            const rate = this.textContent.replace(/,/g, '');
            navigator.clipboard.writeText(rate).then(function() {
                // Show brief success message
                const originalText = exchangeRate.textContent;
                exchangeRate.textContent = 'Copied!';
                exchangeRate.style.color = '#28a745';
                
                setTimeout(function() {
                    exchangeRate.textContent = originalText;
                    exchangeRate.style.color = '';
                }, 1000);
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
