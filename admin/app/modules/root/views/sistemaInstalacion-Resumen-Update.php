<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table">
    <thead>
        <tr>
            <th scope="col">Item</th>
            <th scope="col">Descripcion</th>
            <th scope="col">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['arrModules'] as $module){
            //Contador dependencias no instaladas
            $CountDepends = 0;
            //Imprimo
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
                        foreach ($module['Dependencias'] as $mod){
                            //Se verifica si existe
                            if(isset($mod['Numero'])&&$mod['Numero']!=0){
                                $depInstal = '<span class="badge-sp1 badge-sp1-bg-success">Instalado</span>';
                            }else{
                                $depInstal = '<span class="badge-sp1 badge-sp1-bg-danger">No Instalado</span>';
                                $CountDepends++;
                            }
                            //se escribe
                            echo '<br>'.$mod['Nombre'].' '.$depInstal;
                        }
                    }else{
                        //se escribe
                        echo '<br>Ninguna';
                    }
                    echo '
                </td>
                <td>
                    <div class="btn-group-vertical" role="group">';
                        if($module['countPermisos']!=0){
                            echo '
                            <button type="button" onclick="uninstallModule(\''.$module['Controller'].'\')" class="btn btn-danger     btn-sm tooltiplink" data-title="Desinstalar Modulo completamente"><i class="bi bi-trash"></i> Desinstalar Modulo</button>
                            <button type="button" onclick="checkModuleData(\''.$module['Controller'].'\')" class="btn btn-primary    btn-sm tooltiplink" data-title="Hacer checkeo instalador vs base datos"><i class="bi bi-eye"></i> Checkear Instalador vs BBDD</button>
                            <button type="button" onclick="checkModuleBBDD(\''.$module['Controller'].'\')" class="btn btn-secondary  btn-sm tooltiplink" data-title="Hacer checkeo base datos vs instalador"><i class="bi bi-eye"></i> Checkear BBDD vs Instalador</button>
                            ';
                        }else{
                            //Si se permite la instalacion
                            if($CountDepends===0){
                                echo '<button type="button" onclick="installModule(\''.$module['Controller'].'\')" class="btn btn-primary btn-sm tooltiplink" data-title="Instalar Modulo en la plataforma"><i class="bi bi-eye"></i> Instalar Modulo</button>';
                            }else{
                                echo '<button type="button" class="btn btn-primary disabled btn-sm tooltiplink" data-title="Instalar Modulo en la plataforma"><i class="bi bi-eye"></i> Instalar Modulo</button>';
                            }
                        }
                    echo '
                    </div>
                </td>
            </tr>';
        } ?>
    </tbody>
</table>
