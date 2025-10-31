<div class="form-check checkbox-<?php echo $tipo; ?> <?php echo $requerido; ?>">
    <input                          type="hidden"    value="1"          <?php echo $check; ?> name="<?php echo $name; ?>" >
    <input class="form-check-input" type="<?php echo $type; ?>" value="<?php echo $valor; ?>" <?php echo $check; ?> name="<?php echo $name; ?>" id="<?php echo $ID; ?>" <?php echo $requerido; ?>>
    <label class="form-check-label" for="<?php echo $ID; ?>"><?php echo $placeholder; ?></label>
</div>