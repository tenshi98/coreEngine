<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <?php
        $Theme_color = 'summary-theme-color-2';
        $arrData1 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-shield-lock', 'Icon_color' => 10,'Title' => 'Categoria Permiso', 'Text' => $data['rowData']['PermisosCat']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-power',       'Icon_color' => 10,'Title' => 'Estado',            'Text' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-tag',         'Icon_color' => 10,'Title' => 'Tipo',              'Text' => $data['rowData']['Tipo']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text',   'Icon_color' => 10,'Title' => 'Nombre',            'Text' => $data['rowData']['Nombre']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-shield-lock', 'Icon_color' => 10,'Title' => 'Nivel Acceso',      'Text' => $data['rowData']['LevelLimit']],
        ];
        $arrData2 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-link-45deg',  'Icon_color' => 10,'Title' => 'Ruta Web',          'Text' => $data['rowData']['RutaWeb']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-code-slash',  'Icon_color' => 10,'Title' => 'Controlador',       'Text' => $data['rowData']['RutaController']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text',   'Icon_color' => 10,'Title' => 'Descripcion',       'Text' => $data['rowData']['Descripcion']],
        ];

        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData1, $OptionsTable);
            $OptionsTable['Cols'] = 1;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData2, $OptionsTable);
        echo '</div>';
        ?>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <?php echo $data['Fnc_WidgetsViews']->tittle_v2('Rutas', 23); ?>
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
                    // Verifico si hay datos
                    if(is_array($data['arrRutas'])&&!empty($data['arrRutas'])){
                        //filtro
                        $newData = $data['Fnc_CommonData']->agruparPorClave ($data['arrRutas'], 'Controller' );
                        // Recorro
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
