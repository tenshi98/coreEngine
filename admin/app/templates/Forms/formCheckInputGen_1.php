<div class="form-check checkbox-<?php echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'); ?> <?php echo $requerido_div; ?>">
    <input                          type="hidden"                                 value="1"                                       <?php echo htmlspecialchars($check, ENT_QUOTES, 'UTF-8'); ?> name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
    <input class="form-check-input" type="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>" <?php echo htmlspecialchars($check, ENT_QUOTES, 'UTF-8'); ?> name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($ID, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $requerido; ?>>
    <label class="form-check-label" for="<?php echo htmlspecialchars($ID, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?></label>
</div>
