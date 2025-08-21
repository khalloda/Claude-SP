<?php
use App\Core\I18n;
use App\Core\Helpers;

$isEdit = isset($client);
$title = ($isEdit ? I18n::t('actions.edit') : I18n::t('actions.create')) . ' ' . I18n::t('navigation.clients') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">
            <?= $isEdit ? I18n::t('actions.edit') : I18n::t('actions.create') ?> <?= I18n::t('navigation.clients') ?>
        </h1>
    </div>
    
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/clients/' . $client['id'] : '/clients' ?>">
            <?= Helpers::csrfField() ?>
            
            <div class="form-group">
                <label for="type" class="form-label"><?= I18n::t('common.type') ?> *</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="">Select Type</option>
                    <option value="company" <?= ($isEdit && $client['type'] == 'company') ? 'selected' : '' ?>>Company</option>
                    <option value="individual" <?= ($isEdit && $client['type'] == 'individual') ? 'selected' : '' ?>>Individual</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="name" class="form-label"><?= I18n::t('common.name') ?> *</label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    class="form-control" 
                    value="<?= $isEdit ? Helpers::escape($client['name']) : Helpers::old('name') ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label"><?= I18n::t('common.email') ?></label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    class="form-control" 
                    value="<?= $isEdit ? Helpers::escape($client['email']) : Helpers::old('email') ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="phone" class="form-label"><?= I18n::t('common.phone') ?></label>
                <input 
                    type="text" 
                    name="phone" 
                    id="phone" 
                    class="form-control" 
                    value="<?= $isEdit ? Helpers::escape($client['phone']) : Helpers::old('phone') ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="address" class="form-label"><?= I18n::t('common.address') ?></label>
                <textarea 
                    name="address" 
                    id="address" 
                    class="form-control" 
                    rows="3"
                ><?= $isEdit ? Helpers::escape($client['address']) : Helpers::old('address') ?></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? I18n::t('actions.update') : I18n::t('actions.create') ?>
                </button>
                <a href="/clients" class="btn btn-secondary"><?= I18n::t('actions.cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
