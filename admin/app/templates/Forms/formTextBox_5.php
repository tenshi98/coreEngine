<div class="col-md-<?php echo htmlspecialchars($FormCol, ENT_QUOTES, 'UTF-8'); ?>" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="form-floating field">
        <textarea name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>"  class="form-control" style="height: 100px" <?php echo $requerido; ?>><?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></textarea>
        <label for="floatingName">
            <?php echo $placeholderIcon.htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>
        </label>
    </div>
</div>
