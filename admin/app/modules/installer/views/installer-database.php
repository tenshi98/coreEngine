<h5 class="wizardTittle text-danger">Configuración de Base de Datos</h5>
<div class="steps clearfix">
    <ul>
        <li class="done">     <a><div class="title"><span class="number"><i class="bi bi-check-lg"></i></span><span class="title_text">Bienvenida</span></div></a></li>
        <li class="done">     <a><div class="title"><span class="number"><i class="bi bi-check-lg"></i></span><span class="title_text">Credenciales</span></div></a></li>
        <li class="current">  <a><div class="title"><span class="number">3</span><span class="title_text">BBDD</span></div></a></li>
        <li class="disabled"> <a><div class="title"><span class="number">4</span><span class="title_text">Sumario</span></div></a></li>
        <li class="disabled"> <a><div class="title"><span class="number">5</span><span class="title_text">Finalización</span></div></a></li>
    </ul>
</div>

<form id="FormDatabase" name="FormDatabase" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="card-body">
        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8 col-xl-8 col-xxl-8 mx-auto">
            <div class="text-center">
                <i class="bi bi-database-gear text-color-blue" style="font-size: 5rem;"></i>
            </div>
            <p class="text-center text-muted">Ingresa el nombre de la base de datos que deseas crear para el sistema.</p>
            <?php
            //se dibujan los inputs
            $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 2,'FormCol' => 12,'PlaceholderIcon' => 'bx bx-data', 'Placeholder' => 'Nombre de la Base de Datos',  'Name' => 'DBName',    'Id' => 'DBName',       'Value' => '', 'Required' => 2, 'DataInfo' => 'Solo letras, números y guiones bajos']);
            $data['Fnc_FormInputs']->formPostData(3, 4, 'bi bi-star', 0, '<h4>Nota:</h4><p>El nombre debe tener entre 3 y 64 caracteres y solo puede contener letras, números y guiones bajos.</p>');

            //datos ocultos
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Host',     'Value' => $_SESSION['db_Host'],     'Required' => 2]);
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Usuario',  'Value' => $_SESSION['db_Usuario'],  'Required' => 2]);
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Password', 'Value' => $_SESSION['db_Password'], 'Required' => 2]);
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Port',     'Value' => $_SESSION['db_Port'],     'Required' => 2]);
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Charset',  'Value' => $_SESSION['db_Charset'],  'Required' => 2]);

            ?>
        </div>
    </div>
    <div class="card-footer text-end">
        <a href="<?php echo $BASE.'/install'?>" class="btn btn-danger"><i class="bi bi-arrow-left-circle"></i> Reiniciar</a>
        <?php if($data['ValidInstall'] === true ){ ?>
            <button type="submit" class="btn btn-primary"> Sumario <i class="bi bi-arrow-right-circle"></i></button>
        <?php }else{ ?>
            <a href="#" class="btn btn-primary disabled"> Sumario <i class="bi bi-arrow-right-circle"></i></a>
        <?php } ?>
    </div>
</form>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    /******************************************/
    $("#FormDatabase").submit(function(e) {
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
        //verifico el resultado
        if(validatorResult.valid===false){
            return !!validatorResult.valid;
        }else{
            // Si ya se está ejecutando, salimos
            if (ejecutandoForm.valor) return;
            //Cambio los valores
            ejecutandoForm.valor = true;
            //Ejecucion normal
            e.preventDefault();
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Metodo      = 'POST';
            let Direccion   = '<?php echo $BASE.'/install/database'; ?>';
            let Informacion = $("#FormDatabase").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#InstallerContent', fromData:'<?php echo $BASE.'/install/summary'; ?>', refreshTbl:'false'}
                ],
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

</script>
