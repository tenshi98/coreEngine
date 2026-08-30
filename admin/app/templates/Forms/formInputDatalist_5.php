<div class="col-md-<?php echo htmlspecialchars($FormCol, ENT_QUOTES, 'UTF-8'); ?>" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="form-floating field">
        <input type="text" name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>" list="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" class="form-control <?php echo htmlspecialchars($InputClass, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $requerido; ?>>
        <label for="floatingName">
            <?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>
        </label>
    </div>
    <?php echo $dataList; ?>
</div>
