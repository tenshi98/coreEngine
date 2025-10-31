<section class="section" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="row">

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="card aos-init aos-animate" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">
                        <?php echo $data['TableTitle']; ?>
                        <?php if($data['UserAccess']['LevelAccess']>=3){
                            //Se verifica si se permite Administrar Tableros Independiente de las Tareas
                            if($data['UserData']["KanbanTareasAdminTabIndepend"]==2){ ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <?php if(is_array($data['arrList'])){ ?>
                                        <button type="button" class="btn btn-success"   data-bs-toggle="modal" data-bs-target="#newFormModal"><i class="bi bi-file-earmark"></i> Crear Tarea</button>
                                    <?php } ?>
                                </div>
                            <?php //Si se permite junto con la creacion de tareas
                            }else{ ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#newFormModalEstado"><i class="bi bi-file-earmark"></i> Crear Tablero</button>
                                    <?php if(is_array($data['arrList'])){ ?>
                                        <button type="button" class="btn btn-success"   data-bs-toggle="modal" data-bs-target="#newFormModal"><i class="bi bi-file-earmark"></i> Crear Tarea</button>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </h5>
                    <ul class="nav nav-tabs" id="kanban" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="home-tab_kanban_1" data-bs-toggle="tab" data-bs-target="#tab_kanban_1" type="button" role="tab" aria-controls="tab_kanban_1" aria-selected="true">Tablero</button>
                        </li>
                        <li class="nav-item " role="presentation">
                            <button class="nav-link" id="home-tab_kanban_2" data-bs-toggle="tab" data-bs-target="#tab_kanban_2" type="button" role="tab" aria-controls="tab_kanban_2" aria-selected="false" tabindex="-1">Listado</button>
                        </li>
                    </ul>
                    <div class="tab-content pt-2">
                        <div class="tab-content pt-2" id="listTableData">
                            <?php require_once('kanbanTareas-UpdateList.php'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
//Se verifica si se permite Administrar Tableros Independiente de las Tareas
if($data['UserData']["KanbanTareasAdminTabIndepend"]!=2){
    require_once('kanbanEstados-formNew.php');
}
?>
<?php require_once('kanbanTareas-formNew.php'); ?>

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id="modalContent">

        </div>
    </div>
</div>



<script>
    /*********************************************************************/
    /*                        OPCIONES DE LA TABLA                       */
    /*********************************************************************/
    /******************************************/
    function listTableDataView(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/view/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function listTableDataEdit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function listTableDataDel(ID, Dato) {
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
        }).then((result) => {
            if (result.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'DELETE';
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess']; ?>';
                let Informacion = {"idKanban": ID};
                const Options     = {
                    UpdateDiv : [
                        {Div:'#listTableData', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/updateList'; ?>', refreshTbl:'true'}
                    ],
                    showNoti:'Dato Borrado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
    <?php
    //Se verifica si se permite Administrar Tableros Independiente de las Tareas
    if($data['UserData']["KanbanTareasAdminTabIndepend"]!=2){ ?>
        /*********************************************************************/
        /*                        OPCIONES DE LA TABLA                       */
        /*********************************************************************/
        /******************************************/
        function EditTabla(ID) {
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Div       = '#modalContent';
            let URL       = '<?php echo $BASE.'/gestionProyectos/kanban/estados/getID/'; ?>'+ID;
            const Options = {
                showModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            UpdateContentId(Div, URL, Options);
        }
        /******************************************/
        function delTabla(ID, Dato) {
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
                    let Direccion   = '<?php echo $BASE.'/gestionProyectos/kanban/estados'; ?>';
                    let Informacion = {"idKanbanEstado": ID};
                    const Options     = {
                        UpdateDiv : [
                            {Div:'#listTableData', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/updateList'; ?>', refreshTbl:'true'}
                        ],
                        showNoti:'Dato Borrado Correctamente',
                        closeObject:'#PDloader',
                    };
                    //Se envian los datos al formulario
                    SendDataForms(Metodo, Direccion, Informacion, Options);
                }
            });
        }
    <?php } ?>
    /*********************************************************************/
    /*                              TAREAS                               */
    /*********************************************************************/
    /******************************************/
    function tareas_Add(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'Tareas/newData/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function tareas_Edit(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'Tareas/getID/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /*********************************************************************/
    /*                           PARTICIPANTES                           */
    /*********************************************************************/
    /******************************************/
    function participantes_Add(ID) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Div       = '#modalContent';
        let URL       = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'Participantes/newData/'; ?>'+ID;
        const Options = {
            showModal : '#viewModal',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        UpdateContentId(Div, URL, Options);
    }
    /******************************************/
    function participantes_Del(ID, ID2) {
        Swal.fire({
            title: "Borrar Dato",
            text: "Esta a punto de eliminar el dato, ¿Desea continuar?",
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
                let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'Participantes'; ?>';
                let Informacion = {
                    "idParticipantes": ID,
                    "idKanbanDel": ID2,
                    "idUsuarioDel": <?php echo $data['UserData']['UserID']; ?>,
                    "Fecha_Actual": '<?php echo $data['Fnc_ServerServer']->fechaActual(); ?>',
                    "Hora_Actual": '<?php echo $data['Fnc_ServerServer']->horaActual(); ?>'
                };
                const Options     = {
                    UpdateDiv : [
                        {Div:'#modalContent', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/view/'; ?>'+ID2, refreshTbl:'false'},
                        {Div:'#listTableData', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/updateList'; ?>', refreshTbl:'true'}
                    ],
                    showNoti:'Dato Borrado Correctamente',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
            }
        });
    }
    /*********************************************************************/
    /*                           OTRAS OPCIONES                          */
    /*********************************************************************/
    /******************************************/
    function deleteFilter(collapse=null) {
        //Cargo el loader
        $('#PDloader').show();
        //Opciones
        const Options = {
            refreshTables : 'true',
            closeObject:'#PDloader',
        };
        //refrescar
        UpdateContentId('#listTableDataList', '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/updateTableList'; ?>', Options);
        //Se muestra el modal
        if(typeof collapse !== 'undefined' && collapse != null && collapse!=''){
            //Se ocultan elementos
            $(collapse).collapse("toggle");
        }
    }
    /******************************************/
    function changeStatus(Objeto, Destino) {
        //Cargo el loader
        $('#PDloader').show();
        //Ejecuto
        let Metodo      = 'POST';
        let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/changeStatus'; ?>';
        let Informacion = {
            "idKanban": Objeto,
            "idKanbanEstado": Destino,
            "idUsuario": <?php echo $data['UserData']['UserID']; ?>,
            "Fecha_Actual": '<?php echo $data['Fnc_ServerServer']->fechaActual(); ?>',
            "Hora_Actual": '<?php echo $data['Fnc_ServerServer']->horaActual(); ?>'
        };
        const Options     = {
            showNoti:'Dato Modificado Correctamente',
            closeObject:'#PDloader',
        };
        //Se envian los datos al formulario
        SendDataForms(Metodo, Direccion, Informacion, Options);
    }
    /******************************************/
    let draggableTodo = null;
    let objeto        = '';
    let destino       = '';
    /**********************/
    function dragStart() {
        draggableTodo = this;
        setTimeout(() => {
            this.style.display = "none";
        }, 0);
        console.log("dragStart");
        objeto = this.id;
    }
    /**********************/
    function dragEnd() {
        draggableTodo = null;
        setTimeout(() => {
            this.style.display = "block";
        }, 0);
        console.log("dragEnd");
        //se actualiza
        changeStatus(objeto, destino);
    }
    /**********************/
    function dragOver(e) {
        e.preventDefault();
    }
    /**********************/
    function dragEnter() {
        this.style.border = "1px dashed #ccc";
        console.log("dragEnter");
        destino = this.id;
    }
    /**********************/
    function dragLeave() {
        this.style.border = "1px solid #ebeef4";
        console.log("dragLeave");
    }
    /**********************/
    function dragDrop() {
        this.style.border = "1px solid #ebeef4";
        this.appendChild(draggableTodo);
        console.log("dropped");
    }
    /**********************/
    function call_1() {
        const todos = document.querySelectorAll(".todo");
        todos.forEach((todo) => {
            todo.addEventListener("dragstart", dragStart);
            todo.addEventListener("dragend", dragEnd);
        });
        console.log("call_1");
        call_2();
    }
    /**********************/
    function call_2() {
        const all_status  = document.querySelectorAll(".status");
        all_status.forEach((status) => {
            status.addEventListener("dragover", dragOver);
            status.addEventListener("dragenter", dragEnter);
            status.addEventListener("dragleave", dragLeave);
            status.addEventListener("drop", dragDrop);
        });
        console.log("call_2");
    }
    /**********************/
    //Ejecuto la funcion
    call_1();



</script>

