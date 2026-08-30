<div class="col-md-<?php echo htmlspecialchars($FormCol, ENT_QUOTES, 'UTF-8'); ?>" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="form-floating field">
        <select class="form-select"  name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $requerido; ?>>
            <?php echo $FormOptions; ?>
        </select>
        <label for="floatingName">
            <?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>
        </label>
    </div>
    <?php echo $dataInfo; ?>
</div>
