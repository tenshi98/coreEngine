<?php
/********************************/
$CompanyLogo =  !empty($data['UserData']['Sistema_IMGLogo'])
                ? $BASE.'/upload/'.$data['UserData']['Sistema_IMGLogo']
                : $BASE.'/img/logo.png';

/********************************/
$NombreEntidad  = '';
$NombreEntidad .=   !empty($data['rowData']['EntidadesNombre'])
                    ? $data['rowData']['EntidadesNombre'].' '.$data['rowData']['EntidadesApellido']
                    : $data['rowData']['EntidadesRazonSocial'];
$NombreEntidad .=   !empty($data['rowData']['EntidadesNick'])
                    ? ' ('.$data['rowData']['EntidadesNick'].')'
                    : '';

//Se agrega emisor y receptor
$From = '
<strong class="cs-primary_color">Emisor:</strong>
<p class="cs-mb8">
    <strong>'.$data['rowSistema']['Sistema_Nombre'].'</strong><br>';
    if(isset($data['rowSistema']['Sistema_Direccion'])&&$data['rowSistema']['Sistema_Direccion']!=''){ $From .= $data['rowSistema']['Sistema_Direccion'].'<br>';}
    if(isset($data['rowSistema']['Sistema_Comuna'])&&$data['rowSistema']['Sistema_Comuna']!=''){       $From .= $data['rowSistema']['Sistema_Comuna'].'<br>';}
    if(isset($data['rowSistema']['Sistema_Ciudad'])&&$data['rowSistema']['Sistema_Ciudad']!=''){       $From .= $data['rowSistema']['Sistema_Ciudad'].'<br>';}
    if(isset($data['rowSistema']['Sistema_Email'])&&$data['rowSistema']['Sistema_Email']!=''){         $From .= 'Email: '.$data['rowSistema']['Sistema_Email'].'<br>';}
    if(isset($data['rowSistema']['Sistema_Fono1'])&&$data['rowSistema']['Sistema_Fono1']!=''){         $From .= 'Celular: '.$data['Fnc_DataNumbers']->formatPhone($data['rowSistema']['Sistema_Fono1']).'<br>';}
    if(isset($data['rowSistema']['Sistema_Fono2'])&&$data['rowSistema']['Sistema_Fono2']!=''){         $From .= 'Telefono: '.$data['Fnc_DataNumbers']->formatPhone($data['rowSistema']['Sistema_Fono2']).'<br>';}
$From .= '</p>';
$To = '
<b class="cs-primary_color">Cliente:</b>
<p>
    <strong>'.$NombreEntidad.'</strong><br>';
    if(isset($data['rowData']['EntidadesDireccion'])&&$data['rowData']['EntidadesDireccion']!=''){ $To .= $data['rowData']['EntidadesDireccion'].'<br>';}
    if(isset($data['rowData']['EntidadesComuna'])&&$data['rowData']['EntidadesComuna']!=''){       $To .= $data['rowData']['EntidadesComuna'].'<br>';}
    if(isset($data['rowData']['EntidadesCiudad'])&&$data['rowData']['EntidadesCiudad']!=''){       $To .= $data['rowData']['EntidadesCiudad'].'<br>';}
    if(isset($data['rowData']['EntidadesEmail'])&&$data['rowData']['EntidadesEmail']!=''){         $To .= 'Email: '.$data['rowData']['EntidadesEmail'].'<br>';}
    if(isset($data['rowData']['EntidadesFono1'])&&$data['rowData']['EntidadesFono1']!=''){         $To .= 'Celular: '.$data['Fnc_DataNumbers']->formatPhone($data['rowData']['EntidadesFono1']).'<br>';}
    if(isset($data['rowData']['EntidadesFono2'])&&$data['rowData']['EntidadesFono2']!=''){         $To .= 'Telefono: '.$data['Fnc_DataNumbers']->formatPhone($data['rowData']['EntidadesFono2']).'<br>';}
$To .= '</p>';

?>


<div class="cs-invoice_head cs-type1 cs-mb25 column border-bottom-none">
    <div class="cs-invoice_left w-70 display-flex">
        <div class="cs-logo cs-mb5 cs-mr20"><img src="<?php echo $CompanyLogo; ?>" alt="Logo" style="max-width: 60px;"></div>
    </div>
    <div class="cs-invoice_right cs-text_right">
        <div class="cs-invoice_number cs-primary_color cs-mb0 cs-f16  display-flex justify-content-flex-end">
            <p class="cs-primary_color cs-mb0"><b>Cotización:</b></p>
            <p class="cs-mb0"><?php echo '#'.$data['rowData']['idCotizacion']; ?></p>
        </div>
        <div class="cs-invoice_number cs-primary_color cs-mb0 cs-f16  display-flex justify-content-flex-end">
            <p class="cs-primary_color cs-mb0"><b>Fecha:</b></p>
            <p class="cs-mb0"><?php echo $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Creacion_fecha']); ?></p>
        </div>
    </div>
