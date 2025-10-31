<table class="table table-sm table-hover datatable" id="tableDataPartidasFin">
    <thead>
        <tr>
            <th>Ubicacion</th>
            <th>Entidad</th>
            <th>Estado</th>
            <th>Producto</th>
            <th class="text-end" width="120">Cantidad</th>
            <th class="text-end" width="120">Valor</th>
            <th>Facturacion</th>
            <th>Acciones</th>
        </tr>
        <tr role="row">
            <th colspan="8"><input class="form-control" id="filterTableDataPartidasFin" type="text" placeholder="Filtrar Datos.."></th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrPartidas'])&&!empty($data['arrPartidas'])){
            //Cantidad Productos
            for ($i=1; $i <= 10; $i++) {
                $SubTotalNombre[$i]   = '';
                $SubTotalUnimed[$i]   = '';
                $SubTotalCantidad[$i] = 0;
                $SubTotalValor[$i]    = 0;
            }
            //filtro
            $newData  = $data['Fnc_CommonData']->agruparPorClave ($data['arrPartidas'], 'VentaFecha' );
            $ProdData = $data['Fnc_CommonData']->agruparPorClave ($data['arrPartidasProd'], 'idExistencia' );
            //Recorro
            foreach ($newData AS $VentaFecha=>$datos){
                //imprimimos la categoría
                echo '<tr class="table-secondary"><td colspan="8"><strong>Ventas del '.$data['Fnc_DataDate']->fechaEstandar($VentaFecha).'</strong></td></tr>';
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
                    //Se filtran productos
                    $P_Producto   = '';
                    $P_Cantidad   = '';
                    $P_Beneficios = '';
                    foreach ($ProdData[$crud['idExistencia']] AS $dataP){
                        $P_Producto   .= $dataP['Producto'].'<br/>';
                        $P_Cantidad   .= $data['Fnc_DataNumbers']->Cantidades($dataP['Cantidad'], 2).' '.$dataP['Unimed'].'<br/>';
                        $P_Beneficios .= $data['Fnc_DataNumbers']->Valores($dataP['Beneficios'], 2).'<br/>';
                        //Variables
                        $SubTotalNombre[$dataP['idProducto']]   = $dataP['Producto'];
                        $SubTotalUnimed[$dataP['idProducto']]   = $dataP['Unimed'];
                        $SubTotalCantidad[$dataP['idProducto']] = $SubTotalCantidad[$dataP['idProducto']] + $dataP['Cantidad'];
                        $SubTotalValor[$dataP['idProducto']]    = $SubTotalValor[$dataP['idProducto']]    + $dataP['Beneficios'];
                    }
                    ?>
                    <tr>
                        <td><?php echo $crud['EntidadSector'].'<br>'.$crud['EntidadDireccion']; ?></td>
                        <td><?php echo $Entidad; ?></td>
                        <td><?php echo $crud['EstadoPartida']; ?></td>
                        <td><?php echo $P_Producto; ?></td>
                        <td class="text-end"><?php echo $P_Cantidad; ?></td>
                        <td class="text-end"><?php echo $P_Beneficios; ?></td>
                        <td>
                            <?php
                            if(isset($crud['idFacturacion'])&&$crud['idFacturacion']!=0){
                                //Variables
                                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idFacturacion']);
                                //imprimo
                                echo $crud['DocumentoNombre'].' '.($crud['DocumentoN_Doc'] ?? 'nRef '.$crud['idFacturacion']); ?>
                                <div class="btn-group" role="group">
                                    <button type="button" onclick="tabPartidasFinView('<?php echo $encryptedId; ?>')" class="btn btn-primary btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                                </div>
                            <?php } ?>
                        </td>
                        <td>
                            <?php
                            if(isset($data['rowData']['idEstado'])&&$data['rowData']['idEstado']==1){
                                //Variables
                                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idExistencia']); ?>
                                <div class="btn-group" role="group">
                                    <button type="button" onclick="tabPartidasFinEdit('<?php echo $encryptedId; ?>')"                             class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                                    <button type="button" onclick="tabPartidasFinDel( '<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                                </div>
                            <?php } ?>
                        </td>
                    </tr>
                <?php
                }
            }
        }
        /*****************************************/
        //SUBTOTALES
        echo '<tr><td colspan="8"><br/><br/><br/><br/><br/><br/></td></tr>';
        echo '<tr class="table-secondary"><td colspan="8"><strong>Totales</strong></td></tr>';
        //Cantidad Productos
        for ($i=1; $i <= 10; $i++) {
            if(isset($SubTotalNombre[$i])&&$SubTotalNombre[$i]!=''){
                echo '
                <tr>
                    <td class="text-end" colspan="4"><strong>'.$SubTotalNombre[$i].'</strong></td>
                    <td class="text-end">'.$data['Fnc_DataNumbers']->Cantidades($SubTotalCantidad[$i], 2).' '.$SubTotalUnimed[$i].'</td>
                    <td class="text-end">'.$data['Fnc_DataNumbers']->Valores($SubTotalValor[$i], 2).'</td>
                    <td></td>
                    <td></td>
                </tr>';
            }
        } ?>
    </tbody>
</table>

<script>
    $(document).ready(function(){
        $("#filterTableDataPartidasFin").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#tableDataPartidasFin tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>