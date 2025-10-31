<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>#</th>
            <th>Fecha</th>
            <th>Bodegas</th>
            <th>Observacion</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrList'])&&!empty($data['arrList'])){
            //Recorro
            foreach($data['arrList'] AS $crud){
                //Se verifica movimiento
                switch ($data['idTipoIngreso']) {
                    case 1: $Movimiento = $crud['BodegaIngreso']; break;                             //Ingreso
                    case 2: $Movimiento = $crud['BodegaEgreso']; break;                              //Egreso
                    case 3: $Movimiento = $crud['BodegaEgreso'].' a '.$crud['BodegaIngreso']; break; //Traspaso
                }
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idMovimiento']);
                $level       = $data['UserAccess']['LevelAccess'];
                $route       = $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$encryptedId;
                $Entidad     = addslashes($Movimiento); ?>
                <tr>
                    <td><?php echo 'nRef '.$crud['idMovimiento']; ?></td>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Creacion_fecha']).'|'.$crud['Creacion_hora']; ?></td>
                    <td><?php echo $Movimiento; ?></td>
                    <td><?php echo $crud['Observaciones']; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <?php
                            //Valido
                            if ($level >= 1) {echo '<button type="button" onclick="listTableDataView(\''.$encryptedId.'\')"                  class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>';}
                            if ($level >= 2) {echo '<a href="'.$route.'"                                                                     class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></a>';}
                            if ($level >= 4) {echo '<button type="button" onclick="listTableDataDel(\''.$encryptedId.'\', \''.$Entidad.'\')" class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>';}
                            ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>