<?php
// REPLACE your entire app/views/layouts/nav.php file with this:

use App\Core\I18n;
use App\Core\Auth;
use App\Core\Helpers;

// Get current URL without language parameter
$currentUrl = $_SERVER['REQUEST_URI'];
$urlParts = parse_url($currentUrl);
$basePath = $urlParts['path'] ?? '/dashboard';

// Build language switch URLs
$enUrl = $basePath . '?lang=en';
$arUrl = $basePath . '?lang=ar';
?>
<nav class="navbar">
    <div class="container">
        <div class="navbar-content">
            <a href="/dashboard" class="navbar-brand">
                <?= I18n::t('app.name') ?>
            </a>
            
            <ul class="navbar-nav">
                <li><a href="/dashboard" class="nav-link"><?= I18n::t('navigation.dashboard') ?></a></li>
                
                <!-- Masters Menu -->
                <li class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle">Masters</a>
                    <div class="dropdown-menu">
                        <a href="/clients" class="dropdown-item"><?= I18n::t('navigation.clients') ?></a>
                        <a href="/suppliers" class="dropdown-item"><?= I18n::t('navigation.suppliers') ?></a>
                        <a href="/warehouses" class="dropdown-item"><?= I18n::t('navigation.warehouses') ?></a>
                        <a href="/products" class="dropdown-item"><?= I18n::t('navigation.products') ?></a>
                        <a href="/dropdowns" class="dropdown-item">Dropdowns</a>
                    </div>
                </li>
                
                <!-- Sales Flow Menu -->
                <li class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle">Sales</a>
                    <div class="dropdown-menu">
                        <a href="/quotes" class="dropdown-item"><?= I18n::t('navigation.quotes') ?></a>
                        <a href="/salesorders" class="dropdown-item"><?= I18n::t('navigation.sales_orders') ?></a>
                        <a href="/invoices" class="dropdown-item"><?= I18n::t('navigation.invoices') ?></a>
                        <a href="/payments" class="dropdown-item"><?= I18n::t('navigation.payments') ?></a>
                    </div>
                </li>
                
                <!-- System Menu (Admin Only) -->
                <?php if (Auth::check() && (Auth::role() === 'admin' || Auth::hasRole('admin'))): ?>
                <li class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle">System</a>
                    <div class="dropdown-menu">
                        <a href="/currencies" class="dropdown-item">
                            <i class="fas fa-coins me-2"></i>Manage Currencies
                        </a>
                        <hr class="dropdown-divider">
                        <a href="/users" class="dropdown-item">
                            <i class="fas fa-users me-2"></i>User Management
                        </a>
                    </div>
                </li>
                <?php endif; ?>
                
                <!-- Language Switcher -->
                <li class="lang-switcher">
                    <a href="<?= $enUrl ?>" class="lang-link <?= I18n::getLocale() === 'en' ? 'active' : '' ?>">EN</a>
                    <a href="<?= $arUrl ?>" class="lang-link <?= I18n::getLocale() === 'ar' ? 'active' : '' ?>">AR</a>
                </li>
                
                <!-- Enhanced User Menu Dropdown -->
                <?php if (Auth::check()): ?>
                    <li class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle user-dropdown">
                            <i class="fas fa-user me-2"></i><?= Helpers::escape(Auth::user()['name']) ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="dropdown-header">Welcome back!</div>
                            
                            <!-- Admin-only Currency Management -->
                            <?php if (Auth::role() === 'admin' || Auth::hasRole('admin')): ?>
                                <a class="dropdown-item" href="/currencies">
                                    <i class="fas fa-coins me-2"></i>Manage Currencies
                                </a>
                                <div class="dropdown-divider"></div>
                            <?php endif; ?>
                            
                            <!-- User Preferences -->
                            <a class="dropdown-item" href="#" onclick="showCurrencySelector()">
                                <i class="fas fa-money-bill-wave me-2"></i>Change Currency
                            </a>
                            <div class="dropdown-divider"></div>
                            
                            <!-- Logout -->
                            <a class="dropdown-item text-danger" href="/logout">
                                <i class="fas fa-sign-out-alt me-2"></i><?= I18n::t('auth.logout') ?>
                            </a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Currency Selector Modal (Hidden by default) -->
