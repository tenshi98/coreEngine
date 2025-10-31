<div class="row mb-3" id="div_<?php echo $nameID; ?>">
    <label class="col-sm-<?php echo $otrcol; ?> col-form-label" id="label_<?php echo $name; ?>"><?php echo $placeholder; ?></label>
    <div class="col-sm-<?php echo $FormCol; ?> field">
        <input type="text" name="<?php echo $name; ?>" id="<?php echo $nameID; ?>" class="form-control" value="<?php echo $valor; ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $requerido; ?>  onkeydown="return soloNumeroRealRacional(event)" style="text-align: center;" >
    </div>
</div>

<script>
    //se inicializa el plugin
    $("#<?php echo $nameID; ?>").TouchSpin({
        min: <?php echo $min; ?>,
        max: <?php echo $max; ?>,
        step: <?php echo $step; ?>,
        decimals: <?php echo $ndecimal; ?>,
        boostat: 5,
        maxboostedstep: 10
    });
</script>