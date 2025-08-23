<?php
// Create this file: app/views/currencies/form.php

use App\Core\I18n;
use App\Core\Helpers;

$isEdit = isset($currency);
$title = ($isEdit ? 'Edit' : 'Add New') . ' Currency - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title"><?= $isEdit ? 'Edit' : 'Add New' ?> Currency</h1>
            <a href="/currencies" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/currencies/' . $currency['code'] . '/update' : '/currencies/store' ?>">
            <?= Helpers::csrfField() ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="code">Currency Code *</label>
                        <input type="text" 
                               id="code" 
                               name="code" 
                               class="form-control" 
                               value="<?= Helpers::escape($currency['code'] ?? '') ?>"
                               <?= $isEdit ? 'readonly' : '' ?>
                               placeholder="e.g., USD, EUR, EGP"
                               maxlength="3"
                               style="text-transform: uppercase;"
                               required>
                        <small class="form-text text-muted">3-letter ISO currency code</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Currency Name *</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-control" 
                               value="<?= Helpers::escape($currency['name'] ?? '') ?>"
                               placeholder="e.g., US Dollar, Egyptian Pound"
                               maxlength="50"
                               required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="symbol">Currency Symbol *</label>
                        <input type="text" 
                               id="symbol" 
                               name="symbol" 
                               class="form-control" 
                               value="<?= Helpers::escape($currency['symbol'] ?? '') ?>"
                               placeholder="e.g., $, €, ج.م"
                               maxlength="10"
                               required>
                        <small class="form-text text-muted">Symbol displayed with amounts</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="decimal_places">Decimal Places</label>
                        <select id="decimal_places" name="decimal_places" class="form-control">
                            <?php for ($i = 0; $i <= 8; $i++): ?>
                                <option value="<?= $i ?>" <?= ($currency['decimal_places'] ?? 2) == $i ? 'selected' : '' ?>>
                                    <?= $i ?> <?= $i === 0 ? '(no decimals)' : ($i === 1 ? '(1 decimal)' : "($i decimals)") ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <small class="form-text text-muted">Number of decimal places for this currency</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exchange_rate">Exchange Rate (to Primary Currency) *</label>
                        <input type="number" 
                               id="exchange_rate" 
                               name="exchange_rate" 
                               class="form-control" 
                               value="<?= $currency['exchange_rate'] ?? '1.000000' ?>"
                               step="0.000001"
                               min="0.000001"
                               placeholder="1.000000"
                               required>
                        <small class="form-text text-muted">How many primary currency units = 1 unit of this currency</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Settings</label>
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1" 
                                   class="form-check-input"
                                   <?= ($currency['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label for="is_active" class="form-check-label">
                                Active Currency
                            </label>
                            <small class="form-text text-muted d-block">Inactive currencies won't appear in dropdowns</small>
                        </div>

                        <div class="form-check mt-2">
                            <input type="checkbox" 
                                   id="is_primary" 
                                   name="is_primary" 
                                   value="1" 
                                   class="form-check-input"
                                   <?= ($currency['is_primary'] ?? 0) ? 'checked' : '' ?>
                                   onchange="togglePrimarySettings(this)">
                            <label for="is_primary" class="form-check-label">
                                <strong>Primary Currency</strong>
                            </label>
                            <small class="form-text text-muted d-block">Primary currency has exchange rate of 1.00 and is the base for conversions</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exchange Rate Preview -->
            <div class="row" id="exchange-preview" style="display: none;">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5>Exchange Rate Preview:</h5>
                        <div id="preview-content">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i><?= $isEdit ? 'Update' : 'Create' ?> Currency
                </button>
                <a href="/currencies" class="btn btn-secondary">Cancel</a>
                
                <?php if ($isEdit && !($currency['is_primary'] ?? false)): ?>
                    <button type="button" class="btn btn-danger float-end" onclick="confirmDelete()">
                        <i class="fas fa-trash me-2"></i>Delete Currency
                    </button>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($isEdit && !($currency['is_primary'] ?? false)): ?>
            <!-- Hidden Delete Form -->
            <form id="delete-form" method="POST" action="/currencies/<?= $currency['code'] ?>/delete" style="display: none;">
                <?= Helpers::csrfField() ?>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.form-group {
    margin-bottom: 1.5rem;
}

.form-actions {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #dee2e6;
}

.alert-info {
    border-left: 4px solid #17a2b8;
}

#exchange-preview {
    margin-top: 1rem;
}

.currency-preview {
    font-family: monospace;
    font-size: 1.1em;
    background: #f8f9fa;
    padding: 0.5rem;
    border-radius: 4px;
    margin: 0.5rem 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-uppercase currency code
    document.getElementById('code').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Setup exchange rate preview
    setupExchangePreview();
    
    // Initialize primary currency settings
    togglePrimarySettings(document.getElementById('is_primary'));
});

