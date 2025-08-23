/**
 * Multi-Currency JavaScript Enhancement
 * Handles dynamic currency formatting and conversion in forms
 */

class MultiCurrency {
    constructor(config = {}) {
        this.currencies = config.currencies || {};
        this.defaultCurrency = config.defaultCurrency || 'EGP';
        this.currentCurrency = this.defaultCurrency;
        this.exchangeRates = {};
        
        this.init();
    }

    init() {
        // Load exchange rates from currencies config
        Object.keys(this.currencies).forEach(code => {
            this.exchangeRates[code] = this.currencies[code].exchange_rate;
        });

        // Initialize currency selectors
        this.initCurrencySelectors();
        
        // Initialize currency switcher if present
        this.initCurrencySwitcher();
        
        // Initialize form currency handling
        this.initFormCurrencyHandling();
        
        // Initialize real-time conversion displays
        this.initConversionDisplays();
    }

    /**
     * Initialize currency dropdown selectors in forms
     */
    initCurrencySelectors() {
        const selectors = document.querySelectorAll('.currency-selector');
        
        selectors.forEach(selector => {
            selector.addEventListener('change', (e) => {
                const newCurrency = e.target.value;
                this.handleCurrencyChange(newCurrency, e.target);
            });
        });
    }

    /**
     * Initialize global currency switcher
     */
    initCurrencySwitcher() {
        const switcher = document.getElementById('currency-switcher');
        
        if (switcher) {
            switcher.addEventListener('change', (e) => {
                this.setUserCurrency(e.target.value);
            });
        }
    }

    /**
     * Initialize form currency handling
     */
    initFormCurrencyHandling() {
        // Re-bind formatCurrency functions to use dynamic currency
        if (typeof window.formatCurrency === 'function') {
            window.originalFormatCurrency = window.formatCurrency;
        }
        
        window.formatCurrency = (amount, currency = null) => {
            return this.formatCurrency(amount, currency);
        };

        // Initialize currency-aware price inputs
        const priceInputs = document.querySelectorAll('input[name*="price"], .price-input');
        priceInputs.forEach(input => {
            this.initPriceInput(input);
        });
    }

    /**
     * Initialize conversion displays
     */
    initConversionDisplays() {
        const conversionElements = document.querySelectorAll('[data-show-conversion]');
        
        conversionElements.forEach(element => {
            const fromCurrency = element.dataset.fromCurrency || this.currentCurrency;
            const toCurrency = element.dataset.toCurrency || this.getSecondaryCurrency();
            const amount = parseFloat(element.dataset.amount) || 0;
            
            if (amount > 0 && fromCurrency !== toCurrency) {
                this.showConversion(element, amount, fromCurrency, toCurrency);
            }
        });
    }

    /**
     * Format currency with proper symbol and decimals
     */
    formatCurrency(amount, currency = null) {
        currency = currency || this.currentCurrency;
        
        if (!this.currencies[currency]) {
            return amount.toFixed(2) + ' ' + currency;
        }

        const config = this.currencies[currency];
        const decimals = config.decimal_places || 2;
        const formattedAmount = amount.toFixed(decimals);
        
        return formattedAmount + ' ' + config.symbol;
    }

    /**
     * Convert amount between currencies
     */
    convertCurrency(amount, fromCurrency, toCurrency) {
        if (amount <= 0 || fromCurrency === toCurrency) {
            return amount;
        }

        const fromRate = this.exchangeRates[fromCurrency] || 1;
        const toRate = this.exchangeRates[toCurrency] || 1;
        
        // Convert to primary currency first, then to target currency
        return (amount / fromRate) * toRate;
    }

    /**
     * Handle currency change in forms
     */
    handleCurrencyChange(newCurrency, selectorElement) {
        const form = selectorElement.closest('form');
        if (!form) return;

        // Update current currency for this form
        const oldCurrency = this.currentCurrency;
        this.currentCurrency = newCurrency;

        // Update currency symbol displays
        this.updateCurrencySymbols(form, newCurrency);
        
        // Recalculate totals if calculation function exists
        if (typeof calculateTotals === 'function') {
            calculateTotals();
        }

        // Show conversion rates if different from primary
        this.showCurrencyConversion(form, newCurrency);
        
        // Update exchange rate field if present
        this.updateExchangeRateField(form, newCurrency);
    }

