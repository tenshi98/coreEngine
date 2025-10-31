<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <?php
        $arrData = [
            ['Icon' => '','Titulo' => 'Categoria Permiso', 'Texto' => $data['rowData']['PermisosCat']],
            ['Icon' => '','Titulo' => 'Estado',            'Texto' => '<span class="badge '.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
            ['Icon' => '','Titulo' => 'Tipo',              'Texto' => $data['rowData']['Tipo']],
            ['Icon' => '','Titulo' => 'Nombre',            'Texto' => $data['rowData']['Nombre']],
            ['Icon' => '','Titulo' => 'Nivel Acceso',      'Texto' => $data['rowData']['LevelLimit']],
            ['Icon' => '','Titulo' => 'Ruta Web',          'Texto' => $data['rowData']['RutaWeb']],
            ['Icon' => '','Titulo' => 'Controlador',       'Texto' => $data['rowData']['RutaController']],
            ['Icon' => '','Titulo' => 'Descripcion',       'Texto' => $data['rowData']['Descripcion']],
        ];

        //echo '<h5 class="card-title">Datos del Perfil</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData, 8);
        ?>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <h5 class="card-title">Rutas</h5>
        <div class="clearfix"></div>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Metodo</th>
                        <th>Ruta Web</th>
                        <th>Ruta Controller</th>
                        <th>Descripcion</th>
                        <th>Objetivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    //Verifico si hay datos
                    if(is_array($data['arrRutas'])&&!empty($data['arrRutas'])){
                        //filtro
                        $newData = $data['Fnc_CommonData']->agruparPorClave ($data['arrRutas'], 'Controller' );
                        //Recorro
                        foreach ($newData AS $Controller=>$permisos){
                            //imprimimos la categoría
                            echo '<tr class="table-secondary"><td colspan="5"><strong>'.$Controller.'</strong></td></tr>';
                            //se recorren los datos dentro de la categoría
                            foreach ($permisos AS $ruta){ ?>
                                <tr>
                                    <td><?php echo $ruta['Metodo']; ?></td>
                                    <td><?php echo $ruta['RutaWeb']; ?></td>
                                    <td><?php echo $ruta['RutaController']; ?></td>
                                    <td><?php echo $ruta['Descripcion']; ?></td>
                                    <td><?php echo $ruta['LevelLimit']; ?></td>
                                </tr>
                        <?php }
                        }
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
