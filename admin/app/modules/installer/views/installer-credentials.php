<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<h5 class="wizardTittle text-danger">Credenciales MySQL</h5>
<div class="steps clearfix">
    <ul>
        <li class="done">     <a><div class="title"><span class="number"><i class="bi bi-check-lg"></i></span><span class="title_text">Bienvenida</span></div></a></li>
        <li class="current">  <a><div class="title"><span class="number">2</span><span class="title_text">Credenciales</span></div></a></li>
        <li class="disabled"> <a><div class="title"><span class="number">3</span><span class="title_text">BBDD</span></div></a></li>
        <li class="disabled"> <a><div class="title"><span class="number">4</span><span class="title_text">Sumario</span></div></a></li>
        <li class="disabled"> <a><div class="title"><span class="number">5</span><span class="title_text">Finalización</span></div></a></li>
    </ul>
</div>

<form id="FormCredentials" name="FormCredentials" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
    <div class="card-body">
        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8 col-xl-8 col-xxl-8 mx-auto">
            <div class="text-center">
                <i class="bx bx-server text-color-blue" style="font-size: 5rem;"></i>
            </div>
            <p class="text-center text-muted">Ingresa las credenciales de un usuario válido de MySQL, que ya exista en el servidor y que cuente con los permisos necesarios para crear bases de datos.</p>
            <h4 class="text-muted">
                <i class="bi bi-list-check text-color-blue"></i> Tips importantes:
            </h4>
            <ul class="list-unstyled">
                <li class="text-muted"><i class="bi bi-check text-color-green-dark"></i> El usuario debe estar previamente creado en el servidor MySQL.</li>
                <li class="text-muted"><i class="bi bi-check text-color-green-dark"></i> Debe tener privilegios suficientes (por ejemplo, CREATE o ALL PRIVILEGES).</li>
                <li class="text-muted"><i class="bi bi-check text-color-green-dark"></i> Verifica que el nombre de usuario y la contraseña sean correctos antes de continuar.</li>
                <li class="text-muted"><i class="bi bi-check text-color-green-dark"></i> Puerto y Charset son opcionales, en el caso de no ingresarlos se utiliza la configuración por defecto</li>
            </ul>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10 col-xl-10 col-xxl-10 mx-auto">
            <h4 class="text-muted"><i class="bi bi-list-check text-color-blue"></i> Administrador:</h4>
            <hr>
            <div class="row">
                <div class="col"><?php $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 2,'FormCol' => 12,'PlaceholderIcon' => 'bx bx-user',      'Placeholder' => 'Usuario',        'Name' => 'Admin_Usuario',   'Id' => 'Admin_Usuario',   'Value' => '', 'Required' => 2, 'DataInfo' => 'Usuario MySQL Administrador con permisos de creación de bases de datos']);?></div>
                <div class="col"><?php $data['Fnc_FormInputs']->formInput(['FormType' => 3,'FormAling' => 2,'FormCol' => 12,'PlaceholderIcon' => 'bx bx-key',       'Placeholder' => 'Contraseña',     'Name' => 'Admin_Password',  'Id' => 'Admin_Password',  'Value' => '', 'Required' => 2, 'DataInfo' => 'Contraseña del usuario MySQL']);?></div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10 col-xl-10 col-xxl-10 mx-auto">
            <h4 class="text-muted"><i class="bi bi-list-check text-color-blue"></i> Usuario de Producción:</h4>
            <hr>
            <div class="row">
                <div class="col"><?php $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 2,'FormCol' => 12,'PlaceholderIcon' => 'bx bx-user',      'Placeholder' => 'Usuario',        'Name' => 'Prod_Usuario',   'Id' => 'Prod_Usuario',   'Value' => '', 'Required' => 2, 'DataInfo' => 'Usuario MySQL Produccion con permisos de lectura de bases de datos']);?></div>
                <div class="col"><?php $data['Fnc_FormInputs']->formInput(['FormType' => 3,'FormAling' => 2,'FormCol' => 12,'PlaceholderIcon' => 'bx bx-key',       'Placeholder' => 'Contraseña',     'Name' => 'Prod_Password',  'Id' => 'Prod_Password',  'Value' => '', 'Required' => 2, 'DataInfo' => 'Contraseña del usuario MySQL']);?></div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10 col-xl-10 col-xxl-10 mx-auto">
            <h4 class="text-muted"><i class="bi bi-list-check text-color-blue"></i> Datos del Servidor:</h4>
            <hr>
            <div class="row">
                <div class="col">
                    <?php
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 2,'FormCol' => 12,'PlaceholderIcon' => 'bx bx-server',    'Placeholder' => 'Host de MySQL',  'Name' => 'Host',      'Id' => 'Host',      'Value' => '', 'Required' => 2, 'DataInfo' => 'Dirección del servidor MySQL (generalmente localhost)']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 2,'FormCol' => 12,'PlaceholderIcon' => 'bx bx-braille',   'Placeholder' => 'Charset',        'Name' => 'Charset',   'Id' => 'Charset',   'Value' => '', 'Required' => 1, 'DataInfo' => '(Opcional) Conjunto de caracteres a utilizar, generalmente utf8mb4']);
                    ?>
                </div>
                <div class="col">
                    <?php
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 2,'FormCol' => 12,'PlaceholderIcon' => 'bx bx-git-merge', 'Placeholder' => 'Puerto',         'Name' => 'Port',      'Id' => 'Port',      'Value' => '', 'Required' => 1, 'DataInfo' => '(Opcional) Puerto de conexión a utilizar, generalmente 3306']);
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <a href="<?php echo $BASE.'/install'?>" class="btn btn-danger"><i class="bi bi-arrow-left-circle"></i> Reiniciar</a>
        <?php if($data['ValidInstall'] === true ){ ?>
            <button type="submit" class="btn btn-primary"> Configurar Base de Datos <i class="bi bi-arrow-right-circle"></i></button>
        <?php }else{ ?>
            <a href="#" class="btn btn-primary disabled"> Configurar Base de Datos <i class="bi bi-arrow-right-circle"></i></a>
        <?php } ?>
    </div>
</form>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    /******************************************/
    $("#FormCredentials").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/install/credentials'; ?>';
            let Informacion = $("#FormCredentials").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#InstallerContent', fromData:'<?php echo $BASE.'/install/database'; ?>', refreshTbl:'false'}
                ],
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

</script>