    /**
     * Update currency symbols in form
     */
    updateCurrencySymbols(form, currency) {
        const symbolElements = form.querySelectorAll('.currency-symbol');
        const newSymbol = this.getCurrencySymbol(currency);
        
        symbolElements.forEach(element => {
            element.textContent = newSymbol;
        });
    }

    /**
     * Show currency conversion information
     */
    showCurrencyConversion(form, currency) {
        const conversionInfo = form.querySelector('.currency-conversion-info');
        
        if (conversionInfo && currency !== this.getPrimaryCurrency()) {
            const primaryCurrency = this.getPrimaryCurrency();
            const rate = this.getExchangeRate(currency, primaryCurrency);
            
            conversionInfo.innerHTML = `
                <small class="text-info">
                    <i class="fas fa-exchange-alt"></i>
                    1 ${currency} = ${rate.toFixed(6)} ${primaryCurrency}
                </small>
            `;
            conversionInfo.style.display = 'block';
        } else if (conversionInfo) {
            conversionInfo.style.display = 'none';
        }
    }

    /**
     * Update exchange rate hidden field
     */
    updateExchangeRateField(form, currency) {
        const rateField = form.querySelector('input[name="exchange_rate"]');
        if (rateField) {
            const rate = this.exchangeRates[currency] || 1;
            rateField.value = rate.toFixed(6);
        }
    }

    /**
     * Initialize price input with currency formatting
     */
    initPriceInput(input) {
        input.addEventListener('blur', (e) => {
            const value = parseFloat(e.target.value) || 0;
            if (value > 0) {
                const currency = this.getCurrentFormCurrency(input);
                e.target.setAttribute('title', this.formatCurrency(value, currency));
            }
        });
    }

    /**
     * Show conversion in element
     */
    showConversion(element, amount, fromCurrency, toCurrency) {
        const convertedAmount = this.convertCurrency(amount, fromCurrency, toCurrency);
        const conversionText = `(${this.formatCurrency(convertedAmount, toCurrency)})`;
        
        let conversionSpan = element.querySelector('.currency-conversion');
        if (!conversionSpan) {
            conversionSpan = document.createElement('small');
            conversionSpan.className = 'currency-conversion text-muted ml-1';
            element.appendChild(conversionSpan);
        }
        
        conversionSpan.textContent = conversionText;
    }