function togglePrimarySettings(checkbox) {
    const exchangeRateField = document.getElementById('exchange_rate');
    
    if (checkbox.checked) {
        // Primary currency always has rate of 1.0
        exchangeRateField.value = '1.000000';
        exchangeRateField.readOnly = true;
        exchangeRateField.parentElement.querySelector('small').textContent = 'Primary currency always has exchange rate of 1.0';
    } else {
        exchangeRateField.readOnly = false;
        exchangeRateField.parentElement.querySelector('small').textContent = 'How many primary currency units = 1 unit of this currency';
    }
    
    updateExchangePreview();
}

function setupExchangePreview() {
    const fields = ['code', 'symbol', 'exchange_rate', 'decimal_places'];
    
    fields.forEach(function(fieldName) {
        const field = document.getElementById(fieldName);
        if (field) {
            field.addEventListener('input', updateExchangePreview);
        }
    });
    
    updateExchangePreview();
}

function updateExchangePreview() {
    const code = document.getElementById('code').value;
    const symbol = document.getElementById('symbol').value;
    const rate = parseFloat(document.getElementById('exchange_rate').value) || 1;
    const decimals = parseInt(document.getElementById('decimal_places').value) || 2;
    
    if (!code || !symbol) {
        document.getElementById('exchange-preview').style.display = 'none';
        return;
    }
    
    // Show preview
    document.getElementById('exchange-preview').style.display = 'block';
    
    const primaryAmount = 100; // Example amount
    const convertedAmount = primaryAmount / rate;
    
    const preview = `
        <div class="currency-preview">
            <strong>Format Examples:</strong><br>
            ${symbol}${convertedAmount.toFixed(decimals)} ${code} = Primary Currency ${primaryAmount.toFixed(2)}
        </div>
        <div class="currency-preview">
            <strong>Exchange Rate:</strong> 1 ${code} = ${rate.toFixed(6)} Primary Currency Units
        </div>
    `;
    
    document.getElementById('preview-content').innerHTML = preview;
}

function confirmDelete() {
    const currencyCode = '<?= $currency['code'] ?? '' ?>';
    
    if (confirm(`Are you sure you want to delete the currency ${currencyCode}?\n\nThis action cannot be undone and will fail if the currency is being used in transactions.`)) {
        document.getElementById('delete-form').submit();
    }
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const code = document.getElementById('code').value.trim();
    const name = document.getElementById('name').value.trim();
    const symbol = document.getElementById('symbol').value.trim();
    const rate = parseFloat(document.getElementById('exchange_rate').value);
    
    if (!code || code.length !== 3) {
        alert('Currency code must be exactly 3 characters');
        e.preventDefault();
        return;
    }
    
    if (!name) {
        alert('Currency name is required');
        e.preventDefault();
        return;
    }
    
    if (!symbol) {
        alert('Currency symbol is required');
        e.preventDefault();
        return;
    }
    
    if (isNaN(rate) || rate <= 0) {
        alert('Exchange rate must be a positive number');
        e.preventDefault();
        return;
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
