<section class="section" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between"><?php echo $data['TableTitle']; ?></div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Ruta Web</th>
                                    <th>Estado</th>
                                    <th>Comparado</th>
                                    <th>Existe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                //Variables
                                $arrCompare = array();
                                //Se parsean los datos
                                if(is_array($data['arrModules'])&&!empty($data['arrModules'])){
                                    foreach ($data['arrModules'] AS $key=>$modules){
                                        //Recorro
                                        foreach($modules AS $crud){
                                            if(isset($crud['idMetodo'])&&$crud['idMetodo']!=''){
                                                $arrCompare[$crud['RutaWeb']][$crud['RutaController']]['idMetodo']        = $crud['idMetodo'];
                                                $arrCompare[$crud['RutaWeb']][$crud['RutaController']]['RutaWeb']         = $crud['RutaWeb'];
                                                $arrCompare[$crud['RutaWeb']][$crud['RutaController']]['RutaController']  = $crud['RutaController'];
                                                $arrCompare[$crud['RutaWeb']][$crud['RutaController']]['Descripcion']     = $crud['Descripcion'];
                                                $arrCompare[$crud['RutaWeb']][$crud['RutaController']]['idLevelLimit']    = $crud['idLevelLimit'];
                                                $arrCompare[$crud['RutaWeb']][$crud['RutaController']]['Controller']      = $crud['Controller'];
                                            }
                                        }
                                    }
                                }
                                //Verifico si hay datos
                                if(is_array($data['arrRutas'])&&!empty($data['arrRutas'])){
                                    //Recorro
                                    foreach($data['arrRutas'] AS $crud){
                                        //Conteo
                                        $CountExist = 0;
                                        $CountDif   = 0; ?>
                                        <tr>
                                            <td><?php echo $crud['RutaWeb'].' ('.$crud['RutaController'].')'; ?></td>
                                            <td>
                                                <?php
                                                //Campos
                                                $fields = [
                                                    'idMetodo',
                                                    'RutaWeb',
                                                    'RutaController',
                                                    'Descripcion',
                                                    'idLevelLimit',
                                                    'Controller'
                                                ];
                                                //Se recorren campos
                                                foreach ($fields as $field) {
                                                    if (isset($arrCompare[$crud['RutaWeb']][$crud['RutaController']][$field]) && $arrCompare[$crud['RutaWeb']][$crud['RutaController']][$field] != '') {
                                                        if ($arrCompare[$crud['RutaWeb']][$crud['RutaController']][$field] != $crud[$field]) {
                                                            echo "{$field} es distinto ({$crud[$field]} | {$arrCompare[$crud['RutaWeb']][$crud['RutaController']][$field]})<br/>";
                                                        } else {
                                                            $CountDif++;
                                                        }
                                                    } else {
                                                        $CountExist++;
                                                    }
                                                } ?>
                                            </td>
                                            <td <?php if($CountDif!=6){echo 'class="table-danger"';} ?> ><?php echo $CountDif; ?></td>
                                            <td <?php if($CountExist!=0){echo 'class="table-danger"';} ?> ><?php echo $CountExist; ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

