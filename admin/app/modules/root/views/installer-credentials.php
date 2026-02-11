<h5 class="card-header text-center">
    Credenciales MySQL
    <smal>Paso 1 de 4</smal>
</h5>
<form id="FormCredentials" name="FormCredentials" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="card-body">
        <div class="text-center">
            <i class="bi bi-database-gear text-color-blue" style="font-size: 5rem;"></i>
        </div>
        <?php
        //se dibujan los inputs
        echo '<p class="text-center text-muted">Ingresa las credenciales de un usuario de MySQL que tenga permisos para crear bases de datos.</p>';
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 2,'FormCol' => 12,'PlaceholderIcon' => 'bx bx-server',  'Placeholder' => 'Host de MySQL',  'Name' => 'Host',      'Id' => 'Host',         'Value' => '', 'Required' => 2, 'DataInfo' => 'Dirección del servidor MySQL (generalmente localhost)']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 2,'FormCol' => 12,'PlaceholderIcon' => 'bx bx-user',    'Placeholder' => 'Usuario',        'Name' => 'Usuario',   'Id' => 'Usuario',      'Value' => '', 'Required' => 2, 'DataInfo' => 'Usuario con permisos de creación de bases de datos']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 3,'FormAling' => 2,'FormCol' => 12,'PlaceholderIcon' => 'bx bx-key',     'Placeholder' => 'Contraseña',     'Name' => 'Password',  'Id' => 'IDInput_2_3',  'Value' => '', 'Required' => 2, 'DataInfo' => 'Contraseña del usuario MySQL']);
        ?>
    </div>
    <div class="card-footer text-end">
        <a href="<?php echo $BASE.'/install'?>" class="btn btn-danger"><i class="bi bi-arrow-left-circle"></i> Reiniciar</a>
        <?php if($data['ValidInstall'] === true ){ ?>
            <button type="submit" class="btn btn-primary" onclick="return validateCredentials();"> Siguiente <i class="bi bi-arrow-right-circle"></i></button>
        <?php }else{ ?>
            <a href="#" class="btn btn-primary disabled"> Siguiente <i class="bi bi-arrow-right-circle"></i></a>
        <?php } ?>
    </div>
</form>


