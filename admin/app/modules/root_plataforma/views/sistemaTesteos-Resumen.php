<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Sistema de Testeos</h5>
            <div class="clearfix"></div>
            <div id="DivResumen">
                <?php require_once('sistemaTesteos-Resumen-Update.php'); ?>
            </div>
        </div>
    </div>
</div>

<script>
    /************************************/
    // Escape de texto para evitar inyeccion HTML en el modal
    function escTesteo(valor) {
        if (valor === null || valor === undefined) { return ''; }
        return String(valor)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    /************************************/
    // Clase del badge segun el estado de la prueba
    function badgeEstado(estado) {
        switch (String(estado || '').toUpperCase()) {
            case 'EXITOSA':     return 'badge-sp1 badge-sp1-bg-success';
            case 'ERROR':       return 'badge-sp1 badge-sp1-bg-danger';
            case 'ADVERTENCIA': return 'badge-sp1 badge-sp1-bg-warning';
            default:            return 'badge-sp1 badge-sp1-bg-secondary';
        }
    }
    /************************************/
    // Renderiza el resultado en el modal existente (#viewModal-xl)
    function renderResultadoModal(d) {
        var estado = d.Estado || 'ERROR';
        var html = '' +
            '<div class="modal-header">' +
                '<h5 class="modal-title"><i class="bi bi-card-checklist"></i> Resultado: ' + escTesteo(d.TestName || d.Test || 'Prueba') + '</h5>' +
                '<button type="button" class="btn-close" aria-label="Close" data-modal-close></button>' +
            '</div>' +
            '<div class="modal-body">' +
                '<p class="mb-1"><strong>Módulo: </strong>' + escTesteo(d.ModuleName || d.Module) + '</p>' +
                '<p class="mb-1"><strong>Prueba: </strong>' + escTesteo(d.Test) + '</p>' +
                '<p class="mb-1"><strong>Mecanismo: </strong><code>' + escTesteo(d.Metodo) + '</code></p>' +
                '<p class="mb-1"><strong>Estado: </strong><span class="' + badgeEstado(estado) + '">' + escTesteo(estado) + '</span></p>' +
                '<p class="mb-1"><strong>Mensaje: </strong>' + escTesteo(d.Mensaje) + '</p>' +
                '<p class="mb-2"><strong>Resultado</strong></p>' +
                '<div class="border rounded p-2" style="max-height:40vh;overflow:auto;background-color:#f8f9fa;">' +
                    '<pre class="mb-0" style="white-space:pre-wrap;word-break:break-word;">' + escTesteo(d.Resultado) + '</pre>' +
                '</div>' +
                (d.Info ? '<p class="mt-3 mb-0"><strong>Información adicional: </strong>' + escTesteo(d.Info) + '</p>' : '') +
            '</div>' +
            '<div class="modal-footer">' +
                '<div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">' +
                    '<button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>' +
                '</div>' +
            '</div>';
        var cont = document.getElementById('modalContent-xl');
        if (!cont) { return; }
        cont.innerHTML = html;
        $('#viewModal-xl').modal('show');
    }
    /************************************/
    // Ejecuta UNA prueba individual (sin recargar la pagina)
    function ejecutarPrueba(Module, Test, Nombre) {
        Swal.fire({
            title: "Ejecutar Prueba",
            text: 'Esta a punto de ejecutar la prueba "' + Nombre + '", ¿Desea continuar?',
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, ejecutar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Token CSRF actual (no se depende solo de $.ajaxSetup)
                var token = (typeof window !== 'undefined' && window.CSRF_TOKEN)
                    ? window.CSRF_TOKEN
                    : ((document.querySelector('meta[name="csrf-token"]') || {}).content || '');
                //Ejecuto solo esta prueba
                $.ajax({
                    method: 'POST',
                    url: '<?php echo $BASE.'/Core/plataforma/testeos/executeTest'; ?>',
                    headers: { 'X-CSRF-Token': token },
                    data: { Module: Module, Test: Test, _token: token }
                }).done(function (response) {
                    //Auto-sanacion: si la prueba roto el token (csrfTest), se sincroniza
                    if (response && response.data && response.data.Token && typeof window !== 'undefined') {
                        window.CSRF_TOKEN = response.data.Token;
                    }
                    var d = (response && response.data) ? response.data : {};
                    d.Module   = d.Module   || Module;
                    d.Test     = d.Test     || Test;
                    d.TestName = d.TestName || Nombre;
                    d.Estado   = d.Estado   || 'EXITOSA';
                    d.Mensaje  = d.Mensaje  || ((response && response.message) ? response.message : 'Prueba ejecutada');
                    renderResultadoModal(d);
                }).fail(function (jqXHR) {
                    var body = (jqXHR && jqXHR.responseJSON) ? jqXHR.responseJSON : {};
                    var d = (body.data && typeof body.data === 'object') ? body.data : {};
                    d.Module    = d.Module    || Module;
                    d.Test      = d.Test      || Test;
                    d.TestName  = d.TestName  || Nombre;
                    d.Estado    = d.Estado    || 'ERROR';
                    d.Mensaje   = d.Mensaje   || body.message || 'No se pudo ejecutar la prueba';
                    d.Metodo    = d.Metodo    || (Module + '::' + Test);
                    d.Resultado = d.Resultado || ('HTTP ' + (jqXHR ? jqXHR.status : '?'));
                    renderResultadoModal(d);
                }).always(function () {
                    //Cierro el loader
                    $('#PDloader').hide();
                });
            }
        });
    }
    /************************************/
    // Renderiza TODAS las pruebas de un modulo en el modal existente (#viewModal-xl)
    function renderTodasPruebasModal(d) {
        var tests     = (d.Tests && d.Tests.length) ? d.Tests : [];
        var total     = (d.Total !== undefined && d.Total !== null) ? d.Total : tests.length;
        var totalOk   = d.TotalOk         || 0;
        var totalErr  = d.TotalError      || 0;
        var totalWarn = d.TotalAdvertencia || 0;
        // Porcentaje de pruebas exitosas
        var porcentaje = (total>0) ? ((totalOk/total)*100).toFixed(1) : '0.0';
        // Filas de la tabla
        var filas = '';
        for(var i=0; i<tests.length; i++){
            var t     = tests[i] || {};
            var est   = t.Estado || 'ERROR';
            var resul = (t.Resultado === undefined || t.Resultado === null || t.Resultado === '') ? '(sin detalle)' : t.Resultado;
            filas += '' +
                '<tr>' +
                    '<td>' + (i+1) + '</td>' +
                    '<td><strong>' + escTesteo(t.TestName || t.Test || 'Prueba') + '</strong><br><small class="text-muted">' + escTesteo(t.Mensaje || '') + '</small></td>' +
                    '<td><code>' + escTesteo(t.Metodo || '') + '</code></td>' +
                    '<td><span class="' + badgeEstado(est) + '">' + escTesteo(est) + '</span></td>' +
                    '<td>' +
                        '<details>' +
                            '<summary class="text-muted" style="cursor:pointer;">Ver resultado</summary>' +
                            '<div class="border rounded p-2 mt-1" style="background-color:#f8f9fa;">' +
                                '<pre class="mb-0" style="white-space:pre-wrap;word-break:break-word;font-size:.85rem;">' + escTesteo(resul) + '</pre>' +
                                (t.Info ? '<small class="text-muted d-block mt-1">' + escTesteo(t.Info) + '</small>' : '') +
                            '</div>' +
                        '</details>' +
                    '</td>' +
                '</tr>';
        }
        // Si no hay pruebas
        if(tests.length===0){
            filas = '<tr><td colspan="5" class="text-center text-muted py-3"><i class="bi bi-info-circle"></i> No se ejecuto ninguna prueba.</td></tr>';
        }
        // Color de la barra de resumen
        var colorResumen = (totalErr>0) ? 'danger' : ((totalWarn>0) ? 'warning' : 'success');
        var html = '' +
            '<div class="modal-header">' +
                '<h5 class="modal-title"><i class="bi bi-clipboard-check"></i> Resultado suite completa: ' + escTesteo(d.ModuleName || d.Module || 'Modulo') + '</h5>' +
                '<button type="button" class="btn-close" aria-label="Close" data-modal-close></button>' +
            '</div>' +
            '<div class="modal-body">' +
                '<p class="mb-2"><strong>Modulo: </strong>' + escTesteo(d.Module) + ' | <strong>Tiempo total: </strong>' + escTesteo(d.TiempoTotal || 0) + ' ms</p>' +
                (d.ResultadoError ? '<div class="alert alert-danger"><i class="bi bi-exclamation-octagon"></i> ' + escTesteo(d.ResultadoError) + '</div>' : '') +
                '<div class="table-responsive" style="overflow:auto;">' +
                    '<table class="table table-sm table-hover align-middle mb-0">' +
                        '<thead class="table-light" style="position:sticky;top:0;z-index:1;">' +
                            '<tr>' +
                                '<th scope="col">N°</th>' +
                                '<th scope="col">Prueba</th>' +
                                '<th scope="col">Mecanismo</th>' +
                                '<th scope="col">Estado</th>' +
                                '<th scope="col">Detalle</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>' + filas + '</tbody>' +
                    '</table>' +
                '</div>' +
            '</div>' +
            '<div class="modal-footer">' +
                '<div class="w-100">' +
                    '<div class="row text-center g-2 mb-2">' +
                        '<div class="col-6 col-md-3"><div class="border rounded p-2"><small class="text-muted d-block">Total</small><strong class="fs-5">' + total + '</strong></div></div>' +
                        '<div class="col-6 col-md-3"><div class="border rounded p-2"><small class="text-muted d-block">OK</small><strong class="fs-5" style="color:#2ECC71;">' + totalOk + '</strong></div></div>' +
                        '<div class="col-6 col-md-3"><div class="border rounded p-2"><small class="text-muted d-block">Fallidas</small><strong class="fs-5" style="color:#df8f8f;">' + totalErr + '</strong></div></div>' +
                        '<div class="col-6 col-md-3"><div class="border rounded p-2"><small class="text-muted d-block">Advertencias</small><strong class="fs-5" style="color:#F4BD50;">' + totalWarn + '</strong></div></div>' +
                    '</div>' +
                    '<div class="progress" role="progressbar" aria-label="Resumen de la suite" style="height:8px;">' +
                        '<div class="progress-bar bg-' + colorResumen + '" style="width:' + porcentaje + '%;" title="' + porcentaje + '% de pruebas exitosas"></div>' +
                    '</div>' +
                    '<small class="text-muted">' + porcentaje + '% de pruebas exitosas (' + totalOk + ' de ' + total + ')</small>' +
                    '<div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">' +
                        '<button type="button" class="btn btn-outline-secondary btn-sm" id="btnDetalleSuite"><i class="bx bi-eye"></i> Ver todo el detalle</button>' +
                        '<button type="button" class="btn btn-primary btn-sm" id="btnReEjecutarSuite" data-module="' + escTesteo(d.Module) + '" data-nombre="' + escTesteo(d.ModuleName || d.Module || '') + '"><i class="bi bi-arrow-repeat"></i> Volver a ejecutar</button>' +
                        '<button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        var cont = document.getElementById('modalContent-xl');
        if (!cont) { return; }
        cont.innerHTML = html;
        // Muestra el modal
        $('#viewModal-xl').modal('show');
        // Muestra/oculta el detalle de todas las pruebas
        var btnDetalle = document.getElementById('btnDetalleSuite');
        if (btnDetalle) {
            btnDetalle.addEventListener('click', function () {
                var abiertas = cont.querySelectorAll('details[open]').length;
                cont.querySelectorAll('details').forEach(function (det) { det.open = (abiertas === 0); });
            });
        }
        // Reejecucion de la suite completa
        var btnRe = document.getElementById('btnReEjecutarSuite');
        if (btnRe) {
            btnRe.addEventListener('click', function () {
                var modulo = this.getAttribute('data-module');
                var nombre = this.getAttribute('data-nombre');
                $('#viewModal-xl').modal('hide');
                ejecutarTodasPruebas(modulo, nombre, total);
            });
        }
    }
    /************************************/
    // Ejecuta TODAS las pruebas de un modulo (sin recargar la pagina)
    function ejecutarTodasPruebas(Module, Nombre, Total) {
        Swal.fire({
            title: "Ejecutar Todas las Pruebas",
            text: 'Esta a punto de ejecutar ' + (Total || 0) + ' prueba(s) del modulo "' + Nombre + '", ¿Desea continuar?',
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, ejecutar",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                //Cargo el loader
                $('#PDloader').show();
                //Token CSRF actual (no se depende solo de $.ajaxSetup)
                var token = (typeof window !== 'undefined' && window.CSRF_TOKEN)
                    ? window.CSRF_TOKEN
                    : ((document.querySelector('meta[name="csrf-token"]') || {}).content || '');
                //Ejecuto la suite completa del modulo
                $.ajax({
                    method: 'POST',
                    url: '<?php echo $BASE.'/Core/plataforma/testeos/executeAllTests'; ?>',
                    headers: { 'X-CSRF-Token': token },
                    data: { Module: Module, _token: token }
                }).done(function (response) {
                    //Auto-sanacion: si una prueba roto el token (csrfTest), se sincroniza
                    if (response && response.data && response.data.Token && typeof window !== 'undefined') {
                        window.CSRF_TOKEN = response.data.Token;
                    }
                    var d = (response && response.data) ? response.data : {};
                    d.Module     = d.Module     || Module;
                    d.ModuleName = d.ModuleName || Nombre;
                    d.Tests      = d.Tests      || [];
                    d.Total      = (d.Total === undefined || d.Total === null) ? d.Tests.length : d.Total;
                    renderTodasPruebasModal(d);
                }).fail(function (jqXHR) {
                    var body = (jqXHR && jqXHR.responseJSON) ? jqXHR.responseJSON : {};
                    var d = (body.data && typeof body.data === 'object') ? body.data : {};
                    d.Module           = d.Module           || Module;
                    d.ModuleName       = d.ModuleName       || Nombre;
                    d.Tests            = [];
                    d.Total            = 0;
                    d.TotalOk          = 0;
                    d.TotalError       = 1;
                    d.TotalAdvertencia = 0;
                    d.TiempoTotal      = 0;
                    d.ResultadoError   = (jqXHR && jqXHR.status) ? ('HTTP ' + jqXHR.status + ': ' + (body.message || 'No se pudo ejecutar la suite')) : '';
                    renderTodasPruebasModal(d);
                }).always(function () {
                    //Cierro el loader
                    $('#PDloader').hide();
                });
            }
        });
    }
</script>
