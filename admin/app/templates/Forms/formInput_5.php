<div class="col-md-<?php echo htmlspecialchars($formCol, ENT_QUOTES, 'UTF-8'); ?>" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="form-floating field">
        <input type="<?php echo htmlspecialchars($InTipo, ENT_QUOTES, 'UTF-8'); ?>" name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>" class="form-control <?php echo htmlspecialchars($InputClass, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $requerido.' '.$jsValidation; ?> >
        <label for="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>
        </label>
    </div>
    <?php echo $dataInfo; ?>
</div>
