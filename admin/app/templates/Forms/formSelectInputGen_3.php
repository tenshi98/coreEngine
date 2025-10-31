<div class="col-md-<?php echo $FormCol; ?> field" id="div_<?php echo $nameID; ?>">
    <label class="form-label"><?php echo $placeholder; ?></label>
    <select class="form-select select2_<?php echo $nameID; ?> <?php echo $classMain; ?>"  name="<?php echo $name; ?>" id="<?php echo $nameID; ?>" aria-label="<?php echo $placeholder; ?>" <?php echo $selectProperties; ?>>
        <?php echo $SelectOptions; ?>
    </select>
</div>