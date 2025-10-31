<div class="row mb-3" id="div_<?php echo $nameID; ?>">
    <legend class="col-form-label col-sm-<?php echo $otrcol; ?> pt-0"></legend>
    <div class="col-sm-<?php echo $FormCol; ?>">
        <div class="form-check checkbox-<?php echo $tipo; ?>">
            <input class="form-check-input" type="checkbox" value="1" name="<?php echo $nameID; ?>" id="<?php echo $nameID; ?>" onchange="acbtn_<?php echo $nameID; ?>(this)">
            <label class="form-check-label" for="<?php echo $nameID; ?>"><?php echo $Text; ?></label>
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
        document.getElementById("<?php echo $submitName; ?>").disabled = true;
    }
    //si se esta de acuerdo se activa el boton submit
    function acbtn_<?php echo $nameID; ?>(element) {
        if(element.checked) {
            document.getElementById("<?php echo $submitName; ?>").disabled = false;
        }else  {
            document.getElementById("<?php echo $submitName; ?>").disabled = true;
        }
    }
</script>