<section class="section" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $data['TableTitle']; ?></h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Entidad</th>
                                    <th>Campaña</th>
                                    <th>Documento</th>
                                    <th width="160">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                //Verifico si hay datos
                                if(is_array($data['arrList'])&&!empty($data['arrList'])){
                                    //filtro
                                    $newData = $data['Fnc_CommonData']->agruparPorClave ($data['arrList'], 'idEntidad' );
                                    //Recorro
                                    foreach ($newData AS $EntidadID=>$datos){
                                        //Variables
                                        $Entidad     = '';
                                        $Documento   = '';
                                        $WhatsappEnt = '';
                                        $WhatsappDoc = '';
                                        $WhatsappVal = 0;
                                        $Fono        = $data['Fnc_DataNumbers']->normalizarPhone($datos[0]['EntidadFono1']);
                                        //se recorren los datos dentro de la categoría
                                        foreach ($datos AS $crud){
                                            //Para la lista
                                            $Documento   .= $crud['Documento'].' '.($crud['N_Doc'] ?? 'nRef '.$crud['idFacturacion']);
                                            $Documento   .= ' Fecha '.$data['Fnc_DataDate']->fechaEstandar($crud['Creacion_fecha']);
                                            $Documento   .= ' (Monto: '.$data['Fnc_DataNumbers']->Valores($crud['ValorTotal'], 0);
                                            $Documento   .= '/Pagado: '.$data['Fnc_DataNumbers']->Valores($crud['MontoPagado'], 0).')';
                                            $Documento   .= '<br>';
                                            //Para el whatsapp
                                            //Si es una persona natural
                                            if(isset($crud['EntidadNombre'])&&$crud['EntidadNombre']!=''){
                                                $WhatsappDoc   .= 'Compra con fecha '.$data['Fnc_DataDate']->fechaEstandar($crud['Creacion_fecha']);
                                                $WhatsappDoc   .= ' (Monto: '.$data['Fnc_DataNumbers']->Valores($crud['ValorTotal'], 0);
                                                $WhatsappDoc   .= '/Pagado: '.$data['Fnc_DataNumbers']->Valores($crud['MontoPagado'], 0).')';
                                                $WhatsappDoc   .= '%0A';
                                            //si es empresa
                                            }else{
                                                $WhatsappDoc   .= $crud['Documento'].' '.($crud['N_Doc'] ?? 'nRef '.$crud['idFacturacion']);
                                                $WhatsappDoc   .= ' Fecha '.$data['Fnc_DataDate']->fechaEstandar($crud['Creacion_fecha']);
                                                $WhatsappDoc   .= ' (Monto: '.$data['Fnc_DataNumbers']->Valores($crud['ValorTotal'], 0);
                                                $WhatsappDoc   .= '/Pagado: '.$data['Fnc_DataNumbers']->Valores($crud['MontoPagado'], 0).')';
                                                $WhatsappDoc   .= '%0A';
                                            }
                                            //Se guarda monto
                                            $WhatsappVal = $WhatsappVal + ($crud['ValorTotal'] - $crud['MontoPagado']);
                                        }
                                        //Se formatea
                                        $WhatsappValTotal = $data['Fnc_DataNumbers']->Valores($WhatsappVal, 0);
                                        //Se genera entidad
                                        if (isset($datos[0]['EntidadNick'])&&$datos[0]['EntidadNick']!='') {
                                            $Entidad    .= $datos[0]['EntidadNick'].'<br>';
                                            $WhatsappEnt = $datos[0]['EntidadNick'];
                                        }else{
                                            if(isset($datos[0]['EntidadNombre'])&&$datos[0]['EntidadNombre']!=''){
                                                $WhatsappEnt = $datos[0]['EntidadNombre'].' '.$datos[0]['EntidadApellido'];
                                            }else{
                                                $WhatsappEnt = $datos[0]['EntidadRazonSocial'];
                                            }
                                        }
                                        if(isset($datos[0]['EntidadNombre'])&&$datos[0]['EntidadNombre']!=''){
                                            $Entidad .= $datos[0]['EntidadApellido'].' '.$datos[0]['EntidadNombre'];
                                        }else{
                                            $Entidad .= $datos[0]['EntidadRazonSocial'];
                                        }
                                        //Se genera boton
                                        if(isset($datos[0]['EntidadFono1'])&&$datos[0]['EntidadFono1']!=''){
                                            $Boton = '<button type="button" onclick="whatsappSendInfo(\''.$WhatsappEnt.'\', \''.$Fono.'\', \''.$WhatsappDoc.'\', \''.$WhatsappValTotal.'\')" class="btn btn-success btn-sm tooltiplink" data-title="Enviar Notificación Manual"><i class="bi bi-cursor"></i> Notificación Manual</button>';
                                        }else{
                                            $Boton = '<button type="button" class="btn btn-warning btn-sm tooltiplink" data-title="No existe numero"><i class="bi bi-exclamation-triangle"></i> No existe numero</button>';
                                        }
                                        //imprimimos la categoría
                                        echo '
                                        <tr>
                                            <td>'.$Entidad.'</td>
                                            <td>'.$datos[0]['Campana'].'</td>
                                            <td>'.$Documento.'</td>
                                            <td> <div class="btn-group" role="group">'.$Boton.'</div></td>
                                        </tr>';
                                    }
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="clearfix"></div>
<div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <a href="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/listAll'; ?>" class="btn btn-danger float-end"><i class="bi bi-arrow-left-circle"></i> Volver</a>
</div>
<div class="clearfix"></div>
