
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
        <?php
        $UserIMG  = !empty($data['rowData']['Direccion_img'])
                    ? $BASE.'/upload/'.$data['rowData']['Direccion_img']
                    : $BASE.'/img/picture-img.jpg';
        ?>
        <img src="<?php echo $UserIMG; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
    </div>
    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-7">
        <?php
        $arrData_1 = [
            ['Icon' => '','Titulo' => 'Email',           'Texto' => $data['rowData']['email']],
            ['Icon' => '','Titulo' => 'Tipo de usuario', 'Texto' => $data['rowData']['TipoUsuario']],
        ];
        $arrData_2 = [
            ['Icon' => '','Titulo' => 'Nombre',               'Texto' => $data['rowData']['Nombre']],
            ['Icon' => '','Titulo' => 'Fono',                 'Texto' => $data['Fnc_DataNumbers']->formatPhone($data['rowData']['Fono'])],
            ['Icon' => '','Titulo' => 'Rut',                  'Texto' => $data['rowData']['Rut']],
            ['Icon' => '','Titulo' => 'Fecha de Nacimiento',  'Texto' => $data['Fnc_DataDate']->fechaCompleta($data['rowData']['fNacimiento'])],
            ['Icon' => '','Titulo' => 'Ciudad',               'Texto' => $data['rowData']['Ciudad']],
            ['Icon' => '','Titulo' => 'Comuna',               'Texto' => $data['rowData']['Comuna']],
            ['Icon' => '','Titulo' => 'Dirección',            'Texto' => $data['rowData']['Direccion']],
        ];
        $arrData_3 = [
            ['Icon' => '','Titulo' => 'X(Twitter)',  'Texto' => $data['rowData']['Social_X']],
            ['Icon' => '','Titulo' => 'Facebook',    'Texto' => $data['rowData']['Social_Facebook']],
            ['Icon' => '','Titulo' => 'Instagram',   'Texto' => $data['rowData']['Social_Instagram']],
            ['Icon' => '','Titulo' => 'Linkedin',    'Texto' => $data['rowData']['Social_Linkedin']],
        ];
        $arrData_4 = [
            ['Icon' => '','Titulo' => 'Estado',         'Texto' => '<span class="badge '.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
            ['Icon' => '','Titulo' => 'Posición Menu',  'Texto' => $data['rowData']['MenuPosicion']],
        ];

        /*******************************************/
        echo '<h5 class="card-title">Datos del Perfil</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_1, 8);

        echo '<h5 class="card-title">Datos Personales</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_2, 8);

        echo '<h5 class="card-title">Social</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_3, 8);

        echo '<h5 class="card-title">Opciones</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_4, 8);

        ?>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <h5 class="card-title">Observaciones</h5>
        <div class="clearfix"></div>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <tbody>
                    <?php
                    //Verifico si hay datos
                    if(is_array($data['arrObservaciones'])&&!empty($data['arrObservaciones'])){
                        //Recorro
                        foreach($data['arrObservaciones'] AS $crud){
                             echo '<tr><td>'.$crud['Observacion'].'</td></tr>';
                        }
                    }else{
                        echo '<tr><td colspan="1">No se encontraron entradas</td></tr>';
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
