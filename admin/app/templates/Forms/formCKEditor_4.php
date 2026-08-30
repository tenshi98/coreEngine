<div class="col-md-<?php echo htmlspecialchars($FormCol, ENT_QUOTES, 'UTF-8'); ?> field" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <textarea name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>"  class="tinymce-editor" style="height: 100px" <?php echo $requerido; ?>><?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></textarea>
</div>