</div>

<div class="display-flex cs-text_center">
    <div class="cs-border-1"></div>
    <h5 class="cs-width_12 cs-dip_green_color">DATOS</h5>
    <div class="cs-border-1"></div>
</div>

<div class="cs-invoice_head cs-mb10 ">
    <div class="cs-invoice_left cs-mr97"><?php echo $From; ?></div>
    <div class="cs-invoice_right"><?php echo $To; ?></div>
</div>

<div class="cs-table cs-style2 cs-f12">
    <div class="cs-round_border">
        <div class="cs-table_responsive">
            <table>
                <thead>
                    <tr class="cs-focus_bg">
                        <th class="cs-width_3 cs-semi_bold cs-primary_color">Item</th>
                        <th class="cs-width_3 cs-semi_bold cs-primary_color">Cantidad</th>
                        <th class="cs-width_1 cs-semi_bold cs-primary_color cs-text_right">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /*******************************************************************/
                    if(is_array($data['arrItems'])&&!empty($data['arrItems'])){
                        //Recorro
                        foreach($data['arrItems'] AS $crud){
                            echo '
                            <tr>
                                <td>'.$crud['Item'].'</td>
                                <td>'.$data['Fnc_DataNumbers']->Cantidades($crud['Number'], 2).'</td>
                                <td class="cs-text_right cs-primary_color">'.$data['Fnc_DataNumbers']->Valores($crud['ValorTotal'], 2).'</td>
                            </tr>';
                        }
                    }
                    /*******************************************************************/
                    if(is_array($data['arrProductos'])&&!empty($data['arrProductos'])){
                        //Recorro
                        foreach($data['arrProductos'] AS $crud){
                            echo '
                            <tr>
                                <td>'.$crud['ProductoNombre'].'</td>
                                <td>'.$data['Fnc_DataNumbers']->Cantidades($crud['ProductoCantidad'], 2).' '.$crud['UnidadMedida'].'</td>
                                <td class="cs-text_right cs-primary_color">'.$data['Fnc_DataNumbers']->Valores($crud['ProductoValor'], 2).'</td>
                            </tr>';
                        }
                    }
                    /*******************************************************************/
                    if(is_array($data['arrServicios'])&&!empty($data['arrServicios'])){
                        //Recorro
                        foreach($data['arrServicios'] AS $crud){
                            echo '
                            <tr>
                                <td>'.$crud['ServicioNombre'].'</td>
                                <td>'.$data['Fnc_DataNumbers']->Cantidades($crud['ServicioCantidad'], 2).'</td>
                                <td class="cs-text_right cs-primary_color">'.$data['Fnc_DataNumbers']->Valores($crud['ServicioValor'], 2).'</td>
                            </tr>';
                        }
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="cs-table cs-style2 cs-mt20">
    <div class="cs-table_responsive">
        <table>
            <tbody>
                <tr class="cs-table_baseline">
                    <td class="cs-width_6 cs-primary_color"><?php if(isset($data['rowData']['Observaciones'])&&$data['rowData']['Observaciones']!=''){echo $data['rowData']['Observaciones'];}else{echo 'Sin Observaciones.';} ?></td>
                    <td class="cs-width_3 cs-text_right">
                        <p class="cs-mb5 cs-mb5 cs-f15 cs-primary_color cs-semi_bold">Subtotal:</p>
                        <p class="cs-primary_color cs-bold cs-f16 cs-mb5 ">IVA:</p>
                        <p class="cs-primary_color cs-bold cs-f16 cs-mb5 ">Total:</p>
                    </td>
                    <td class="cs-width_3 cs-text_rightcs-f16">
                        <p class="cs-mb5 cs-mb5 cs-text_right cs-f15 cs-primary_color cs-semi_bold"><?php echo $data['Fnc_DataNumbers']->Valores($data['rowData']['ValorNeto'], 2); ?></p>
                        <p class="cs-primary_color cs-bold cs-f16 cs-mb5 cs-text_right"><?php echo $data['Fnc_DataNumbers']->Valores($data['rowData']['IVA'], 2); ?></p>
                        <p class="cs-primary_color cs-bold cs-f16 cs-mb5 cs-text_right"><?php echo $data['Fnc_DataNumbers']->Valores($data['rowData']['ValorTotal'], 2); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

