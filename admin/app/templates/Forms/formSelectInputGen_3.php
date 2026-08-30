<div class="col-md-<?php echo htmlspecialchars($FormCol, ENT_QUOTES, 'UTF-8'); ?> field" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <label class="form-label" for="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo $placeholderIcon.htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8').$dataPopover; ?>
    </label>
    <select class="form-select select2_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8').' '.htmlspecialchars($classMain, ENT_QUOTES, 'UTF-8'); ?>"  name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selectProperties; ?>>
        <?php echo $SelectOptions; ?>
    </select>
    <?php echo $dataInfo; ?>
</div>
