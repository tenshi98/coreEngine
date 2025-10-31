<?php
/********************************/
$CompanyLogo = !empty($data['UserData']['Sistema_IMGLogo'])
    ? $BASE.'/upload/'.$data['UserData']['Sistema_IMGLogo']
    : $BASE.'/img/logo.png';
?>
<div class="cs-invoice_head cs-type1 cs-mb25 column border-bottom-none">
    <div class="cs-invoice_left w-70 display-flex">
        <div class="cs-logo cs-mb5 cs-mr20"><img src="<?php echo $CompanyLogo; ?>" alt="Logo" style="max-width: 60px;"></div>
    </div>
    <div class="cs-invoice_right cs-text_right">
        <div class="cs-invoice_number cs-primary_color cs-mb0 cs-f16  display-flex justify-content-flex-end">
            <p class="cs-primary_color cs-mb0"><b>Campaña <strong><?php echo $data['rowData']['Nombre'];?></b></p>
        </div>
        <div class="cs-invoice_number cs-primary_color cs-mb0 cs-f16  display-flex justify-content-flex-end">
            <p class="cs-primary_color cs-mb0"><b>Fecha:</b></p>
            <p class="cs-mb0"><?php echo $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Fecha']); ?></p>
        </div>
    </div>
</div>

<div class="display-flex cs-text_center">
    <div class="cs-border-1"></div>
    <h5 class="cs-width_12 cs-dip_green_color">DATOS</h5>
    <div class="cs-border-1"></div>
</div>

<div class="cs-invoice_head cs-mb10 ">
    <div class="cs-invoice_left cs-mr97">
        <h4>Datos de la campaña</h4>
        <address>
            <strong><?php echo $data['rowData']['Nombre'];?></strong><br>
            <?php echo 'Fecha de Creacion: '.$data['Fnc_DataDate']->fechaEstandar($data['rowData']['Fecha']); ?><br>
            <?php echo 'Estado: <span class="badge '.$data['rowData']['EstadoColor'].'">'.$data['rowData']['EstadoNombre'].'</span>'; ?><br>
            <?php echo 'Usuario Creacion: '.$data['rowData']['Creador']; ?><br>
        </address>
    </div>
    <div class="cs-invoice_right">
        <h4>Montos</h4>
        <address>
            <?php echo 'Costos: '.$data['Fnc_DataNumbers']->Valores($data['rowData']['Costos'], 2); ?><br>
            <?php echo 'Beneficios: '.$data['Fnc_DataNumbers']->Valores($data['rowData']['Beneficios'], 2); ?><br>
            <?php echo 'Perdidas: '.$data['Fnc_DataNumbers']->Valores($data['rowData']['Perdidas'], 2); ?><br>
            <?php
            //Verifico el monto
            $isNegative = isset($data['rowData']['Margen']) && $data['rowData']['Margen'] < 0;
            $Icon       = $isNegative ? '<i class="ri-arrow-down-circle-line"></i>' : '<i class="ri-arrow-up-circle-line"></i>';
            $Color      = $isNegative ? 'text-danger' : 'text-success';
            echo 'Margen: <span class="'.$Color.'">'.$Icon.' '.$data['Fnc_DataNumbers']->Valores($data['rowData']['Margen'], 2).'</span>'; ?><br>
        </address>
    </div>
</div>

