<div class="col-md-<?php echo $FormCol; ?>" id="div_<?php echo $nameID; ?>">
    <div class="form-floating field">
        <select class="form-select select2_Main"  name="<?php echo $name; ?>" id="<?php echo $nameID; ?>" aria-label="<?php echo $placeholder; ?>" <?php echo $requerido; ?>>; ?>
            <?php echo $FormOptions; ?>
        </select>
        <label for="floatingName"><?php echo $placeholder; ?></label>
    </div>
</div>

<?php echo $dataRequire; ?>