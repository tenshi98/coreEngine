<div class="row mb-3" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <legend class="col-form-label col-sm-<?php echo htmlspecialchars($otrcol, ENT_QUOTES, 'UTF-8'); ?> pt-0"></legend>
    <div class="col-sm-<?php echo htmlspecialchars($FormCol, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="form-check checkbox-<?php echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'); ?>">
            <input class="form-check-input" type="checkbox" value="1" name="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>" onchange="acbtn_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>(this)">
            <label class="form-check-label" for="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($Text, ENT_QUOTES, 'UTF-8'); ?>
            </label>
        </div>
    </div>
</div>

<script>
    //se desactiva el boton f5
    window.onload = function () {
        disableSubmit();
    }
    //se desactiva el boton submit
    function disableSubmit() {
        document.getElementById("<?php echo htmlspecialchars($submitName, ENT_QUOTES, 'UTF-8'); ?>").disabled = true;
    }
    //si se esta de acuerdo se activa el boton submit
    function acbtn_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>(element) {
        if(element.checked) {
            document.getElementById("<?php echo htmlspecialchars($submitName, ENT_QUOTES, 'UTF-8'); ?>").disabled = false;
        }else  {
            document.getElementById("<?php echo htmlspecialchars($submitName, ENT_QUOTES, 'UTF-8'); ?>").disabled = true;
        }
    }
</script>
