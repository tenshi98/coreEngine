<div class="row mb-3" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <label class="col-sm-<?php echo htmlspecialchars($otrcol, ENT_QUOTES, 'UTF-8'); ?> col-form-label" id="label_<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" for="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo $placeholderIcon.htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8').$dataPopover; ?>
    </label>
    <div class="col-sm-<?php echo htmlspecialchars($formCol, ENT_QUOTES, 'UTF-8').' '.htmlspecialchars($ExtraClass, ENT_QUOTES, 'UTF-8'); ?> field">
        <?php echo $input_1; ?>
        <input type="<?php echo htmlspecialchars($InTipo, ENT_QUOTES, 'UTF-8'); ?>" name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>" class="form-control <?php echo htmlspecialchars($InputClass, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $requerido.' '.$jsValidation.' '.$input_2; ?> >
        <?php echo $input_3; ?>
    </div>
    <?php echo $dataInfo; ?>
</div>
