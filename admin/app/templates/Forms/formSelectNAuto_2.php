<div class="col-<?php echo $FormCol; ?> field" id="div_<?php echo $nameID; ?>">
    <label class="form-label"><?php echo $placeholder; ?></label>
    <select class="form-select"  name="<?php echo $name; ?>" id="<?php echo $nameID; ?>" aria-label="<?php echo $placeholder; ?>" <?php echo $requerido; ?>>
        <?php echo $FormOptions; ?>
    </select>
</div>