    /**
     * Set user currency preference
     */
    setUserCurrency(currency) {
        fetch('/currencies/set-user-currency', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                currency: currency,
                _token: this.getCsrfToken()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.currentCurrency = currency;
                this.refreshPage();
            } else {
                console.error('Error setting currency:', data.message);
            }
        })
        .catch(error => {
            console.error('Error setting currency:', error);
        });
    }

    /**
     * Get current form currency
     */
    getCurrentFormCurrency(element) {
        const form = element.closest('form');
        const currencySelector = form ? form.querySelector('.currency-selector') : null;
        return currencySelector ? currencySelector.value : this.currentCurrency;
    }

    /**
     * Get currency symbol
     */
    getCurrencySymbol(currency) {
        return this.currencies[currency] ? this.currencies[currency].symbol : currency;
    }

    /**
     * Get primary currency code
     */
    getPrimaryCurrency() {
        for (const code in this.currencies) {
            if (this.currencies[code].is_primary) {
                return code;
            }
        }
        return 'EGP';
    }

    /**
     * Get secondary currency (first non-primary active currency)
     */
    getSecondaryCurrency() {
        const primary = this.getPrimaryCurrency();
        for (const code in this.currencies) {
            if (code !== primary) {
                return code;
            }
        }
        return 'USD';
    }

    /**
     * Get exchange rate between currencies
     */
    getExchangeRate(fromCurrency, toCurrency) {
        if (fromCurrency === toCurrency) return 1;
        
        const fromRate = this.exchangeRates[fromCurrency] || 1;
        const toRate = this.exchangeRates[toCurrency] || 1;
        
        return fromRate / toRate;
    }

    /**
     * Get CSRF token
     */
    getCsrfToken() {
        const tokenInput = document.querySelector('input[name="_token"]');
        return tokenInput ? tokenInput.value : '';
    }

    /**
     * Refresh page to apply currency changes
     */
    refreshPage() {
        window.location.reload();
    }

    /**
     * Update exchange rates (for admin)
     */
    updateExchangeRates(rates) {
        Object.assign(this.exchangeRates, rates);
        
        // Update currencies config
        Object.keys(rates).forEach(code => {
            if (this.currencies[code]) {
                this.currencies[code].exchange_rate = rates[code];
            }
        });

        // Refresh conversion displays
        this.initConversionDisplays();
    }

    /**
     * Add currency to dropdown
     */
    addCurrencyToDropdowns(currencyData) {
        const selectors = document.querySelectorAll('.currency-selector');
        
        selectors.forEach(selector => {
            const option = document.createElement('option');
            option.value = currencyData.code;
            option.textContent = `${currencyData.name} (${currencyData.symbol})`;
            option.dataset.symbol = currencyData.symbol;
            option.dataset.rate = currencyData.exchange_rate;
            
            selector.appendChild(option);
        });

        // Add to internal config
        this.currencies[currencyData.code] = currencyData;
        this.exchangeRates[currencyData.code] = currencyData.exchange_rate;
    }

    /**
     * Remove currency from dropdowns
     */
    removeCurrencyFromDropdowns(currencyCode) {
        const selectors = document.querySelectorAll('.currency-selector');
        
        selectors.forEach(selector => {
            const option = selector.querySelector(`option[value="${currencyCode}"]`);
            if (option) {
                option.remove();
            }
        });

        // Remove from internal config
        delete this.currencies[currencyCode];
        delete this.exchangeRates[currencyCode];
    }

    /**
     * Create currency selector HTML
     */
    createCurrencySelector(name = 'currency_code', selectedCurrency = null, cssClass = 'currency-selector') {
        selectedCurrency = selectedCurrency || this.currentCurrency;
        
        let html = `<select name="${name}" class="form-control ${cssClass}">`;
        
        Object.keys(this.currencies).forEach(code => {
            const currency = this.currencies[code];
            const selected = code === selectedCurrency ? 'selected' : '';
            const primaryTag = currency.is_primary ? ' (Primary)' : '';
            
            html += `<option value="${code}" data-symbol="${currency.symbol}" data-rate="${currency.exchange_rate}" ${selected}>
                ${currency.name} (${currency.symbol})${primaryTag}
            </option>`;
        });
        
        html += '</select>';
        return html;
    }
}

// Global currency management
window.MultiCurrency = MultiCurrency;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get currency configuration from PHP (should be set by Helpers::getCurrencyJavaScriptConfig())
    if (typeof window.currencyConfig !== 'undefined') {
        window.currencyManager = new MultiCurrency({
            currencies: window.currencyConfig.currencies || {},
            defaultCurrency: window.currencyConfig.defaultCurrency || 'EGP'
        });
    } else {
        // Fallback configuration
        window.currencyManager = new MultiCurrency({
            currencies: {
                'EGP': {
                    name: 'Egyptian Pound',
                    symbol: 'ج.م',
                    decimal_places: 2,
                    exchange_rate: 1.000000,
                    is_primary: true
                },
                'USD': {
                    name: 'US Dollar',
                    symbol: '$',
                    decimal_places: 2,
                    exchange_rate: 0.032258,
                    is_primary: false
                }
            },
            defaultCurrency: 'EGP'
        });
    }
});

// Utility functions for backward compatibility
function formatCurrency(amount, currency = null) {
    if (window.currencyManager) {
        return window.currencyManager.formatCurrency(amount, currency);
    }
    
    // Fallback
    currency = currency || 'EGP';
    const symbol = currency === 'EGP' ? 'ج.م' : '$';
    return amount.toFixed(2) + ' ' + symbol;
}

function convertCurrency(amount, from, to) {
    if (window.currencyManager) {
        return window.currencyManager.convertCurrency(amount, from, to);
    }
    
    // Fallback - no conversion
    return amount;
}

function getCurrencySymbol(currency) {
    if (window.currencyManager) {
        return window.currencyManager.getCurrencySymbol(currency);
    }
    
    // Fallback
    return currency === 'EGP' ? 'ج.م' : '$';
}
