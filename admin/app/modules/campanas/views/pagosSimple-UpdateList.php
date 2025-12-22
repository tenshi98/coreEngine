<form id="FormPagoSimple" name="FormPagoSimple" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">

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
                                        <th>Pago</th>
                                        <th width="160">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    //Verifico si hay datos
                                    if(is_array($data['arrList'])&&!empty($data['arrList'])){
                                        //Recorro
                                        foreach ($data['arrList'] as $crud){
                                            //Para la lista
                                            $Documento    = $crud['Documento'].' '.($crud['N_Doc'] ?? 'nRef '.$crud['idFacturacion']);
                                            $Documento   .= ' Fecha '.$data['Fnc_DataDate']->fechaEstandar($crud['Creacion_fecha']);
                                            $Documento   .= ' (Monto: '.$data['Fnc_DataNumbers']->Valores($crud['ValorTotal'], 0);
                                            $Documento   .= '/Pagado: '.$data['Fnc_DataNumbers']->Valores($crud['MontoPagado'], 0).')';
                                            //Se genera entidad
                                            $Entidad = '';
                                            $Entidad .= !empty($crud['EntidadNick'])
                                                        ? $crud['EntidadNick'].'<br>'
                                                        : '';
                                            $Entidad .= !empty($crud['EntidadNombre'])
                                                        ? $crud['EntidadApellido'].' '.$crud['EntidadNombre']
                                                        : $crud['EntidadRazonSocial'];
                                            //Calculo de la diferencia para el pago
                                            $Diferencia = $crud['ValorTotal'] - $crud['MontoPagado'];
                                            //El id codificado
                                            $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idFacturacion']);
                                            //imprimimos la categoría
                                            echo '
                                            <tr>
                                                <td>'.$Entidad.'</td>
                                                <td>'.$crud['Campana'].'</td>
                                                <td>'.$Documento.'</td>
                                                <td>';
                                                    //datos ocultos
                                                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idFacturacion[]', 'Value' => $crud['idFacturacion'], 'Required' => 2]);  //ID Cliente
                                                    //se dibujan los inputs
                                                    $data['Fnc_FormInputs']->formInput([  'FormAling' => 4,'FormCol' => 12,'FormType' => 6, 'Placeholder' => 'Valor Total',  'Name' => 'MontoPagado[]',   'Value' => $data['Fnc_DataNumbers']->valoresComparables($Diferencia),'Required' => 2,'Icon' => 'bi bi-currency-dollar']);
                                                    echo '
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" onclick="listTableDataView(\''.$encryptedId.'\')" class="btn btn-primary btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                                                        <button type="button" onclick="delRow(this)"                            class="btn btn-danger  btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                                                    </div>
                                                </td>
                                            </tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php
    //datos ocultos
    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idDocumentoPago', 'Value' => 5,                                        'Required' => 2]);  //Transferencia Bancaria
    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario',       'Value' => $data['UserData']['UserID'],              'Required' => 2]);  //Usuario que lo creo
    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'FechaPago',       'Value' => $data['Fnc_ServerServer']->fechaActual(), 'Required' => 2]);  //Fecha de creacion automatica
    ?>

    <div class="clearfix"></div>
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <a href="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/listAll'; ?>" class="btn btn-danger float-end"><i class="bi bi-arrow-left-circle"></i> Volver</a>
        <button type="button" class="btn btn-success" onclick="submitForm(this)"><i class="bx bx-save"></i> Guardar Cambios</button>
    </div>
    <div class="clearfix"></div>

</form>