<?php if (Auth::check()): ?>
<div id="currencyModal" class="currency-modal" style="display: none;">
    <div class="currency-modal-content">
        <div class="currency-modal-header">
            <h3>Select Currency</h3>
            <span class="currency-modal-close" onclick="hideCurrencySelector()">&times;</span>
        </div>
        <div class="currency-modal-body">
            <div id="currency-options">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Enhanced dropdown styles */
.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    min-width: 200px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    border: none;
    padding: 0.5rem 0;
    z-index: 1001;
}

.dropdown-menu-end {
    left: auto;
    right: 0;
}

.dropdown:hover .dropdown-menu {
    display: block;
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem;
    clear: both;
    font-weight: 400;
    color: #333;
    text-align: inherit;
    text-decoration: none;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    color: #333;
}

.dropdown-item.text-danger {
    color: #dc3545 !important;
}

.dropdown-item.text-danger:hover {
    background-color: #f8f9fa;
    color: #dc3545 !important;
}

.dropdown-header {
    display: block;
    padding: 0.5rem 1rem;
    margin-bottom: 0;
    font-size: 0.875rem;
    color: #6c757d;
    white-space: nowrap;
    font-weight: bold;
}

.dropdown-divider {
    height: 0;
    margin: 0.5rem 0;
    overflow: hidden;
    border-top: 1px solid #e9ecef;
}

.user-dropdown {
    display: flex;
    align-items: center;
}

/* Currency Modal Styles */
.currency-modal {
    display: none;
    position: fixed;
    z-index: 1002;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.currency-modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 0;
    border-radius: 10px;
    width: 400px;
    max-width: 90%;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.currency-modal-header {
    padding: 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.currency-modal-header h3 {
    margin: 0;
}

.currency-modal-close {
    color: white;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.currency-modal-close:hover {
    opacity: 0.7;
}

.currency-modal-body {
    padding: 1rem;
}

.currency-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    margin: 0.25rem 0;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.currency-option:hover {
    background-color: #f8f9fa;
}

.currency-option.active {
    background-color: #e7f3ff;
    border-color: #007bff;
}

.currency-name {
    font-weight: 500;
}

.currency-symbol {
    font-family: monospace;
    font-weight: bold;
    color: #666;
}

/* Icon support */
.fas, .far, .fab {
    margin-right: 0.5rem;
    width: 1rem;
    text-align: center;
}

/* Responsive dropdown */
@media (max-width: 768px) {
    .dropdown-menu {
        position: static;
        display: block;
        box-shadow: none;
        border: none;
        background: rgba(255,255,255,0.1);
        margin-top: 0.5rem;
    }
    
    .dropdown-item {
        color: white;
        padding: 0.25rem 1rem;
    }
    
    .dropdown-item:hover {
        background-color: rgba(255,255,255,0.1);
        color: white;
    }
    
    .dropdown-header {
        color: rgba(255,255,255,0.8);
    }
    
    .dropdown-divider {
        border-top-color: rgba(255,255,255,0.3);
    }
}
</style>

<script>
// Currency selector functionality
function showCurrencySelector() {
    const modal = document.getElementById('currencyModal');
    const optionsContainer = document.getElementById('currency-options');
    
    // Load available currencies
    if (window.currencyConfig && window.currencyConfig.currencies) {
        const currencies = window.currencyConfig.currencies;
        const currentCurrency = window.currencyConfig.defaultCurrency;
        
        let html = '';
        Object.keys(currencies).forEach(code => {
            const currency = currencies[code];
            const isActive = code === currentCurrency ? 'active' : '';
            
            html += `
                <div class="currency-option ${isActive}" onclick="selectCurrency('${code}')">
                    <div>
                        <div class="currency-name">${currency.name}</div>
                        <div class="currency-code">${code}</div>
                    </div>
                    <div class="currency-symbol">${currency.symbol}</div>
                </div>
            `;
        });
        
        optionsContainer.innerHTML = html;
    } else {
        optionsContainer.innerHTML = '<p>Loading currencies...</p>';
    }
    
    modal.style.display = 'block';
}

function hideCurrencySelector() {
    document.getElementById('currencyModal').style.display = 'none';
}

function selectCurrency(currencyCode) {
    // Update user's currency preference
    fetch('/currencies/set-user-currency', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `currency=${currencyCode}&csrf_token=${window.csrfToken || ''}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the page
            window.location.reload();
        } else {
            alert('Failed to change currency: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to change currency');
    });
    
    hideCurrencySelector();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('currencyModal');
    if (event.target === modal) {
        hideCurrencySelector();
    }
}
</script>
