<h5 class="wizardTittle text-danger">Resumen de Instalación</h5>
<div class="steps clearfix">
    <ul>
        <li class="done">     <a><div class="title"><span class="number"><i class="bi bi-check-lg"></i></span><span class="title_text">Bienvenida</span></div></a></li>
        <li class="done">     <a><div class="title"><span class="number"><i class="bi bi-check-lg"></i></span><span class="title_text">Credenciales</span></div></a></li>
        <li class="done">     <a><div class="title"><span class="number"><i class="bi bi-check-lg"></i></span><span class="title_text">BBDD</span></div></a></li>
        <li class="current">  <a><div class="title"><span class="number">4</span><span class="title_text">Sumario</span></div></a></li>
        <li class="disabled"> <a><div class="title"><span class="number">5</span><span class="title_text">Finalización</span></div></a></li>
    </ul>
</div>

<form id="FormSummary" name="FormSummary" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="card-body">
        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8 col-xl-8 col-xxl-8 mx-auto">
            <div class="text-center">
                <i class="bi bi-database-gear text-color-blue" style="font-size: 5rem;"></i>
            </div>
            <p class="text-center text-muted">Revisa la configuración antes de ejecutar la instalación. Una vez iniciada, se crearán los recursos en el servidor.</p>
            <?php
            echo '<div class="row">';
                echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6">';
                    $data['Fnc_FormInputs']->formPostData(1, 5, 'exclamation-circle', 0, '<h4><i class="bx bx-server"></i> Host de MySQL</h4>  <p>'.$_SESSION['db_Host'].'</p>');
                echo '</div>';
                echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6">';
                    $data['Fnc_FormInputs']->formPostData(1, 5, 'exclamation-circle', 0, '<h4><i class="bx bx-user"></i> Usuario</h4>          <p>'.$_SESSION['db_Usuario'].'</p>');
                echo '</div>';
                echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6">';
                    $data['Fnc_FormInputs']->formPostData(1, 5, 'exclamation-circle', 0, '<h4><i class="bx bx-git-merge"></i> Puerto</h4>           <p>'.$_SESSION['db_Port'].'</p>');
                echo '</div>';
                echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6">';
                    $data['Fnc_FormInputs']->formPostData(1, 5, 'exclamation-circle', 0, '<h4><i class="bx bx-braille"></i> Conjunto de Caracteres</h4>   <p>'.$_SESSION['db_Charset'].'</p>');
                echo '</div>';
                echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6">';
                    $data['Fnc_FormInputs']->formPostData(1, 5, 'exclamation-circle', 0, '<h4><i class="bx bx-data"></i> Base de Datos</h4>    <p>'.$_SESSION['db_DBName'].'</p>');
                echo '</div>';
                echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6">';
                    $data['Fnc_FormInputs']->formPostData(1, 5, 'exclamation-circle', 0, '<h4><i class="bx bx-extension"></i> Archivo SQL</h4> <p>install.sql</p>');
                echo '</div>';
            echo '</div>';

            $DataInf = '<h4>Importante:</h4><p>Al ejecutar la instalación se realizarán las siguientes acciones:</p>
            <ul class="list-unstyled">
                <li><i class="bi bi-check text-color-green-dark"></i> Se creará la base de datos <strong>'.$_SESSION['db_DBName'].'</strong></li>
                <li><i class="bi bi-check text-color-green-dark"></i> Se ejecutará el archivo SQL de instalación</li>
                <li><i class="bi bi-check text-color-green-dark"></i> Se generará el archivo de configuración</li>
            </ul>';
            $data['Fnc_FormInputs']->formPostData(3, 4, 'bi bi-star', 0, $DataInf);

            //datos ocultos
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Host',     'Value' => $_SESSION['db_Host'],     'Required' => 2]);
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Usuario',  'Value' => $_SESSION['db_Usuario'],  'Required' => 2]);
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Password', 'Value' => $_SESSION['db_Password'], 'Required' => 2]);
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Port',     'Value' => $_SESSION['db_Port'],     'Required' => 2]);
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Charset',  'Value' => $_SESSION['db_Charset'],  'Required' => 2]);
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'DBName',   'Value' => $_SESSION['db_DBName'],   'Required' => 2]);

            ?>

        </div>
    </div>
    <div class="card-footer text-end">
        <a href="<?php echo $BASE.'/install'?>" class="btn btn-danger"><i class="bi bi-arrow-left-circle"></i> Reiniciar</a>
        <?php if($data['ValidInstall'] === true ){ ?>
            <button type="submit" class="btn btn-primary"> Instalar <i class="bi bi-arrow-right-circle"></i></button>
        <?php }else{ ?>
            <a href="#" class="btn btn-primary disabled"> Instalar <i class="bi bi-arrow-right-circle"></i></a>
        <?php } ?>
    </div>
</form>

<script>
    $("#FormSummary").submit(function(e) {
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
        //verifico el resultado
        if(validatorResult.valid===false){
            return !!validatorResult.valid;
        }else{
            e.preventDefault();
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Metodo      = 'POST';
            let Direccion   = '<?php echo $BASE.'/install/summary'; ?>';
            let Informacion = $("#FormSummary").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#InstallerContent', fromData:'<?php echo $BASE.'/install/finish'; ?>', refreshTbl:'false'}
                ],
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>

