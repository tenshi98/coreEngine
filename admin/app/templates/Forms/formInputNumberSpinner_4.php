<div class="col-md-<?php echo htmlspecialchars($FormCol, ENT_QUOTES, 'UTF-8'); ?> field" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="text" name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" value="<?php echo htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $requerido; ?>  onkeydown="return soloNumeroRealRacional(event)" style="text-align: center;">
</div>

<script>
    //se inicializa el plugin
    $("#<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>").TouchSpin({
        min: <?php echo htmlspecialchars($min, ENT_QUOTES, 'UTF-8'); ?>,
        max: <?php echo htmlspecialchars($max, ENT_QUOTES, 'UTF-8'); ?>,
        step: <?php echo htmlspecialchars($step, ENT_QUOTES, 'UTF-8'); ?>,
        decimals: <?php echo htmlspecialchars($ndecimal, ENT_QUOTES, 'UTF-8'); ?>,
        boostat: 5,
        maxboostedstep: 10
    });
</script>
