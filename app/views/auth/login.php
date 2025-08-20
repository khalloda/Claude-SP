<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('auth.login');
$showNav = false;

ob_start();
?>

<div class="login-container">
    <div class="login-card card">
        <div class="login-header">
            <h1 class="login-title"><?= I18n::t('app.name') ?></h1>
            <p class="login-subtitle"><?= I18n::t('app.welcome') ?></p>
        </div>
        
        <div class="card-body">
            <form method="POST" action="/login" id="loginForm">
                <?= Helpers::csrfField() ?>
                
                <div class="form-group">
                    <label for="email" class="form-label"><?= I18n::t('auth.email') ?></label>
                    <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        name="email" 
                        value="<?= Helpers::old('email', 'admin@example.com') ?>"
                        required
                        autocomplete="email"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label"><?= I18n::t('auth.password') ?></label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        value="Admin@123"
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" id="loginBtn">
                        <?= I18n::t('auth.login') ?>
                    </button>
                </div>
            </form>
            
            <!-- Debug info -->
            <div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px; font-size: 12px;">
                <strong>Pre-filled Test Credentials</strong><br>
                (You can change them if needed)
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <div class="lang-switcher" style="justify-content: center;">
                    <a href="?lang=en" class="lang-link <?= I18n::getLocale() === 'en' ? 'active' : '' ?>">English</a>
                    <a href="?lang=ar" class="lang-link <?= I18n::getLocale() === 'ar' ? 'active' : '' ?>">العربية</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Minimal JavaScript - just logging, no interference
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('loginBtn');
    
    console.log('=== LOGIN FORM DEBUG ===');
    console.log('Form found:', !!form);
    console.log('Form action:', form ? form.action : 'N/A');
    console.log('Form method:', form ? form.method : 'N/A');
    console.log('Button found:', !!btn);
    
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('🚀 FORM SUBMIT EVENT FIRED!');
            console.log('Event:', e);
            console.log('Form data:');
            console.log('- Email:', form.email.value);
            console.log('- Password:', form.password.value);
            console.log('- CSRF Token:', form._token.value);
            
            // Disable button to show loading state
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Logging in...';
            }
            
            // DO NOT PREVENT DEFAULT - let form submit naturally
            // return true; // explicitly allow
        });
    }
    
    if (btn) {
        btn.addEventListener('click', function(e) {
            console.log('🖱️ BUTTON CLICKED');
            console.log('Button type:', btn.type);
            console.log('Form valid:', form ? form.checkValidity() : 'No form');
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>