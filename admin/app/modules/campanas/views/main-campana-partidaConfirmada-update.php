<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Confirmacion</th>
            <th>Entidad</th>
            <th>Sector</th>
            <th>Direccion</th>
            <th>Campana</th>
            <th>Fecha Campana</th>
            <th>Producto</th>
            <th>Cantidad</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['MainViewData']['Data_PartidaConfirmada'])){
            //Se separan los datos
            $PartidaConfirmada = $data['Fnc_CommonData']->agruparPorClave ($data['MainViewData']['Data_PartidaConfirmada'], 'idExistencia' );
            //Se recorren los datos
            foreach ($PartidaConfirmada AS $Existencia=>$datos){
                //Se obtiene el nombre o la razón social
                $Entidad  = '';
                $Entidad .= !empty($datos[0]['EntidadNick'])
                            ? $datos[0]['EntidadNick'].'<br>'
                            : '';
                $Entidad .= !empty($datos[0]['EntidadNombre'])
                            ? $datos[0]['EntidadApellido'].' '.$datos[0]['EntidadNombre']
                            : $datos[0]['EntidadRazonSocial'];
                //var
                $Tab_Producto = '';
                $Tab_Cantidad = '';
                //Recorro
                foreach($datos AS $crud){
                    $Tab_Producto .= $crud['ProductoNombre'].'<br>';
                    $Tab_Cantidad .= $data['Fnc_DataNumbers']->Cantidades($crud['Cantidad'], 2).' '.$crud['ProductoUniMed'].'<br>';
                }
                echo '
                    <tr>
                        <td>'.$data['Fnc_DataDate']->fechaEstandar($datos[0]['ConfirmacionFecha']).'<br/>'.$datos[0]['ConfirmacionHora'].'</td>
                        <td>'.$Entidad.'</td>
                        <td>'.$datos[0]['EntidadSector'].'</td>
                        <td>'.$datos[0]['EntidadDireccion'].'</td>
                        <td>'.$datos[0]['CampanaNombre'].'</td>
                        <td>'.$data['Fnc_DataDate']->fechaEstandar($datos[0]['Fecha']).'</td>
                        <td>'.$Tab_Producto.'</td>
                        <td>'.$Tab_Cantidad.'</td>
                    </tr>';
                }
            } ?>
    </tbody>
</table>