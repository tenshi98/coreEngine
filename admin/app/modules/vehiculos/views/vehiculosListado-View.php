<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="modal-header">
    <?php
    switch ($data['UserData']["sistemaModalSubtitle"]) {
        case 1:
            echo '
            <h5 class="modal-title">
                <i class="bi bi-card-checklist"></i> Ver Datos
            </h5>';
            break;
        case 2:
            echo '
            <h5 class="modal-title modal-subtitle">
                <div class="icon"><i class="bi bi-card-checklist"></i></div>
                Ver Datos<br>
                <small>Permite visualizar los datos de un elemento existente</small>
            </h5>';
            break;
    } ?>
    <button type="button" class="btn-close" aria-label="Close" data-modal-close></button>
</div>
<div class="modal-body">
    <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
        <li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100 active" id="view_tab_1" data-bs-toggle="tab" data-bs-target="#tab_id_1" type="button" role="tab" aria-controls="tab_id_1" aria-selected="true"><i class="bi bi-card-list"></i> Datos Básicos</button></li>
        <li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100"        id="view_tab_2" data-bs-toggle="tab" data-bs-target="#tab_id_2" type="button" role="tab" aria-controls="tab_id_2" aria-selected="false" tabindex="-1"><i class="bi bi-file-text"></i> Documentos</button></li>
        <li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100"        id="view_tab_3" data-bs-toggle="tab" data-bs-target="#tab_id_3" type="button" role="tab" aria-controls="tab_id_3" aria-selected="false" tabindex="-1"><i class="bi bi-chat-dots"></i> Observaciones</button></li>
    </ul>
    <div class="tab-content pt-2" id="tabId_560_Content">
        <div class="tab-pane fade active show" id="tab_id_1" role="tabpanel" aria-labelledby="view_tab_1">
            <?php require_once('vehiculosListado-Resumen-Update.php'); ?>
        </div>

        <div class="tab-pane fade" id="tab_id_2" role="tabpanel" aria-labelledby="view_tab_2">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                    <h5 class="box-title text-color-red-dark">Documentos</h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <tbody>
                                <?php
                                // Verifico si hay datos
                                if(is_array($data['arrDocumentos'])&&!empty($data['arrDocumentos'])){
                                    // Recorro los datos
                                    foreach($data['arrDocumentos'] as $crud){
                                        $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idDocumentos']);?>
                                        <tr>
                                            <td><?php echo $crud['Tipo']; ?></td>
                                            <td><?php echo $crud['Nombre']; ?></td>
                                            <td>
                                                <?php
                                                echo '<strong>Creacion: </strong>'.$data['Fnc_DataDate']->fechaEstandar($crud['FechaCreacion']);
                                                if(isset($crud['FechaVencimiento'])&&$crud['FechaVencimiento']!='0000-00-00'){echo '<br><strong>Vencimiento: </strong>'.$data['Fnc_DataDate']->fechaEstandar($crud['FechaVencimiento']);}
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" onclick="viewDocumentosView('<?php echo $encryptedId['data']; ?>')"  class="btn btn-primary btn-sm"><i class="bi bi-eye"></i> Ver Archivo</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php }else{
                                    echo '<tr><td>No se encontraron entradas</td></tr>';
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab_id_3" role="tabpanel" aria-labelledby="view_tab_3">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                    <h5 class="box-title text-color-red-dark">Observaciones</h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <tbody>
                                <?php
                                // Verifico si hay datos
                                if(is_array($data['arrObservaciones'])&&!empty($data['arrObservaciones'])){
                                    // Recorro los datos
                                    foreach($data['arrObservaciones'] as $crud){ ?>
                                        <tr>
                                            <td style="width: 100px;"><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['FechaCreacion']); ?></td>
                                            <td><?php echo '<strong>'.$crud['Usuario'].':</strong><br>'.$crud['Observacion']; ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php }else{
                                    echo '<tr><td>No se encontraron entradas</td></tr>';
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
if($data['UserData']["sistemaModalCloseBTN"]==2){
    echo '
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
        </div>
    </div>';
}else{
    echo '<style>.modal-body {max-height: 80vh;}</style>';
} ?>

<script>
    /************************************/
    function viewDocumentosView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#popupModalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/view/'; ?>'+ID;
        const Options = {
            closeObject:'#PDloader',
            callFNC:'openPopupModal',
            callFNCData:'PopupModalLarge',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
</script>
