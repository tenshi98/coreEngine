<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
        <?php
        $UserIMG  = !empty($data['rowData']['Direccion_img'])
                    ? $data['UserData']['MainPathUrl'].$data['rowData']['Direccion_img']
                    : $BASE.'/img/picture-img.jpg';
        ?>
        <img src="<?php echo $UserIMG; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
    </div>
    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col-xxl-10">
        <?php
        //Se obtiene el nombre o la razón social
        $Entidad  = !empty($data['rowData']['Nombre'])
                    ? $data['rowData']['ApellidoPat'].' '.$data['rowData']['ApellidoMat'].' '.$data['rowData']['Nombre']
                    : $data['rowData']['RazonSocial'];

        //selecciono
        switch ($data['rowData']['idTipoEntidad']) {
            //Persona Natural
            case 1:
                $arrData_1 = [
                    ['Icon' => '','Titulo' => 'Tipo Entidad',     'Texto' => $data['rowData']['TipoEntidad']],
                    ['Icon' => '','Titulo' => 'Nombre',           'Texto' => $Entidad],
                    ['Icon' => '','Titulo' => 'Nick',             'Texto' => $data['rowData']['Nick']],
                    ['Icon' => '','Titulo' => 'Sexo',             'Texto' => $data['rowData']['Sexo']],
                ];
                break;
            //Empresas
            case 2:
                $arrData_1 = [
                    ['Icon' => '','Titulo' => 'Tipo Entidad', 'Texto' => $data['rowData']['TipoEntidad']],
                    ['Icon' => '','Titulo' => 'Nombre',       'Texto' => $Entidad],
                    ['Icon' => '','Titulo' => 'Nick',         'Texto' => $data['rowData']['Nick']],
                ];
                break;
        }

        echo '<h5 class="box-title text-color-red-dark">Datos Básicos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_1, 8);

        ?>
    </div>
</div>
