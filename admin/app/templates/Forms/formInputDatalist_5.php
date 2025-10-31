<div class="col-md-<?php echo $FormCol; ?>" id="div_<?php echo $nameID; ?>">
    <div class="form-floating field">
        <input type="text" name="<?php echo $name; ?>" id="<?php echo $nameID; ?>" list="<?php echo $name; ?>" class="form-control <?php echo $InputClass; ?>" value="<?php echo $value; ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $requerido; ?>>
        <label for="floatingName"><?php echo $placeholder; ?></label>
        <?php echo $dataList; ?>
    </div>
</div>