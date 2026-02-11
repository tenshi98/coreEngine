<h5 class="card-header text-center">
    Resumen de Instalación
    <smal>Paso 3 de 4</smal>
</h5>
<form id="FormSummary" name="FormSummary" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="card-body">
        <div class="text-center">
            <i class="bi bi-database-gear text-color-blue" style="font-size: 5rem;"></i>
        </div>
        <?php
        echo '<p class="text-center text-muted">Revisa la configuración antes de ejecutar la instalación. Una vez iniciada, se crearán los recursos en el servidor.</p>';
        $data['Fnc_FormInputs']->formPostData(1, 5, 'exclamation-circle', 0, '<h4><i class="bx bx-server"></i> Host de MySQL</h4>  <p>'.$data['PostData']['Host'].'</p>');
        $data['Fnc_FormInputs']->formPostData(1, 5, 'exclamation-circle', 0, '<h4><i class="bx bx-user"></i> Usuario</h4>          <p>'.$data['PostData']['Usuario'].'</p>');
        $data['Fnc_FormInputs']->formPostData(1, 5, 'exclamation-circle', 0, '<h4><i class="bx bx-data"></i> Base de Datos</h4>    <p>'.$data['PostData']['DBName'].'</p>');
        $data['Fnc_FormInputs']->formPostData(1, 5, 'exclamation-circle', 0, '<h4><i class="bx bx-extension"></i> Archivo SQL</h4> <p>install.sql</p>');

        $DataInf = '<h4>Importante:</h4><p>Al ejecutar la instalación se realizarán las siguientes acciones:</p>
        <ul class="list-unstyled">
            <li><i class="bi bi-check text-color-green-dark"></i> Se creará la base de datos <strong>'.$data['PostData']['DBName'].'</strong></li>
            <li><i class="bi bi-check text-color-green-dark"></i> Se ejecutará el archivo SQL de instalación</li>
            <li><i class="bi bi-check text-color-green-dark"></i> Se generará el archivo de configuración</li>
        </ul>';
        $data['Fnc_FormInputs']->formPostData(3, 4, 'bi bi-star', 0, $DataInf);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Host',     'Value' => $data['PostData']['Host'],     'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Usuario',  'Value' => $data['PostData']['Usuario'],  'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Password', 'Value' => $data['PostData']['Password'], 'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'DBName',   'Value' => $data['PostData']['DBName'],   'Required' => 2]);

        ?>
    </div>
    <div class="card-footer text-end">
        <a href="<?php echo $BASE.'/install'?>" class="btn btn-danger"><i class="bi bi-arrow-left-circle"></i> Reiniciar</a>
        <?php if($data['ValidInstall'] === true ){ ?>
            <button type="submit" class="btn btn-primary" onclick="return validateSummary();"> Siguiente <i class="bi bi-arrow-right-circle"></i></button>
        <?php }else{ ?>
            <a href="#" class="btn btn-primary disabled"> Siguiente <i class="bi bi-arrow-right-circle"></i></a>
        <?php } ?>
    </div>
</form>

