<div class="row mb-3" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <label class="col-sm-<?php echo htmlspecialchars($otrcol, ENT_QUOTES, 'UTF-8'); ?> col-form-label" for="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo $placeholderIcon.htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8').$dataPopover; ?>
    </label>
    <div class="col-sm-<?php echo htmlspecialchars($FormCol, ENT_QUOTES, 'UTF-8'); ?> field">
        <select class="form-select"  name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $requerido; ?>>
            <?php echo $FormOptions; ?>
        </select>
    </div>
    <?php echo $dataInfo; ?>
</div>
