<?php
use App\Core\I18n;
use App\Core\Helpers;

$isEdit = isset($dropdown);
$title = ($isEdit ? 'Edit' : 'Create') . ' Dropdown Item - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">
            <?= $isEdit ? 'Edit' : 'Create' ?> Dropdown Item
        </h1>
    </div>
    
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/dropdowns/' . $dropdown['id'] : '/dropdowns' ?>" id="dropdownForm">
            <?= Helpers::csrfField() ?>
            
            <div class="form-group">
                <label for="category" class="form-label">Category *</label>
                <select name="category" id="category" class="form-control" required onchange="handleCategoryChange()">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $key => $label): ?>
                        <option value="<?= $key ?>" 
                                <?= ($isEdit && $dropdown['category'] == $key) || $parentCategory == $key ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="value" class="form-label">Value *</label>
                <input 
                    type="text" 
                    name="value" 
                    id="value" 
                    class="form-control" 
                    value="<?= $isEdit ? Helpers::escape($dropdown['value']) : Helpers::old('value') ?>"
                    required
                >
            </div>
            
            <!-- Parent dropdown for car models -->
            <div class="form-group" id="parent-group" style="display: none;">
                <label for="parent_id" class="form-label">Car Make</label>
                <select name="parent_id" id="parent_id" class="form-control">
                    <option value="">Select Car Make</option>
                    <?php if (!empty($parents)): ?>
                        <?php foreach ($parents as $parent): ?>
                            <option value="<?= $parent['id'] ?>" 
                                    <?= ($isEdit && $dropdown['parent_id'] == $parent['id']) ? 'selected' : '' ?>>
                                <?= Helpers::escape($parent['value']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <small class="form-text">Only required for car models</small>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Update' : 'Create' ?> Item
                </button>
                <a href="/dropdowns" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function handleCategoryChange() {
    const categorySelect = document.getElementById('category');
    const parentGroup = document.getElementById('parent-group');
    const parentSelect = document.getElementById('parent_id');
    
    if (categorySelect.value === 'car_model') {
        parentGroup.style.display = 'block';
        loadCarMakes();
    } else {
        parentGroup.style.display = 'none';
        parentSelect.value = '';
    }
}

function loadCarMakes() {
    const parentSelect = document.getElementById('parent_id');
    
    // Clear current options except first
    parentSelect.innerHTML = '<option value="">Select Car Make</option>';
    
    console.log('Loading car makes...');
    
    // Make AJAX request to get car makes
    fetch('/dropdowns/get-by-parent?category=car_make')
        .then(response => {
            console.log('Car makes response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Car makes data:', data);
            if (data.success && data.data) {
                data.data.forEach(make => {
                    const option = document.createElement('option');
                    option.value = make.id;
                    option.textContent = make.value;
                    parentSelect.appendChild(option);
                });
                console.log('Added', data.data.length, 'car makes');
            } else {
                console.error('No car makes found or API error:', data);
            }
        })
        .catch(error => {
            console.error('Error loading car makes:', error);
        });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    handleCategoryChange();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
