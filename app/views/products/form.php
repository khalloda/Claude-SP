<?php
use App\Core\I18n;
use App\Core\Helpers;

$isEdit = isset($product);
$title = ($isEdit ? 'Edit' : 'Create') . ' Product - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">
            <?= $isEdit ? 'Edit' : 'Create' ?> Product
        </h1>
    </div>
    
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/products/' . $product['id'] : '/products' ?>" id="productForm">
            <?= Helpers::csrfField() ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="classification" class="form-label">Classification *</label>
                        <select name="classification" id="classification" class="form-control" required>
                            <option value="">Select Classification</option>
                            <?php foreach ($dropdowns['classifications'] as $classification): ?>
                                <option value="<?= Helpers::escape($classification['value']) ?>" 
                                        <?= ($isEdit && $product['classification'] == $classification['value']) ? 'selected' : '' ?>>
                                    <?= Helpers::escape($classification['value']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="code" class="form-label">Product Code</label>
                        <input 
                            type="text" 
                            name="code" 
                            id="code" 
                            class="form-control" 
                            value="<?= $isEdit ? Helpers::escape($product['code']) : '' ?>"
                            placeholder="Auto-generated if empty"
                            readonly
                        >
                        <small class="form-text">Code will be auto-generated based on classification</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Product Name *</label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            class="form-control" 
                            value="<?= $isEdit ? Helpers::escape($product['name']) : Helpers::old('name') ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="color" class="form-label">Color</label>
                        <select name="color" id="color" class="form-control">
                            <option value="">Select Color</option>
                            <?php foreach ($dropdowns['colors'] as $color): ?>
                                <option value="<?= Helpers::escape($color['value']) ?>" 
                                        <?= ($isEdit && $product['color'] == $color['value']) ? 'selected' : '' ?>>
                                    <?= Helpers::escape($color['value']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="brand" class="form-label">Brand</label>
                        <select name="brand" id="brand" class="form-control">
                            <option value="">Select Brand</option>
                            <?php foreach ($dropdowns['brands'] as $brand): ?>
                                <option value="<?= Helpers::escape($brand['value']) ?>" 
                                        <?= ($isEdit && $product['brand'] == $brand['value']) ? 'selected' : '' ?>>
                                    <?= Helpers::escape($brand['value']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="car_make" class="form-label">Car Make</label>
                        <select name="car_make" id="car_make" class="form-control" onchange="loadCarModels(this.value)">
                            <option value="">Select Car Make</option>
                            <?php foreach ($dropdowns['car_makes'] as $make): ?>
                                <option value="<?= Helpers::escape($make['value']) ?>" 
                                        data-id="<?= $make['id'] ?>"
                                        <?= ($isEdit && $product['car_make'] == $make['value']) ? 'selected' : '' ?>>
                                    <?= Helpers::escape($make['value']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="car_model" class="form-label">Car Model</label>
                        <select name="car_model" id="car_model" class="form-control">
                            <option value="">Select Car Model</option>
                            <?php if ($isEdit && $product['car_model']): ?>
                                <option value="<?= Helpers::escape($product['car_model']) ?>" selected>
                                    <?= Helpers::escape($product['car_model']) ?>
                                </option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="cost_price" class="form-label">Cost Price *</label>
                        <input 
                            type="number" 
                            name="cost_price" 
                            id="cost_price" 
                            class="form-control" 
                            step="0.01"
                            min="0"
                            value="<?= $isEdit ? $product['cost_price'] : Helpers::old('cost_price') ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="sale_price" class="form-label">Sale Price *</label>
                        <input 
                            type="number" 
                            name="sale_price" 
                            id="sale_price" 
                            class="form-control" 
                            step="0.01"
                            min="0"
                            value="<?= $isEdit ? $product['sale_price'] : Helpers::old('sale_price') ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="total_qty" class="form-label">Total Quantity</label>
                        <input 
                            type="number" 
                            name="total_qty" 
                            id="total_qty" 
                            class="form-control" 
                            step="0.01"
                            min="0"
                            value="<?= $isEdit ? $product['total_qty'] : '0' ?>"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Warehouse Locations -->
            <div class="warehouse-locations" style="margin-top: 2rem;">
                <h3>Warehouse Locations</h3>
                <div id="warehouse-locations-container">
                    <?php if ($isEdit && !empty($locations)): ?>
                        <?php foreach ($locations as $index => $location): ?>
                            <div class="warehouse-location-row" style="display: flex; gap: 1rem; margin-bottom: 1rem; align-items: end;">
                                <div style="flex: 1;">
                                    <label>Warehouse</label>
                                    <select name="warehouses[]" class="form-control">
                                        <?php foreach ($warehouses as $warehouse): ?>
                                            <option value="<?= $warehouse['id'] ?>" 
                                                    <?= $warehouse['id'] == $location['warehouse_id'] ? 'selected' : '' ?>>
                                                <?= Helpers::escape($warehouse['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <label>Quantity</label>
                                    <input type="number" name="quantities[]" class="form-control" step="0.01" 
                                           value="<?= $location['qty'] ?>">
                                </div>
                                <div style="flex: 1;">
                                    <label>Location</label>
                                    <input type="text" name="locations[]" class="form-control" 
                                           placeholder="A1-01" value="<?= Helpers::escape($location['location_label']) ?>">
                                </div>
                                <div>
                                    <button type="button" onclick="removeWarehouseRow(this)" class="btn btn-danger btn-sm">Remove</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" onclick="addWarehouseRow()" class="btn btn-secondary btn-sm">Add Warehouse Location</button>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Update' : 'Create' ?> Product
                </button>
                <a href="/products" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Load car models based on selected car make
function loadCarModels(makeValue) {
    const modelSelect = document.getElementById('car_model');
    const makeSelect = document.getElementById('car_make');
    const selectedOption = makeSelect.options[makeSelect.selectedIndex];
    const makeId = selectedOption.getAttribute('data-id');
    
    // Clear current options
    modelSelect.innerHTML = '<option value="">Select Car Model</option>';
    
    if (makeId) {
        // Make AJAX request to get models
        fetch(`/dropdowns/get-by-parent?parent_id=${makeId}&category=car_model`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(model => {
                        const option = document.createElement('option');
                        option.value = model.value;
                        option.textContent = model.value;
                        modelSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading car models:', error));
    }
}

// Add warehouse location row
function addWarehouseRow() {
    const container = document.getElementById('warehouse-locations-container');
    const warehouses = <?= json_encode($warehouses) ?>;
    
    const rowDiv = document.createElement('div');
    rowDiv.className = 'warehouse-location-row';
    rowDiv.style.cssText = 'display: flex; gap: 1rem; margin-bottom: 1rem; align-items: end;';
    
    let warehouseOptions = '<option value="">Select Warehouse</option>';
    warehouses.forEach(warehouse => {
        warehouseOptions += `<option value="${warehouse.id}">${warehouse.name}</option>`;
    });
    
    rowDiv.innerHTML = `
        <div style="flex: 1;">
            <label>Warehouse</label>
            <select name="warehouses[]" class="form-control">
                ${warehouseOptions}
            </select>
        </div>
        <div style="flex: 1;">
            <label>Quantity</label>
            <input type="number" name="quantities[]" class="form-control" step="0.01" min="0" value="0">
        </div>
        <div style="flex: 1;">
            <label>Location</label>
            <input type="text" name="locations[]" class="form-control" placeholder="A1-01">
        </div>
        <div>
            <button type="button" onclick="removeWarehouseRow(this)" class="btn btn-danger btn-sm">Remove</button>
        </div>
    `;
    
    container.appendChild(rowDiv);
}

// Remove warehouse location row
function removeWarehouseRow(button) {
    button.closest('.warehouse-location-row').remove();
}

// Auto-generate code based on classification
document.getElementById('classification').addEventListener('change', function() {
    const codeInput = document.getElementById('code');
    if (this.value && !codeInput.value) {
        // Auto-generate code preview (actual generation happens on server)
        const prefix = this.value.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '0');
        codeInput.placeholder = `Will be auto-generated (e.g., ${prefix}0001)`;
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
