<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<?php if(isset($data['MainViewData']['Data_ComprasTotal']['ValorTotal'])){ ?>
    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
        <div class="card">
            <div class="card-body p-4">
                <div class="infographicBox">
                    <i class="bi bi-currency-dollar bg-success"></i>
                    <span class="headline">Total Compras x Pagar</span>
                    <span class="value"><span class="timer"><?php echo $data['Fnc_DataNumbers']->Valores(($data['MainViewData']['Data_ComprasTotal']['ValorTotal'] - $data['MainViewData']['Data_ComprasTotal']['MontoPagado']), 0); ?></span></span>
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive" id="listTableData">
                    <table class="table table-sm table-hover datatable">
                        <thead>
                            <tr>
                                <th scope="col">Proveedor</th>
                                <th scope="col" class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            //Verifico si hay datos
                            if(is_array($data['MainViewData']['Data_ComprasListado'])){
                                foreach($data['MainViewData']['Data_ComprasListado'] as $datos){
                                    //Se obtiene el nombre o la razón social
                                    $Entidad  = '';
                                    $Entidad .= !empty($datos['EntidadesNick'])
                                                ? $datos['EntidadesNick'].'<br>'
                                                : '';
                                    $Entidad .= !empty($datos['EntidadesNombre'])
                                                ? $datos['EntidadesApellido'].' '.$datos['EntidadesNombre']
                                                : $datos['EntidadesRazonSocial'];
                                    echo '
                                    <tr>
                                        <td>'.$Entidad.'</td>
                                        <td class="text-end">'.$data['Fnc_DataNumbers']->Valores(($datos['ValorTotal'] - $datos['MontoPagado']), 0).'</td>
                                    </tr>';
                                }
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-around">
                    <a class="btn btn-primary flex-grow-1" target="_blank" rel="noopener noreferrer" href="<?php echo $BASE.'/gestionDocumentos/compras/listado/listAll'; ?>"><i class="bi bi-bounding-box"></i> Ver Documentos</a>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<?php if(isset($data['MainViewData']['Data_VentasTotal']['ValorTotal'])){ ?>
    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
        <div class="card">
            <div class="card-body p-4">
                <div class="infographicBox">
                    <i class="bi bi-currency-dollar bg-success"></i>
                    <span class="headline">Total Ventas x Cobrar</span>
                    <span class="value"><span class="timer"><?php echo $data['Fnc_DataNumbers']->Valores(($data['MainViewData']['Data_VentasTotal']['ValorTotal'] - $data['MainViewData']['Data_VentasTotal']['MontoPagado']), 0); ?></span></span>
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive" id="listTableData">
                    <table class="table table-sm table-hover datatable">
                        <thead>
                            <tr>
                                <th scope="col">Cliente</th>
                                <th scope="col" class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            //Verifico si hay datos
                            if(is_array($data['MainViewData']['Data_VentasListado'])){
                                foreach($data['MainViewData']['Data_VentasListado'] as $datos){
                                    //Se obtiene el nombre o la razón social
                                    $Entidad  = '';
                                    $Entidad .= !empty($datos['EntidadesNick'])
                                                ? $datos['EntidadesNick'].' | '
                                                : '';
                                    $Entidad .= !empty($datos['EntidadesNombre'])
                                                ? $datos['EntidadesApellido'].' '.$datos['EntidadesNombre']
                                                : $datos['EntidadesRazonSocial'];
                                    echo '
                                    <tr>
                                        <td>'.$Entidad.'</td>
                                        <td class="text-end">'.$data['Fnc_DataNumbers']->Valores(($datos['ValorTotal'] - $datos['MontoPagado']), 0).'</td>
                                    </tr>';
                                }
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-around">
                    <a class="btn btn-primary flex-grow-1" target="_blank" rel="noopener noreferrer" href="<?php echo $BASE.'/gestionDocumentos/ventas/listado/listAll'; ?>"><i class="bi bi-bounding-box"></i> Ver Documentos</a>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
