<table class="table table-sm table-hover datatable" id="tableDataPartidas">
    <thead>
        <tr>
            <th>Ubicacion</th>
            <th>Entidad</th>
            <th>Estado</th>
            <th>Producto</th>
            <th class="text-end" width="120">Cantidad</th>
            <th class="text-end" width="120">Valor</th>
            <th>Acciones</th>
        </tr>
        <tr role="row">
            <th colspan="7"><input class="form-control" id="filterTableDataPartidas" type="text" placeholder="Filtrar Datos.."></th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrPartidas'])&&!empty($data['arrPartidas'])){
            //Cantidad Productos
            for ($i=1; $i <= 10; $i++) {
                //Variables para los totales
                $TotalNombre[$i]      = '';
                $TotalUnimed[$i]      = '';
                $TotalCantidad[$i]    = 0;
                $TotalValor[$i]       = 0;
                //Variables para los subtotales
                $SubTotalNombre[$i]   = '';
                $SubTotalUnimed[$i]   = '';
                $SubTotalCantidad[$i] = 0;
                $SubTotalValor[$i]    = 0;
                //Estados
                for ($x=0; $x <= 6; $x++) {
                    //Variables para los totales
                    $TotalProdCantidad[$i][$x]    = 0;
                    $TotalProdValor[$i][$x]       = 0;
                    $TotalProdEstado[$i][$x]      = '';
                    //Variables para los subtotales
                    $SubTotalProdCantidad[$i][$x] = 0;
                    $SubTotalProdValor[$i][$x]    = 0;
                    $SubTotalProdEstado[$i][$x]   = '';
                }
            }

            //filtro
            $newData  = $data['Fnc_CommonData']->agruparPorClave ($data['arrPartidas'], 'Fecha' );
            $ProdData = $data['Fnc_CommonData']->agruparPorClave ($data['arrPartidasProd'], 'idExistencia' );
            //Recorro
            foreach ($newData as $Fecha=>$datos){
                //Datos para borrado masivo
                $Del_idCampana       = $data['Fnc_Codification']->encryptDecrypt('encrypt', $datos[0]['idCampana']);
                $Del_Fecha           = $data['Fnc_Codification']->encryptDecrypt('encrypt', $Fecha);
                $Del_PartidaCreada   = $data['Fnc_Codification']->encryptDecrypt('encrypt', 1);
                $Del_PartidaEnviada  = $data['Fnc_Codification']->encryptDecrypt('encrypt', 2);
                //imprimimos la categoría
                echo '
                <tr class="table-secondary">
                    <td colspan="6"><strong>Partida del '.$data['Fnc_DataDate']->fechaEstandar($Fecha).'</strong></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="tabPartidasDelMassive( \''.$Del_idCampana.'\', \''.$Del_Fecha.'\', \''.$Del_PartidaCreada.'\', \'recien creadas con fecha '.$data['Fnc_DataDate']->fechaEstandar($Fecha).'\')" class="btn btn-danger  btn-sm tooltiplink" data-title="Borrar Partidas"><i class="bi bi-trash"></i> Creadas</button>
                            <button type="button" onclick="tabPartidasDelMassive( \''.$Del_idCampana.'\', \''.$Del_Fecha.'\', \''.$Del_PartidaEnviada.'\', \'enviadas con fecha '.$data['Fnc_DataDate']->fechaEstandar($Fecha).'\')"      class="btn btn-danger  btn-sm tooltiplink" data-title="Borrar Partidas"><i class="bi bi-trash"></i> Enviadas</button>';
                            if(isset($data['UserData']["Config_WhatsappToken"])&&$data['UserData']["Config_WhatsappToken"]!=''){
                                echo '<button type="button" onclick="tabPartidasSendMassive( \''.$Del_idCampana.'\', \''.$Del_Fecha.'\', \''.$Del_PartidaCreada.'\')" class="btn btn-success btn-sm tooltiplink" data-title="Enviar Notificación Masiva por Whatsapp"><i class="bi bi-whatsapp"></i> Enviar Notificación</button>';
                            }
                             echo '
                        </div>
                    </td>
                </tr>';
                //se recorren los datos dentro de la categoría
                foreach ($datos as $crud){
                    /********************************/
                    //Se obtiene el nombre o la razón social
                    $Entidad  = '';
                    $Entidad .= !empty($crud['EntidadNick'])
                                ? $crud['EntidadNick'].'<br>'
                                : '';
                    $Entidad .= !empty($crud['EntidadNombre'])
                                ? $crud['EntidadApellido'].' '.$crud['EntidadNombre']
                                : $crud['EntidadRazonSocial'];

                    /********************************/
                    //Se obtiene el nombre o la razón social
                    $EntidadWsp = '';
                    if (isset($crud['EntidadNick'])&&$crud['EntidadNick']!='') {
                        $EntidadWsp  = $crud['EntidadNick'];
                    }else{
                        $EntidadWsp =   !empty($crud['EntidadNombre'])
                                        ? $crud['EntidadNombre'].' '.$crud['EntidadApellido']
                                        : $crud['EntidadRazonSocial'];
                    }
                    /********************************/
                    //Se filtran productos
                    $P_Producto   = '';
                    $P_Cantidad   = '';
                    $P_Beneficios = '';
                    //Se vacian los datos
                    //Cantidad Productos
                    for ($i=1; $i <= 10; $i++) {
                        $SubTotalNombre[$i]   = '';
                        $SubTotalUnimed[$i]   = '';
                        $SubTotalCantidad[$i] = 0;
                        $SubTotalValor[$i]    = 0;
                        //Estados
                        for ($x=0; $x <= 6; $x++) {
                            $SubTotalProdCantidad[$i][$x] = 0;
                            $SubTotalProdValor[$i][$x]    = 0;
                            $SubTotalProdEstado[$i][$x]   = '';
                        }
                    }
                    //Se recorren datos
                    foreach ($ProdData[$crud['idExistencia']] as $dataP){
                        $P_Producto   .= $dataP['Producto'].'<br/>';
                        $P_Cantidad   .= $data['Fnc_DataNumbers']->Cantidades($dataP['Cantidad'], 2).' '.$dataP['Unimed'].'<br/>';
                        $P_Beneficios .= $data['Fnc_DataNumbers']->Valores($dataP['Beneficios'], 2).'<br/>';
                        //Variables
                        $SubTotalNombre[$dataP['idProducto']]                                 = $dataP['Producto'];
                        $SubTotalUnimed[$dataP['idProducto']]                                 = $dataP['Unimed'];
                        $SubTotalCantidad[$dataP['idProducto']]                               = $SubTotalCantidad[$dataP['idProducto']] + $dataP['Cantidad'];
                        $SubTotalValor[$dataP['idProducto']]                                  = $SubTotalValor[$dataP['idProducto']]    + $dataP['Beneficios'];
                        $SubTotalProdCantidad[$dataP['idProducto']][$crud['idEstadoPartida']] = $SubTotalProdCantidad[$dataP['idProducto']][$crud['idEstadoPartida']] + $dataP['Cantidad'];
                        $SubTotalProdValor[$dataP['idProducto']][$crud['idEstadoPartida']]    = $SubTotalProdValor[$dataP['idProducto']][$crud['idEstadoPartida']]    + $dataP['Beneficios'];
                        $SubTotalProdEstado[$dataP['idProducto']][$crud['idEstadoPartida']]   = $crud['EstadoPartida'];
                        //Variables
                        $TotalNombre[$dataP['idProducto']]                                 = $dataP['Producto'];
                        $TotalUnimed[$dataP['idProducto']]                                 = $dataP['Unimed'];
                        $TotalCantidad[$dataP['idProducto']]                               = $TotalCantidad[$dataP['idProducto']] + $dataP['Cantidad'];
                        $TotalValor[$dataP['idProducto']]                                  = $TotalValor[$dataP['idProducto']]    + $dataP['Beneficios'];
                        $TotalProdCantidad[$dataP['idProducto']][$crud['idEstadoPartida']] = $TotalProdCantidad[$dataP['idProducto']][$crud['idEstadoPartida']] + $dataP['Cantidad'];
                        $TotalProdValor[$dataP['idProducto']][$crud['idEstadoPartida']]    = $TotalProdValor[$dataP['idProducto']][$crud['idEstadoPartida']]    + $dataP['Beneficios'];
                        $TotalProdEstado[$dataP['idProducto']][$crud['idEstadoPartida']]   = $crud['EstadoPartida'];
                    }
                    ?>
                    <tr class="<?php echo $crud['EstadoPartidaColor']; ?>">
                        <td><?php echo $crud['EntidadSector'].'<br>'.$crud['EntidadDireccion']; ?></td>
                        <td><?php echo $Entidad; ?></td>
                        <td><?php echo $crud['EstadoPartida']; ?></td>
                        <td><?php echo $P_Producto; ?></td>
                        <td class="text-end"><?php echo $P_Cantidad; ?></td>
                        <td class="text-end"><?php echo $P_Beneficios; ?></td>
                        <td>
                            <?php
                            //Solo so esta abierto
                            if(isset($data['rowData']['idEstado'])&&$data['rowData']['idEstado']==1){
                                //Variables
                                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idExistencia']);
                                $Entidad     = addslashes($Entidad);
                                //Variables para envío whatsapp
                                $WSP_ExistenciaID  = $crud['idExistencia'];
                                $WSP_encryptedId   = $data['Fnc_Codification']->simpleEncode($crud['idExistencia'], 8080);
                                $WSP_Fono          = $data['Fnc_DataNumbers']->normalizarPhone($crud['EntidadFono1']);
                                /*****************************/
                                echo '<div class="btn-group" role="group">';
                                    switch ($crud['idEstadoPartida']) {
                                        /*************************************/
                                        //Recién Creado
                                        case 1:
                                            echo '
                                            <button type="button" onclick="tabPartidasEdit(\''.$encryptedId.'\')"                    class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                                            <button type="button" onclick="tabPartidasDel( \''.$encryptedId.'\', \''.$Entidad.'\')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>';
                                            if(isset($crud['EntidadFono1'])&&$crud['EntidadFono1']!=''){
                                                echo '<button type="button" onclick="tabPartidasSendInfo(\''.$WSP_ExistenciaID.'\', \''.$EntidadWsp.'\', \''.$WSP_encryptedId.'\', \''.$WSP_Fono.'\')" class="btn btn-success btn-sm tooltiplink" data-title="Enviar Notificación Manual"><i class="bi bi-cursor"></i> Noti Manual</button>';
                                                if(isset($data['UserData']["Config_WhatsappToken"])&&$data['UserData']["Config_WhatsappToken"]!=''){
                                                    echo '<button type="button" onclick="tabPartidasSendWhatsapp(\''.$encryptedId.'\')" class="btn btn-success btn-sm tooltiplink" data-title="Enviar Notificación por Whatsapp"><i class="bi bi-whatsapp"></i> Noti Whatsapp</button>';
                                                }
                                            }else{
                                                echo '<button type="button" class="btn btn-warning btn-sm tooltiplink" data-title="No existe numero"><i class="bi bi-exclamation-triangle"></i> No existe numero</button>';
                                            }
                                            break;
                                        /*************************************/
                                        //Campaña Enviada
                                        case 2:
                                            echo '<button type="button" onclick="tabPartidasEdit(\''.$encryptedId.'\')" class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i> Editar</button>';
                                            if(isset($crud['EntidadFono1'])&&$crud['EntidadFono1']!=''){
                                                echo '<button type="button" onclick="tabPartidasSendInfo(\''.$WSP_ExistenciaID.'\', \''.$EntidadWsp.'\', \''.$WSP_encryptedId.'\', \''.$WSP_Fono.'\')" class="btn btn-success btn-sm tooltiplink" data-title="Reenviar Enviar Notificación Manual"><i class="bi bi-cursor"></i> Reenviar Noti Manual</button>';
                                                if(isset($data['UserData']["Config_WhatsappToken"])&&$data['UserData']["Config_WhatsappToken"]!=''){
                                                    echo '<button type="button" onclick="tabPartidasSendWhatsapp(\''.$encryptedId.'\')" class="btn btn-success btn-sm tooltiplink" data-title="Reenviar Enviar Notificación por Whatsapp"><i class="bi bi-whatsapp"></i> Reenviar Noti Whatsapp</button>';
                                                }
                                            }else{
                                                echo '<button type="button" class="btn btn-warning btn-sm tooltiplink" data-title="No existe numero"><i class="bi bi-exclamation-triangle"></i> No existe numero</button>';
                                            }
                                            break;
                                        /*************************************/
                                        case 3: //Campaña Revisada
                                        case 4: //Campaña Confirmada
                                            echo '<button type="button" onclick="tabPartidasEdit(\''.$encryptedId.'\')" class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i> Editar</button>';
                                            break;
                                        /*************************************/
                                        //Campaña Rechazada
                                        case 5:
                                            //Nada
                                            break;
                                    }
                                    //Se verifica si hay fono
                                    if(isset($crud['EntidadFono1'])&&$crud['EntidadFono1']!=''){
                                        echo '<button type="button" onclick="tabPartidasCall( \''.$crud['EntidadFono1'].'\', \''.$Entidad.'\')"  class="btn btn-secondary btn-sm tooltiplink" data-title="Llamar al Telefono '.$crud['EntidadFono1'].'"><i class="bi bi-telephone"></i> Llamar</button>';
                                        //echo '<a class="btn btn-secondary btn-sm tooltiplink" data-title="Llamar al Telefono '.$crud['EntidadFono1'].'" href="tel:'.$crud['EntidadFono1'].'"><i class="bi bi-telephone"></i> Llamar</a>';
                                    }

                                echo '</div>';
                            } ?>
                        </td>
                    </tr>
                    <?php
                }
                /*****************************************/
                //SUBTOTALES
                //Cantidad Productos
                for ($i=1; $i <= 10; $i++) {
                    if(isset($SubTotalNombre[$i])&&$SubTotalNombre[$i]!=''){
                        echo '<tr class="table-info"><td colspan="7"><strong>'.$SubTotalNombre[$i].'</strong></td></tr>';
                        //Estados
                        for ($x=0; $x <= 6; $x++) {
                            if(isset($SubTotalProdEstado[$i][$x])&&$SubTotalProdEstado[$i][$x]!=''){
                                echo '
                                <tr>
                                    <td class="text-end" colspan="4">'.$SubTotalProdEstado[$i][$x].'</td>
                                    <td class="text-end">'.$data['Fnc_DataNumbers']->Cantidades($SubTotalProdCantidad[$i][$x], 2).' '.$SubTotalUnimed[$i].'</td>
                                    <td class="text-end">'.$data['Fnc_DataNumbers']->Valores($SubTotalProdValor[$i][$x], 2).'</td>
                                    <td></td>
                                </tr>';
                            }
                        }
                        echo '
                        <tr>
                            <td class="text-end" colspan="4"><strong>Total General</strong></td>
                            <td class="text-end">'.$data['Fnc_DataNumbers']->Cantidades($SubTotalCantidad[$i], 2).' '.$SubTotalUnimed[$i].'</td>
                            <td class="text-end">'.$data['Fnc_DataNumbers']->Valores($SubTotalValor[$i], 2).'</td>
                            <td></td>
                        </tr>';
                    }
                }
            }
        }
        /*****************************************/
        //TOTALES
        echo '<tr><td colspan="7"><br/><br/><br/><br/><br/><br/></td></tr>';
        //Cantidad Productos
        for ($i=1; $i <= 10; $i++) {
            if(isset($TotalNombre[$i])&&$TotalNombre[$i]!=''){
                echo '<tr class="table-secondary"><td colspan="7"><strong>'.$TotalNombre[$i].'</strong></td></tr>';
                //Estados
                for ($x=0; $x <= 6; $x++) {
                    if(isset($TotalProdEstado[$i][$x])&&$TotalProdEstado[$i][$x]!=''){
                        echo '
                        <tr>
                            <td class="text-end" colspan="4">'.$TotalProdEstado[$i][$x].'</td>
                            <td class="text-end">'.$data['Fnc_DataNumbers']->Cantidades($TotalProdCantidad[$i][$x], 2).' '.$TotalUnimed[$i].'</td>
                            <td class="text-end">'.$data['Fnc_DataNumbers']->Valores($TotalProdValor[$i][$x], 2).'</td>
                            <td></td>
                        </tr>';
                    }
                }
                echo '
                <tr>
                    <td class="text-end" colspan="4"><strong>Total General</strong></td>
                    <td class="text-end">'.$data['Fnc_DataNumbers']->Cantidades($TotalCantidad[$i], 2).' '.$TotalUnimed[$i].'</td>
                    <td class="text-end">'.$data['Fnc_DataNumbers']->Valores($TotalValor[$i], 2).'</td>
                    <td></td>
                </tr>';
            }
        } ?>

    </tbody>
</table>





<script>
    $(document).ready(function(){
        $("#filterTableDataPartidas").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#tableDataPartidas tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>