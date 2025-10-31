
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
        <?php
        $UserIMG  = !empty($data['rowData']['Sistema_IMGLogo'])
                    ? $BASE.'/upload/'.$data['rowData']['Sistema_IMGLogo']
                    : $BASE.'/img/picture-img.jpg';
        ?>
        <img src="<?php echo $UserIMG; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
    </div>
    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-7">
        <?php
        $arrData_1 = [
            ['Icon' => '','Titulo' => 'Nombre',              'Texto' => $data['rowData']['Sistema_Nombre']],
            ['Icon' => '','Titulo' => 'Email',               'Texto' => $data['rowData']['Sistema_Email']],
            ['Icon' => '','Titulo' => 'Rut',                 'Texto' => $data['rowData']['Sistema_Rut']],
            ['Icon' => '','Titulo' => 'Ciudad',              'Texto' => $data['rowData']['Ciudad']],
            ['Icon' => '','Titulo' => 'Comuna',              'Texto' => $data['rowData']['Comuna']],
            ['Icon' => '','Titulo' => 'Direccion',           'Texto' => $data['rowData']['Sistema_Direccion']],
            ['Icon' => '','Titulo' => 'Fono Noti Whatsapp',  'Texto' => $data['rowData']['Sistema_NotiWhatsapp']],
        ];
        $arrData_2 = [
            ['Icon' => '','Titulo' => 'Contacto Nombre', 'Texto' => $data['rowData']['Contacto_Nombre']],
            ['Icon' => '','Titulo' => 'Contacto Fono1',  'Texto' => $data['rowData']['Contacto_Fono1']],
            ['Icon' => '','Titulo' => 'Contacto Fono2',  'Texto' => $data['rowData']['Contacto_Fono2']],
            ['Icon' => '','Titulo' => 'Contacto Fax',    'Texto' => $data['rowData']['Contacto_Fax']],
            ['Icon' => '','Titulo' => 'Contacto Email',  'Texto' => $data['rowData']['Contacto_Email']],
            ['Icon' => '','Titulo' => 'Contacto Web',    'Texto' => $data['rowData']['Contacto_Web']],
        ];
        $arrData_3 = [
            ['Icon' => '','Titulo' => 'Representante Nombre',  'Texto' => $data['rowData']['RepresentanteNombre']],
            ['Icon' => '','Titulo' => 'Representante Rut',     'Texto' => $data['rowData']['RepresentanteRut']],
            ['Icon' => '','Titulo' => 'Representante Fono',    'Texto' => $data['rowData']['RepresentanteFono']],
            ['Icon' => '','Titulo' => 'Representante Email',   'Texto' => $data['rowData']['RepresentanteEmail']],
        ];
        $arrData_4 = [
            ['Icon' => '','Titulo' => 'Config Whatsapp Token',        'Texto' => $data['rowData']['Config_WhatsappToken']],
            ['Icon' => '','Titulo' => 'Config Whatsapp Instance Id',  'Texto' => $data['rowData']['Config_WhatsappInstanceId']],
        ];



        echo '<h5 class="card-title">Datos Básicos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_1, 8);

        echo '<h5 class="card-title">Datos de Contacto</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_2, 8);

        echo '<h5 class="card-title">Representante Legal</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_3, 8);

        echo '<h5 class="card-title">APIS</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_4, 8);

        //funcion para devolver el uso
        function activo($valor){
            switch ($valor) {
                case 1: return 'No'; break;
                case 2: return 'Si'; break;
            }
        }
        ?>
    </div>

</div>