<?php if(is_array($data['arrCostos'])&&!empty($data['arrCostos'])){ ?>
    <div class="cs-table cs-style2 cs-f12" style="margin-bottom:15px;">
        <div class="cs-round_border">
            <div class="cs-table_responsive">
                <table>
                    <thead>
                        <tr class="cs-focus_bg">
                            <th class="cs-semi_bold cs-primary_color">Fecha</th>
                            <th class="cs-semi_bold cs-primary_color">Item</th>
                            <th class="cs-semi_bold cs-primary_color">Facturacion</th>
                            <th class="cs-semi_bold cs-primary_color cs-text_right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //Recorro
                        foreach($data['arrCostos'] AS $crud){
                            echo '
                            <tr>
                                <td>'.$data['Fnc_DataDate']->fechaEstandar($crud['Fecha']).'</td>
                                <td>'.$crud['Item'].'</td>
                                <td>';
                                    if(isset($crud['idFacturacion'])&&$crud['idFacturacion']!=0){
                                        echo $crud['DocumentoNombre'].' '.($crud['DocumentoN_Doc'] ?? 'nRef '.$crud['idFacturacion']);
                                    }
                                    echo '
                                </td>
                                <td class="cs-text_right cs-primary_color">'.$data['Fnc_DataNumbers']->Valores($crud['Costos'], 0).'</td>
                            </tr>';
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>

<?php if(is_array($data['arrPartidas'])&&!empty($data['arrPartidas'])){ ?>
    <div class="cs-table cs-style2 cs-f12" style="margin-bottom:15px;">
        <div class="cs-round_border">
            <div class="cs-table_responsive">
                <table>
                    <thead>
                        <tr class="cs-focus_bg">
                            <th class="cs-semi_bold cs-primary_color">Ubicacion</th>
                            <th class="cs-semi_bold cs-primary_color">Entidad</th>
                            <th class="cs-semi_bold cs-primary_color">Estado</th>
                            <th class="cs-semi_bold cs-primary_color">Producto</th>
                            <th class="cs-semi_bold cs-primary_color cs-text_right">Cantidad</th>
                            <th class="cs-semi_bold cs-primary_color cs-text_right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //filtro
                        $newData  = $data['Fnc_CommonData']->agruparPorClave ($data['arrPartidas'], 'Fecha' );
                        $ProdData = $data['Fnc_CommonData']->agruparPorClave ($data['arrPartidasProd'], 'idExistencia' );
                        //Recorro
                        foreach ($newData AS $Fecha=>$datos){
                            //imprimimos la categoría
                            echo '<tr class="cs-focus_bg"><td colspan="7"><strong>Partida del '.$data['Fnc_DataDate']->fechaEstandar($Fecha).'</strong></td></tr>';
                            //se recorren los datos dentro de la categoría
                            foreach ($datos AS $crud){
                                //Se obtiene el nombre o la razón social
                                $Entidad  = '';
                                $Entidad .= !empty($crud['EntidadNick'])
                                            ? $crud['EntidadNick'].'<br>'
                                            : '';
                                $Entidad .= !empty($crud['EntidadNombre'])
                                            ? $crud['EntidadApellido'].' '.$crud['EntidadNombre']
                                            : $crud['EntidadRazonSocial'];

                                //Se revisan los productos
                                $P_Producto   = '';
                                $P_Cantidad   = '';
                                $P_Beneficios = '';
                                foreach ($ProdData[$crud['idExistencia']] AS $dataP){
                                    $P_Producto   .= $dataP['Producto'].'<br/>';
                                    $P_Cantidad   .= $data['Fnc_DataNumbers']->Cantidades($dataP['Cantidad'], 0).' '.$dataP['Unimed'].'<br/>';
                                    $P_Beneficios .= $data['Fnc_DataNumbers']->Valores($dataP['Beneficios'], 0).'<br/>';
                                }
                                /*********************/
                                echo '
                                <tr>
                                    <td>'.$crud['EntidadSector'].'<br>'.$crud['EntidadDireccion'].'</td>
                                    <td>'.$Entidad.'</td>
                                    <td>
                                        '.$crud['EstadoPartida'].'<br/>';
                                        if(isset($crud['idFacturacion'])&&$crud['idFacturacion']!=0){
                                            echo $crud['DocumentoNombre'].' '.($crud['DocumentoN_Doc'] ?? 'nRef '.$crud['idFacturacion']);
                                        }
                                        echo '
                                    </td>
                                    <td>'.$P_Producto.'</td>
                                    <td class="cs-text_right cs-primary_color">'.$P_Cantidad.'</td>
                                    <td class="cs-text_right cs-primary_color">'.$P_Beneficios.'</td>
                                </tr>';
                            }
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>

<?php if(is_array($data['arrPerdidas'])&&!empty($data['arrPerdidas'])){ ?>
    <div class="cs-table cs-style2 cs-f12" style="margin-bottom:15px;">
        <div class="cs-round_border">
            <div class="cs-table_responsive">
                <table>
                    <thead>
                        <tr class="cs-focus_bg">
                            <th class="cs-semi_bold cs-primary_color">Fecha</th>
                            <th class="cs-semi_bold cs-primary_color">Perdida</th>
                            <th class="cs-semi_bold cs-primary_color cs-text_right">Cantidad</th>
                            <th class="cs-semi_bold cs-primary_color cs-text_right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //Recorro
                        foreach($data['arrPerdidas'] AS $crud){
                            echo '
                            <tr>
                                <td>'.$data['Fnc_DataDate']->fechaEstandar($crud['Fecha']).'</td>
                                <td>
                                    '.$crud['Item'].'<br/>';
                                    if(isset($crud['idMovimiento'])&&$crud['idMovimiento']!=0){
                                        echo 'Mov Prod '.$crud['Producto'].' nRef '.$crud['idMovimiento'];
                                    }
                                    echo '
                                </td>
                                <td class="cs-text_right cs-primary_color">'.$data['Fnc_DataNumbers']->Cantidades($crud['Cantidad'], 0).'</td>
                                <td class="cs-text_right cs-primary_color">'.$data['Fnc_DataNumbers']->Valores($crud['Perdidas'], 0).'</td>
                            </tr>';
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>

<div class="cs-table cs-style2 cs-mt20">
    <div class="cs-table_responsive">
        <table>
            <tbody>
                <tr class="cs-table_baseline">
                    <td class="cs-width_6 cs-primary_color">
                        <?php if(isset($data['rowData']['Observaciones'])&&$data['rowData']['Observaciones']!=''){echo $data['rowData']['Observaciones'];}else{echo 'Sin Observaciones.';} ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

