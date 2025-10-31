<table class="table">
    <thead>
        <tr>
            <th scope="col">Item</th>
            <th scope="col">Descripcion</th>
            <th scope="col">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['arrModules'] AS $module){
            echo '
            <tr>
                <td>
                    <strong>Nombre: </strong><br>
                    <strong>Descripcion: </strong><br>
                    <strong>Dependencias: </strong>
                </td>
                <td>
                    <strong>'.$module['Nombre'].'</strong><br>
                    '.$module['Descripcion'];
                    /******************************************/
                    //Verifico si existe
                    if(isset($module['Dependencias'])&&is_array($module['Dependencias'])){
                        //Se recorren las dependencias
                        foreach ($module['Dependencias'] AS $mod){
                            //Se verifica si existe
                            if(isset($mod['Numero'])&&$mod['Numero']!=0){
                                $depInstal = '<span class="badge bg-success">Instalado</span>';
                            }else{
                                $depInstal = '<span class="badge bg-danger">No Instalado</span>';
                            }
                            //se escribe
                            echo '<br>'.$mod['Nombre'].' '.$depInstal;
                        }
                    }else{
                        //se escribe
                        echo '<br>Ninguna';
                    }

                    //echo '<br>countPermisos:'.$module['countPermisos'];

                    echo '
                </td>
                <td>';
                    if($module['countPermisos']!=0){
                        echo '
                        <div class="btn-group" role="group">
                            <button type="button" onclick="uninstallModule(\''.$module['Controller'].'\')" class="btn btn-danger btn-sm tooltiplink" data-title="Desinstalar Modulo completamente"><i class="bi bi-trash"></i> Desinstalar Modulo</button>
                        </div>';
                    }else{
                        echo '
                        <div class="btn-group" role="group">
                            <button type="button" onclick="installModule(\''.$module['Controller'].'\')" class="btn btn-primary btn-sm tooltiplink" data-title="Instalar Modulo en la plataforma"><i class="bi bi-eye"></i> Instalar Modulo</button>
                        </div>';
                    }
                    echo '
                </td>
            </tr>';
        } ?>
    </tbody>
</table>
