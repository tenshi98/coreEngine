<div class="col-md-<?php echo $FormCol; ?> field" id="div_<?php echo $nameID; ?>">
    <label class="form-label"><?php echo $placeholder; ?></label>
    <input type="text" name="<?php echo $name; ?>" id="<?php echo $nameID; ?>" class="form-control" value="<?php echo $valor; ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $requerido; ?>  onkeydown="return soloNumeroRealRacional(event)" style="text-align: center;" >
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