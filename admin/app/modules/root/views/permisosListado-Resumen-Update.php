<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <?php
        $arrData = [
            ['Icon' => '','Titulo' => 'Categoria Permiso', 'Texto' => $data['rowData']['PermisosCat']],
            ['Icon' => '','Titulo' => 'Estado',            'Texto' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
            ['Icon' => '','Titulo' => 'Tipo',              'Texto' => $data['rowData']['Tipo']],
            ['Icon' => '','Titulo' => 'Nombre',            'Texto' => $data['rowData']['Nombre']],
            ['Icon' => '','Titulo' => 'Nivel Acceso',      'Texto' => $data['rowData']['LevelLimit']],
            ['Icon' => '','Titulo' => 'Ruta Web',          'Texto' => $data['rowData']['RutaWeb']],
            ['Icon' => '','Titulo' => 'Controlador',       'Texto' => $data['rowData']['RutaController']],
            ['Icon' => '','Titulo' => 'Descripcion',       'Texto' => $data['rowData']['Descripcion']],
        ];

        //echo '<h5 class="box-title text-color-red-dark">Datos del Perfil</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData, 8);
        ?>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <h5 class="box-title text-color-red-dark">Rutas</h5>
        <div class="clearfix"></div>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th scope="col">Metodo</th>
                        <th scope="col">Ruta</th>
                        <th scope="col">Descripcion</th>
                        <th scope="col">Objetivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    //Verifico si hay datos
                    if(is_array($data['arrRutas'])&&!empty($data['arrRutas'])){
                        //filtro
                        $newData = $data['Fnc_CommonData']->agruparPorClave ($data['arrRutas'], 'Controller' );
                        //Recorro
                        foreach ($newData as $Controller=>$permisos){
                            //imprimimos la categoría
                            echo '<tr class="table-secondary"><td colspan="5"><strong>'.$Controller.'</strong></td></tr>';
                            //se recorren los datos dentro de la categoría
                            foreach ($permisos as $ruta){ ?>
                                <tr>
                                    <td><?php echo $ruta['Metodo']; ?></td>
                                    <td><?php echo '<strong>Ruta: </strong>'.$ruta['RutaWeb'].'<br><strong>Controlador: </strong>'.$ruta['RutaController']; ?></td>
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
