<div class="col-md-<?php echo htmlspecialchars($FormCol, ENT_QUOTES, 'UTF-8'); ?> field" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <label class="form-label" for="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo $placeholderIcon.htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8').$dataPopover; ?>
    </label>
    <?php echo $input_1; ?>
    <input type="text" name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $requerido.' '.$input_2; ?> >
    <?php echo $input_3; ?>
</div>

<script type="text/javascript">
    $("#<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>").clockpicker({
        placement: "<?php echo htmlspecialchars($x_pos, ENT_QUOTES, 'UTF-8'); ?>",
        align: "left",
        donetext: "Listo"
    });
</script>
