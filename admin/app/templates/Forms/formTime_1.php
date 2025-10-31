<div class="row mb-3" id="div_<?php echo $nameID; ?>">
    <label class="col-sm-<?php echo $otrcol; ?> col-form-label" id="label_<?php echo $name; ?>"><?php echo $placeholder; ?></label>
    <div class="col-sm-<?php echo $FormCol; ?> field">
        <?php echo $input_1; ?>
        <input type="text" name="<?php echo $name; ?>" id="<?php echo $nameID; ?>" class="form-control" value="<?php echo $value; ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $requerido; ?> <?php echo $input_2; ?> >
        <?php echo $input_3; ?>
    </div>
</div>

<script type="text/javascript">
    $("#<?php echo $nameID; ?>").clockpicker({
        placement: "<?php echo $x_pos; ?>",
        align: "left",
        donetext: "Listo"
    });
</script>