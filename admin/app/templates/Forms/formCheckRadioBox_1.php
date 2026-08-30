<div class="row mb-3" id="div_<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
    <legend class="col-form-label col-sm-<?php echo htmlspecialchars($otrcol, ENT_QUOTES, 'UTF-8'); ?> pt-0">
        <?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8').$dataPopover; ?>
    </legend>
    <div class="col-sm-<?php echo htmlspecialchars($FormCol, ENT_QUOTES, 'UTF-8'); ?> field">
        <?php echo $formInput; ?>
        <?php echo $dataInfo; ?>
    </div>
</div>
