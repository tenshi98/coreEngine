<section class="section">
    <div class="row">
        <?php
        require_once 'coreFormularios-form_1.php';
        require_once 'coreFormularios-form_2.php';
        ?>
    </div>
    <div class="row">
        <?php
        require_once 'coreFormularios-form_3.php';
        require_once 'coreFormularios-form_4.php';
        ?>
    </div>
    <div class="row">
        <?php
        //require_once 'coreFormularios-form_5.php';
        require_once 'coreFormularios-form_6.php';
        ?>
    </div>
</section>

<script>
    document.getElementById("form1").onsubmit = function(e){
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
		return !!validatorResult.valid;
	}
    document.getElementById("form2").onsubmit = function(e){
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
		return !!validatorResult.valid;
	}
    document.getElementById("form3").onsubmit = function(e){
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
		return !!validatorResult.valid;
	}
    document.getElementById("form4").onsubmit = function(e){
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
		return !!validatorResult.valid;
	}
    /*document.getElementById("form5").onsubmit = function(e){
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
		return !!validatorResult.valid;
	}*/
    document.getElementById("form6").onsubmit = function(e){
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
		return !!validatorResult.valid;
	}


</script>
