<h5 class="card-header text-center">
    Configuración de Base de Datos
    <smal>Paso 2 de 4</smal>
</h5>
<form id="FormDatabase" name="FormDatabase" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="card-body">
        <div class="text-center">
            <i class="bi bi-database-gear text-color-blue" style="font-size: 5rem;"></i>
        </div>
        <?php
        echo '<p class="text-center text-muted">Ingresa el nombre de la base de datos que deseas crear para el sistema.</p>';
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 2,'FormCol' => 12,'PlaceholderIcon' => 'bx bx-data', 'Placeholder' => 'Nombre de la Base de Datos',  'Name' => 'DBName',    'Id' => 'DBName',       'Value' => '', 'Required' => 2, 'DataInfo' => 'Solo letras, números y guiones bajos']);
        $data['Fnc_FormInputs']->formPostData(3, 4, 'bi bi-star', 0, '<h4>Nota:</h4><p>El nombre debe tener entre 3 y 64 caracteres y solo puede contener letras, números y guiones bajos.</p>');

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Host',     'Value' => $data['PostData']['Host'],     'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Usuario',  'Value' => $data['PostData']['Usuario'],  'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Password', 'Value' => $data['PostData']['Password'], 'Required' => 2]);

        ?>
    </div>
    <div class="card-footer text-end">
        <a href="<?php echo $BASE.'/install'?>" class="btn btn-danger"><i class="bi bi-arrow-left-circle"></i> Reiniciar</a>
        <?php if($data['ValidInstall'] === true ){ ?>
            <button type="submit" class="btn btn-primary" onclick="return validateDatabase();"> Siguiente <i class="bi bi-arrow-right-circle"></i></button>
        <?php }else{ ?>
            <a href="#" class="btn btn-primary disabled"> Siguiente <i class="bi bi-arrow-right-circle"></i></a>
        <?php } ?>
    </div>
</form>
