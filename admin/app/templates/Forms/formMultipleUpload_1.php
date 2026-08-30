<?php echo $Alertas; ?>

<div class="form-group" id="div_<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="margin-bottom:10px;">
        <label class="control-label col-xs-12 col-sm-4 col-md-4 col-lg-4" for="<?php echo htmlspecialchars($nameID, ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>
        </label>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <input id="kv-<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" name="<?php echo htmlspecialchars($name.$ndat, ENT_QUOTES, 'UTF-8'); ?>" type="file" multiple>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#kv-<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>").fileinput({
            'theme': 'explorer',
            language: "es",
            allowedFileExtensions: [<?php echo htmlspecialchars($type_files, ENT_QUOTES, 'UTF-8'); ?>],
            maxFileCount: <?php echo htmlspecialchars($max_files, ENT_QUOTES, 'UTF-8'); ?>,
            overwriteInitial: false,
            initialPreviewAsData: true,
            showUpload: false
        });
    });
</script>
