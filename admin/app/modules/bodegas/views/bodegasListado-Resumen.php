<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">
        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-edit"><i class="bi bi-pencil-square"></i> Editar Datos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-img"><i class="bi bi-image"></i> Cambiar Imagen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-obs" onclick="tabObsLoadList()"><i class="bi bi-chat-dots"></i> Observaciones</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-pasillos"><i class="bi bi-image"></i> Pasillos</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#resumen-visual"><i class="bi bi-image"></i> Visual</button></li>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">
                    <?php require_once('bodegasListado-Resumen-Update.php'); ?>
                </div>

                <div class="tab-pane fade" id="resumen-edit">

                    <form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                                <?php
                                //se dibujan los inputs
                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Básicos', 'Clase' => 'box-title text-color-red-dark']);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Nombre',     'Name'  => 'Nombre',    'Id'  => 'Edit_Nombre',     'Value'  => ($data['rowData']['Nombre'] ?? ''),    'Required'  => 2]);
                                $data['Fnc_FormInputs']->formSelectDepend([           'Placeholder1' => 'Ciudad',     'Name1' => 'idCiudad',  'Id1' => 'Edit_idCiudad',   'Value1' => ($data['rowData']['idCiudad'] ?? ''),  'Required1' => 1,'arrData1' => $data['arrCiudad'],
                                                                                      'Placeholder2' => 'Comuna',     'Name2' => 'idComuna',  'Id2' => 'Edit_idComuna',   'Value2' => ($data['rowData']['idComuna'] ?? ''),  'Required2' => 1,'arrData2' => $data['arrComuna']]);
                                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Dirección',  'Name'  => 'Direccion', 'Id'  => 'Edit_Direccion',  'Value'  => ($data['rowData']['Direccion'] ?? ''), 'Required'  => 1,'Icon' => 'bi bi-geo-alt-fill']);

                                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Administración', 'Clase' => 'box-title text-color-red-dark']);
                                $data['Fnc_FormInputs']->formSelect([  'Placeholder' => 'Estado', 'Name' => 'idEstado',  'Id' => 'Edit_idEstado',  'Value'  => ($data['rowData']['idEstado'] ?? ''),'Required' => 2,'arrData' => $data['arrEstado']]);

                                //datos ocultos
                                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idBodegas','Value' => $data['rowData']['idBodegas'],'Required' => 2]);
                                ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <div class="tab-pane fade" id="resumen-img">
                    <div class="d-flex justify-content-center pt-4">
                        <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
                            <h4 class="title_h4 box-title text-color-red-dark">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                    Imagen de <?php echo $data['rowData']['Nombre']; ?>
                                </div>
                            </h4>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <?php
                    if(isset($data['rowData']['Direccion_img'])&&$data['rowData']['Direccion_img']!=''){ ?>
                        <div class="d-flex justify-content-center pt-4">
                            <div class="col-xs-12 col-sm-8 col-md-6 col-lg-6 col-xl-4 col-xxl-3">
                                <div class="d-flex justify-content-center">
                                    <img src="<?php echo $data['UserData']['MainPathUrl'].$data['rowData']['Direccion_img']; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
                                </div>
                                <div class="d-flex justify-content-center pt-2">
                                    <button  onclick="delIMG('<?php echo $data['rowData']['Direccion_img']; ?>')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Borrar Imagen</button>
                                </div>
                            </div>
                        </div>
                    <?php }else{ ?>
                        <div class="d-flex justify-content-center pt-3">
                            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-xl-6 col-xxl-5">
                                <?php $data['Fnc_FormInputs']->formUploadIMG(['Name' => 'Direccion_img','URL' => $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update','ExtraData' => '"idBodegas": '.$data['rowData']['idBodegas']]);?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="tab-pane fade" id="resumen-obs">
                    <h5 class="text-color-red-dark">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Observaciones de <?php echo $data['rowData']['Nombre']; ?>
                            <button type="button" class="btn btn-success"  onclick="tabObsNew('<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idBodegas']); ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabObsDataTable">

                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-pasillos">
                    <h5 class="text-color-red-dark">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Pasillos de <?php echo $data['rowData']['Nombre']; ?>
                            <button type="button" class="btn btn-success" onclick="addEditBlock()"><i class="bi bi-plus-lg me-1"></i> Agregar Pasillo</button>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div id="editBlocksContainer">

                    </div>
                </div>

                <div class="tab-pane fade" id="resumen-visual">
                    <h5 class="text-color-red-dark">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Visual de <?php echo $data['rowData']['Nombre']; ?>
                            <button type="button" class="btn btn-success"  onclick="tabObsNew('<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idBodegas']); ?>')"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabObsDataTable">

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<div class="clearfix"></div>
<div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <a href="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/listAll'; ?>" class="btn btn-danger float-end"><i class="bi bi-arrow-left-circle"></i> Volver</a>
</div>
<div class="clearfix"></div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    /******************************************/
    $("#FormEditData").submit(function(e) {
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
        //verifico el resultado
        if(validatorResult.valid===false){
            return !!validatorResult.valid;
        }else{
            // Si ya se está ejecutando, salimos
            if (ejecutandoForm.valor) return;
            //Cambio los valores
            ejecutandoForm.valor = true;
            //Ejecucion normal
            e.preventDefault();
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Metodo      = 'POST';
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update'; ?>';
            let Informacion = $("#FormEditData").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#resumen', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumenUpdate/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idBodegas']); ?>'},
                ],
                showNoti:'Datos Editados Correctamente',
                triggerTab:'.nav-tabs button[data-bs-target="#resumen"]',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

    /*********************************************************************/
    /*                             IMAGENES                              */
    /*********************************************************************/
    /******************************************/
    function delIMG(File) {
        Swal.fire({
            title: "Borrar Imagen",
            text: "Esta a punto de borrar la imagen, ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, borrar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'PUT';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/delFiles'; ?>';
                let Informacion = {
                    "idBodegas": <?php echo $data['rowData']['idBodegas']; ?>,
                    "Direccion_img": File
                };
                const Options     = {
                    Destino:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idBodegas']); ?>',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }

    /*********************************************************************/
    /*                          OBSERVACIONES                            */
    /*********************************************************************/
    // Variables
    let ObsLoad = 0;
    /******************************************/
    function tabObsLoadList() {
        //Comparo
        if(ObsLoad===0){
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#tabObsDataTable';
            let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idBodegas']); ?>';
            const Options = {
                closeObject:'#PDloader',
                refreshTables:'true',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
            //Indico que esta cargado
            ObsLoad = 1;
        }
    }
    /******************************************/
    function tabObsNew(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/new/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabObsView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabObsEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent-lg';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal-lg',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tabObsDel(ID, Dato) {
        Swal.fire({
            title: "Borrar Dato",
            text: "Esta a punto de eliminar el dato " + Dato + ", ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, borrar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result2) => {
            if (result2.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'DELETE';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones'; ?>';
                let Informacion = {"idObservaciones": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#tabObsDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/observaciones/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idBodegas']); ?>', refreshTbl:'true'}
                    ],
                    showNoti:'Dato Borrado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }


    // ============================================================
    // Simulated PHP Backend Data
    // In a real app, this would come from an API/PHP endpoint
    // ============================================================
    let pasillos = [
        { id: 1, name: 'Pasillo 1', filas: '4', columnas: '10', consolidado: 'No' },
        { id: 2, name: 'Pasillo 2', filas: '2', columnas: '10', consolidado: 'No' },
        { id: 3, name: 'Pasillo 3', filas: '2', columnas: '10', consolidado: 'No' },
        { id: 4, name: 'Pasillo 4', filas: '3', columnas: '10', consolidado: 'No' },
    ];

    let nextId = 5;

    // ============================================================
    // EDIT VIEW
    // ============================================================
    let editBlocks = [];

    function initEditView() {
        editBlocks = pasillos.map(p => ({ ...p }));
        renderEditBlocks();
    }

    function renderEditBlocks() {
        const container = document.getElementById('editBlocksContainer');
        if (editBlocks.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-plus-circle"></i>
                    <h5>No hay pasillos para editar</h5>
                    <p>Agrega pasillos desde el formulario de creación.</p>
                </div>`;
            return;
        }

        container.innerHTML = editBlocks.map((block, index) => `
            <div class="card" id="editBlock_${index}" data-index="${index}">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill bg-primary block-number">${index + 1}</span>
                            <span class="fw-bold text-warning">
                                <i class="bi bi-pencil-fill me-1"></i>${escapeHtml(block.name || 'Sin nombre')}
                            </span>
                            ${block.id ? `<small class="text-muted">(ID: ${block.id})</small>` : '<small class="text-muted">(Nuevo)</small>'}
                        </div>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-outline-success btn-move-up" onclick="moveEditBlock(${index}, -1)" title="Mover arriba" ${index === 0 ? 'disabled' : ''}>
                                <i class="bi bi-chevron-up"></i>
                            </button>
                            <button type="button" class="btn btn-outline-success btn-move-down" onclick="moveEditBlock(${index}, 1)" title="Mover abajo" ${index === editBlocks.length - 1 ? 'disabled' : ''}>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-remove ms-2" onclick="removeEditBlock(${index})" title="Eliminar">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nombre</label>
                            <input type="text" class="form-control" placeholder="Ej: Pasillo 1"
                                value="${escapeHtml(block.name)}"
                                onchange="editBlocks[${index}].name = this.value">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Filas</label>
                            <input type="number" class="form-control" min="1" placeholder="4"
                                value="${escapeHtml(block.filas)}"
                                onchange="editBlocks[${index}].filas = this.value">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Columnas</label>
                            <input type="number" class="form-control" min="1" placeholder="10"
                                value="${escapeHtml(block.columnas)}"
                                onchange="editBlocks[${index}].columnas = this.value">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Consolidado</label>
                            <select class="form-select" onchange="editBlocks[${index}].consolidado = this.value">
                                <option value="No" ${block.consolidado === 'No' ? 'selected' : ''}>No</option>
                                <option value="Sí" ${block.consolidado === 'Sí' ? 'selected' : ''}>Sí</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="w-100 text-center p-2 rounded" style="background: #fff3e0;">
                                <small class="text-muted d-block">Posición</small>
                                <strong class="text-warning fs-5">${index + 1}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function addEditBlock() {
        editBlocks.push({ id: null, name: '', filas: '', columnas: '', consolidado: 'No' });
        renderEditBlocks();
        setTimeout(() => {
            const newBlock = document.getElementById(`editBlock_${editBlocks.length - 1}`);
            if (newBlock) {
                newBlock.classList.add('highlight-up');
                newBlock.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 50);
        showToast('Bloque agregado', 'success');
    }

    function removeEditBlock(index) {
        editBlocks.splice(index, 1);
        renderEditBlocks();
        showToast('Bloque eliminado', 'info');
    }

    function moveEditBlock(index, direction) {
        const newIndex = index + direction;
        if (newIndex < 0 || newIndex >= editBlocks.length) return;

        // Sync current values
        syncEditInputs();

        const temp = editBlocks[index];
        editBlocks[index] = editBlocks[newIndex];
        editBlocks[newIndex] = temp;

        renderEditBlocks();

        setTimeout(() => {
            const block = document.getElementById(`editBlock_${newIndex}`);
            if (block) {
                block.classList.add(direction < 0 ? 'highlight-up' : 'highlight-down');
            }
        }, 50);
    }

    function syncEditInputs() {
        editBlocks.forEach((block, i) => {
            const container = document.getElementById(`editBlock_${i}`);
            if (container) {
                const inputs = container.querySelectorAll('input, select');
                if (inputs[0]) block.name = inputs[0].value;
                if (inputs[1]) block.filas = inputs[1].value;
                if (inputs[2]) block.columnas = inputs[2].value;
                if (inputs[3]) block.consolidado = inputs[3].value;
            }
        });
    }

    function saveEdited() {
        syncEditInputs();

        // Validate
        let valid = true;
        const errors = [];

        editBlocks.forEach((block, i) => {
            if (!block.name.trim()) {
                errors.push(`Bloque ${i + 1}: El nombre es requerido`);
                valid = false;
            }
            if (!block.filas || parseInt(block.filas) < 1) {
                errors.push(`Bloque ${i + 1}: Las filas deben ser mayor a 0`);
                valid = false;
            }
            if (!block.columnas || parseInt(block.columnas) < 1) {
                errors.push(`Bloque ${i + 1}: Las columnas deben ser mayor a 0`);
                valid = false;
            }
        });

        if (!valid) {
            errors.forEach(err => showToast(err, 'danger'));
            return;
        }

        // Save
        pasillos = editBlocks.map(block => ({
            id: block.id || nextId++,
            name: block.name.trim(),
            filas: block.filas,
            columnas: block.columnas,
            consolidado: block.consolidado
        }));

        showToast('Cambios guardados exitosamente', 'success');
        showView('list');
    }

    // ============================================================
    // DELETE
    // ============================================================
    function confirmDelete(id) {
        const pasillo = pasillos.find(p => p.id === id);
        if (!pasillo) return;

        const modal = document.getElementById('confirmModal');
        modal.innerHTML = `
            <div class="confirm-overlay" id="confirmOverlay">
                <div class="card" style="max-width: 420px; width: 90%;">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">¿Eliminar Pasillo?</h5>
                        <p class="text-muted mb-4">Se eliminará <strong>${escapeHtml(pasillo.name)}</strong> permanentemente.</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <button class="btn btn-outline-secondary px-4" onclick="closeConfirm()">Cancelar</button>
                            <button class="btn btn-danger px-4" onclick="deletePasillo(${id})">
                                <i class="bi bi-trash me-1"></i>Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function closeConfirm() {
        document.getElementById('confirmModal').innerHTML = '';
    }

    function deletePasillo(id) {
        pasillos = pasillos.filter(p => p.id !== id);
        closeConfirm();
        showToast('Pasillo eliminado correctamente', 'success');
        renderList();
    }

    // ============================================================
    // TOAST NOTIFICATIONS
    // ============================================================
    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const icons = {
            success: 'bi-check-circle-fill',
            danger: 'bi-exclamation-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };
        const bgColors = {
            success: '#2e7d32',
            danger: '#c62828',
            warning: '#e65100',
            info: '#1565c0'
        };

        const toastEl = document.createElement('div');
        toastEl.className = 'toast show mb-2';
        toastEl.setAttribute('role', 'alert');
        toastEl.innerHTML = `
            <div class="toast-body d-flex align-items-center gap-2 text-white" style="background: ${bgColors[type] || bgColors.info}; border-radius: 8px;">
                <i class="bi ${icons[type] || icons.info}"></i>
                <span>${message}</span>
                <button type="button" class="btn-close btn-close-white ms-auto" onclick="this.closest('.toast').remove()"></button>
            </div>`;
        container.appendChild(toastEl);

        setTimeout(() => {
            toastEl.classList.remove('show');
            setTimeout(() => toastEl.remove(), 300);
        }, 3500);
    }

    // ============================================================
    // HELPERS
    // ============================================================
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    // ============================================================
    // INIT
    // ============================================================
    document.addEventListener('DOMContentLoaded', () => {
        initEditView();
    });



</script>
