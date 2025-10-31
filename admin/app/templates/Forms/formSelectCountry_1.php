<div class="row mb-3" id="div_<?php echo $nameID; ?>">
    <label class="col-sm-<?php echo $otrcol; ?> col-form-label"><?php echo $placeholder; ?></label>
    <div class="col-sm-<?php echo $FormCol; ?> field">
        <select class="form-select select2_Main"  name="<?php echo $name; ?>" id="<?php echo $nameID; ?>" aria-label="<?php echo $placeholder; ?>" <?php echo $requerido; ?>>; ?>
            <?php echo $FormOptions; ?>
        </select>
    </div>
</div>

<?php echo $dataRequire; ?>