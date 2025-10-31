<div class="row mb-3" id="div_<?php echo $nameID; ?>">
    <label class="col-sm-<?php echo $otrcol; ?> col-form-label" id="label_<?php echo $name; ?>"><?php echo $placeholder; ?></label>
    <div class="col-sm-<?php echo $formCol; ?> <?php echo $ExtraClass; ?> field">
        <?php echo $input_1; ?>
        <input type="<?php echo $InTipo; ?>" name="<?php echo $name; ?>" id="<?php echo $nameID; ?>" class="form-control <?php echo $InputClass; ?>" value="<?php echo $value; ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $requerido; ?> <?php echo $jsValidation; ?> <?php echo $input_2; ?> >
        <?php echo $input_3; ?>
    </div>
</div>