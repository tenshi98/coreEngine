<?php echo $Alertas; ?>

<div class="form-group" id="div_<?php echo $name; ?>">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="margin-bottom:10px;">
        <label class="control-label col-xs-12 col-sm-4 col-md-4 col-lg-4"><?php echo $placeholder; ?></label>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <input id="kv-<?php echo $name; ?>" name="<?php echo $name.$ndat; ?>" type="file" multiple>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#kv-<?php echo $name; ?>").fileinput({
            'theme': 'explorer',
            language: "es",
            allowedFileExtensions: [<?php echo $type_files; ?>],
            maxFileCount: <?php echo $max_files; ?>,
            overwriteInitial: false,
            initialPreviewAsData: true,
            showUpload: false
        });
    });
</script>