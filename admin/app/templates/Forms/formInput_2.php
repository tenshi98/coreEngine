<div class="col-<?php echo htmlspecialchars($formCol, ENT_QUOTES, 'UTF-8'); ?> field" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <label class="form-label" for="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo $placeholderIcon.htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8').$dataPopover; ?>
    </label>
    <?php echo $input_1; ?>
    <input type="<?php echo htmlspecialchars($InTipo, ENT_QUOTES, 'UTF-8'); ?>" name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>" class="form-control <?php echo htmlspecialchars($InputClass, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $requerido.' '.$jsValidation.' '.$input_2; ?> >
    <?php echo $input_3; ?>
    <?php echo $dataInfo; ?>
</div>
