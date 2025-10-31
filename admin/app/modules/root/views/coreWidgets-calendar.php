<section class="section">

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Calendario</h5>
                    <div id="calendar"></div>
                    

                </div>
            </div>

        </div>
    </div>

</section>

<!-- Modal para Crear/Editar Evento -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="eventForm" name="eventForm" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                <div class="modal-header">
                    <?php
                    switch ($data['UserData']["sistemaModalSubtitle"]) {
                        case 1:
                            echo '
                            <h5 class="modal-title">
                                <i class="bi bi-search"></i> <span id="eventModalLabel"></span>
                            </h5>';
                            break;
                        case 2:
                            echo '
                            <h5 class="modal-title modal-subtitle">
                                <div class="icon"><i class="bi bi-search"></i></div>
                                <span id="eventModalLabel"></span><br>
                                <small>Permite agregar un nuevo evento al calendario</small>
                            </h5>';
                            break;
                    } ?>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 2,'FormCol' => 12,  'Placeholder' => 'Título del Evento', 'Name' => 'title',       'Id' => 'title',       'Value' => '', 'Required' => 2]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,'FormAling' => 2,'FormCol' => 12,  'Placeholder' => 'Fecha de Inicio',   'Name' => 'start',  'Id' => 'start',  'Value' => '', 'Required' => 2, 'Icon' => 'bi bi-calendar3']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,'FormAling' => 2,'FormCol' => 12,  'Placeholder' => 'Fecha de Fin',      'Name' => 'end', 'Id' => 'end', 'Value' => '', 'Required' => 2, 'Icon' => 'bi bi-calendar3']);

                    //datos ocultos
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'eventId',   'Value' => '',   'Required' => 1]);  //Usuario que lo creo
                    ?>

                </div>
                <div class="modal-footer">
                    <button type="button" id="deleteEventBtn" class="btn btn-danger" style="display: none;"><i class="bx bi-x-circle"></i> Eliminar Evento</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        //identificar calendario
        const calendarEl = document.getElementById('calendar');
        //funcionalidad calendario
        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'es',
            initialView: 'dayGridMonth',
            editable: true,
            events: 'fetch_events.php',
            eventClick: function(info) {
                // Editar evento al hacer clic
                $('#eventModal').modal('show');
                $('#title').val(info.event.title);
                $('#start').val(info.event.startStr);
                $('#end').val(info.event.endStr);
                $('#eventId').val(info.event.id);
                $('#eventModalLabel').text('Editar Evento');
                $('#deleteEventBtn').show(); // Mostrar botón de eliminar
            },
            dateClick: function(info) {
                // Crear nuevo evento al hacer clic en una fecha
                $('#eventModal').modal('show');
                $('#eventForm')[0].reset();
                $('#title').val('');
                $('#start').val(info.dateStr);
                $('#end').val('');
                $('#eventId').val('');
                $('#eventModalLabel').text('Agregar Evento');
                $('#deleteEventBtn').hide(); // Ocultar botón de eliminar
            }
        });

        /******************************************/
        //Se carga el calendario
        calendar.render();

        /******************************************/
        // Guardar evento (Agregar o Editar)
        $("#eventForm").submit(function(e) {
            //Se validan los datos de los formularios
            var validatorResult = validator.checkAll(this);
            //verifico el resultado
            if(validatorResult.valid===false){
                return !!validatorResult.valid;
            }else{
                e.preventDefault();
                //se busca si existe
                const eventData = {
                    id: $('#eventId').val()
                };
                //Se definen las direcciones
                let Direccion_insert   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess']; ?>';
                let Direccion_update   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update'; ?>';
                //Cargo el loader
                $('#PDloader').show();
                //Ejecuto
                let Metodo      = 'POST';
                let Direccion   = eventData.id ? Direccion_update : Direccion_insert;
                let Informacion = $("#eventForm").serialize();
                const Options     = {
                    closeModal:'#eventModal',
                    closeObject:'#PDloader',
                };
                //Se envian los datos al formulario
                SendDataForms(Metodo, Direccion, Informacion, Options);
                // Recargar eventos
                calendar.refetchEvents();
            }
        });

        /******************************************/
        // Eliminar evento
        $('#deleteEventBtn').on('click', function() {
            const eventId = $('#eventId').val();
            const title   = $('#title').val();
            //Borrar dato
            listTableDataDel(eventId, title);
        });

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
            }).then((result2) => {
                if (result2.isConfirmed) {
                    //Cargo el loader
                    $('#PDloader').show();
                    //Ejecuto
                    let Metodo      = 'DELETE';
                    let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess']; ?>';
                    let Informacion = {"idCalendario": ID};
                    const Options     = {
                        showNoti:'Dato Borrado Correctamente',
                        closeModal:'#eventModal',
                        closeObject:'#PDloader',
                    };
                    //Se envian los datos al formulario
                    SendDataForms(Metodo, Direccion, Informacion, Options);
                    // Recargar eventos
                    calendar.refetchEvents();
                }
            });
        }

    });
</script>