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
