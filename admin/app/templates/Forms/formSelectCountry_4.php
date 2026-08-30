<div class="col-md-<?php echo htmlspecialchars($FormCol, ENT_QUOTES, 'UTF-8'); ?> field" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <select class="form-select select2_Main"  name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $requerido; ?>>
        <?php echo $FormOptions; ?>
    </select>
    <?php echo $dataInfo; ?>
</div>

<?php echo $dataRequire; ?>
