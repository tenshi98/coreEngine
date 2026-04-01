<div class="col-md-<?php echo $FormCol; ?> field" id="div_<?php echo $nameID; ?>">
    <label class="form-label" for="<?php echo $name; ?>"><?php echo $placeholderIcon.$placeholder.$dataPopover; ?></label>
    <select class="form-select select2_Main"  name="<?php echo $name; ?>" id="<?php echo $nameID; ?>" aria-label="<?php echo $placeholder; ?>" <?php echo $requerido; ?>>; ?>
        <?php echo $FormOptions; ?>
    </select>
    <?php echo $dataInfo; ?>
</div>

<?php echo $dataRequire; ?>
