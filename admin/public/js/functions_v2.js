/*******************************************************************************
 * functions_v2.js
 * -----------------------------------------------------------------------------
 * Version optimizada de `functions.js`. Es un reemplazo directo (drop-in):
 * conserva el nombre y la firma de TODAS las funciones publicas que usan las
 * vistas PHP y mantiene su ambito global (las vistas usan onclick="...").
 *
 * Cambios aplicados:
 *   1. Robustez : Options nunca es null; JSON.parse protegido; guardas de null
 *                 en getElementById; UpdateContentId tambien muestra el mensaje
 *                 de error que devuelve el backend.
 *   2. DRY      : optSet(), parseJsonSeguro(), mensajeDeError(), notiBubble(),
 *                 initDataTables() (antes estaba 3 veces), ejecutarCallFNC(),
 *                 aplicarOpcionesModal/Objeto/liberarFormulario() y
 *                 sendAjaxForm() (unifica SendDataForms/SendDataFormsFiles).
 *   3. Limpieza : se eliminan `_refuerzosBloqueo`, el handler de cierre NO
 *                 delegado, el bloque de botones comentado y la escritura
 *                 muerta `select.dataset.valorPrevio`.
 *   4. Rendim.  : addElement ya no re-inicializa Select2 de los clones previos
 *                 (era O(n^2)); exportTableToExcel usa Blob + createObjectURL.
 *   5. Calidad  : sin variables globales implicitas (`Valor`), parseInt con
 *                 radix y control de NaN, escapeHtml completo, logs de traza
 *                 silenciados y activables con window.DEBUG_FNCS = true.
 *   6. UX/A11y  : openPopupModal bloquea el scroll, marca aria y gestiona el
 *                 foco; un unico punto de cierre para los PopupModal.
 *
 * Para depurar en consola:  window.DEBUG_FNCS = true;
 ******************************************************************************/
(function () {
    'use strict';

    /**********************************************************/
    //Log de traza: solo se muestra si se activa window.DEBUG_FNCS = true
    function logFNCS(...args) { if (window.DEBUG_FNCS) { console.log(...args); } }

    /**********************************************************/
    //Aviso de un problema real (siempre visible)
    function warnFNCS(...args) { console.warn(...args); }

    /**********************************************************/
    //Indica si una opcion viene informada.
    //Se usa la comparacion laxa ( != ) a proposito: mantiene la semantica del
    //original, donde 0, false y [] se consideran "vacios".
    function optSet(Options, key) {
        return Options != null &&
               typeof Options[key] !== 'undefined' &&
               Options[key] != null &&
               Options[key] != '';
    }

    /**********************************************************/
    // Devuelve el token CSRF ACTUAL de la pagina.
    // Se lee en CADA peticion (no se "cocina" al cargar) para que, si la
    // sesion lo rota (login/logout o una prueba como csrfTest), no quede obsoleto.
    function currentCsrfToken() {
        if (typeof window === 'undefined') { return ''; }
        if (window.CSRF_TOKEN) { return window.CSRF_TOKEN; }
        var meta = document.querySelector('meta[name="csrf-token"]');
        return (meta && meta.content) ? meta.content : '';
    }

    /**********************************************************/
    //Parsea la respuesta del backend sin romper el flujo si no es JSON
    //(p. ej. una pagina de error HTML o una sesion expirada)
    function parseJsonSeguro(texto) {
        if (texto == null || texto === '') { return null; }
        try {
            return JSON.parse(texto);
        } catch (error) {
            warnFNCS('functions: la respuesta no es JSON valido', error);
            return null;
        }
    }

    /**********************************************************/
    //Construye un mensaje legible a partir de la respuesta de error del backend
    function mensajeDeError(jqXHR) {
        //Sin respuesta no hay mensaje que extraer
        if (!jqXHR || !jqXHR.responseText) { return ''; }
        //Se parsea de forma segura
        const jsonData = parseJsonSeguro(jqXHR.responseText);
        if (!jsonData) { return ''; }
        //Variable
        let message = '';
        //se verifica si resultado es un array
        if (Array.isArray(jsonData.data)) {
            // data es un array
            jsonData.data.forEach(item => {
                if (item && item.message) {
                    message += item.message + '<br>';
                }
            });
        //si hay datos
        } else if (jsonData.data) {
            // data es un string u otro valor
            message = jsonData.message && jsonData.message != jsonData.data
                ? `${jsonData.message}<br>${jsonData.data}`
                : jsonData.data;
        //si no lo es solo se muestra
        } else {
            // No existe data
            message = jsonData.message || 'Error desconocido';
        }
        // Fallback por si queda vacio
        if (!message) { message = 'Error desconocido'; }
        //Devuelvo
        return message;
    }

    /**********************************************************/
    //Notificacion de error con el formato comun (antes repetido 3 veces)
    function notiBubble(mensaje, type, esHtml) {
        //Configuracion comun
        const config = {
            position: "top-end",
            timer: 5000,
            showConfirmButton: false,
            timerProgressBar: true,
            icon: type
        };
        //El mensaje puede venir con etiquetas (html) o como texto plano
        if (esHtml) { config.html = mensaje; } else { config.text = mensaje; }
        //Se muestra
        Swal.fire(config);
    }

    /**********************************************************/
    //Inicializa las tablas de datos dentro de un contenedor.
    //Antes este mismo bloque estaba duplicado en SendDataOptions,
    //UpdateContentId y main.js (con distinta configuracion).
    function initDataTables(Contexto) {
        //Si la libreria no esta cargada no se hace nada (antes lanzaba error)
        if (typeof simpleDatatables === 'undefined' || !simpleDatatables) { return; }
        //Contenedor por defecto: el documento completo
        const contenedor = Contexto || document;
        const datatables = contenedor.querySelectorAll('.datatable');
        datatables.forEach(datatable => {
            try {
                new simpleDatatables.DataTable(datatable, {
                    perPage: 100,
                    perPageSelect: [5, 10, 25, 50, 100, ["Todas", -1]],
                    labels: {
                        placeholder: "Buscar...",
                        searchTitle: "Buscar dentro de la tabla",
                        perPage: "entradas por página",
                        pageTitle: "Página {page}",
                        noRows: "No se encontraron entradas",
                        noResults: "No hay resultados que coincidan con tu consulta de búsqueda",
                        info: "Mostrando {start} a {end} de {rows} entradas"
                    }
                });
            } catch (error) {
                //Si una tabla ya estaba inicializada no se corta el refresco del resto
                warnFNCS('initDataTables: no se pudo inicializar la tabla', error);
            }
        });
    }

    /**********************************************************/
    //Muestra/oculta modales y vistas colapsadas (mismo orden que el original)
    function aplicarOpcionesModal(Options) {
        if (optSet(Options, 'showModal')) { $(Options.showModal).modal('show'); }
        if (optSet(Options, 'closeModal')) { $(Options.closeModal).modal('hide'); }
        if (optSet(Options, 'colapseDiv')) { $('.collapse').collapse("toggle"); }
    }

    /**********************************************************/
    //Muestra/oculta objetos
    function aplicarOpcionesObjeto(Options) {
        if (optSet(Options, 'showObject')) { $(Options.showObject).show(); }
        if (optSet(Options, 'closeObject')) { $(Options.closeObject).hide(); }
    }

    /**********************************************************/
    //Devuelve un objeto de bloqueo (boton deshabilitado) a su estado inicial
    function liberarFormulario(Options) {
        if (optSet(Options, 'changeValForm')) { Options.changeValForm.valor = false; }
    }

    /**********************************************************/
    //Ejecuta la funcion global indicada en Options.callFNC.
    //Soporta: sin argumentos, con un argumento (objeto/string) o con varios
    //argumentos si Options.callFNCData es un array.
    function ejecutarCallFNC(Options, origen) {
        //Sin funcion no se hace nada
        if (!optSet(Options, 'callFNC')) { return; }
        //Obtengo la funcion global
        const functionObj = window[Options.callFNC];
        //Verifico que exista (evita romper las opciones siguientes)
        if (typeof functionObj !== 'function') {
            warnFNCS((origen || 'functions') + ': no existe la funcion global "' + Options.callFNC + '"');
            return;
        }
        //Evaluo si hay datos relacionados
        if (Array.isArray(Options.callFNCData)) {
            //llamo a la funcion con varios datos
            functionObj.apply(null, Options.callFNCData);
        } else if (optSet(Options, 'callFNCData')) {
            //llamo a la funcion con datos
            functionObj(Options.callFNCData);
        } else {
            //llamo a la funcion sin datos
            functionObj();
        }
    }

    /**********************************************************/
    //Envia el formulario por AJAX.
    //Centraliza SendDataForms y SendDataFormsFiles (eran el mismo codigo con
    //processData/contentType como unica diferencia).
    function sendAjaxForm(esArchivo, Metodo, Direccion, Informacion, Options) {
        //log
        logFNCS('SendDataForms: ingreso');
        //Configuracion base
        const config = { method: Metodo, url: Direccion, data: Informacion };
        //Los formularios con archivos no deben procesar ni serializar los datos
        if (esArchivo) {
            config.processData = false;
            config.contentType = false;
        }
        //consulto los datos
        $.ajax(config).done(function (data, textStatus, jqXHR) {
            //Se ejecutan las opciones
            SendDataOptions(Options, jqXHR);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            //Se ejecutan los errores
            SendDataErrors(Options, jqXHR);
        });
    }

    /**********************************************************/
    //Se agrega elemento
    function addElement(IDobjTo, IDobjclone, Node, select2, modalID, NInt){
        //se instancian los objetos a clonar
        const objTo    = document.getElementById(IDobjTo);
        const objclone = document.getElementById(IDobjclone);
        //Verifico que existan (antes lanzaba TypeError si la vista cambiaba)
        if (!objTo || !objclone) {
            warnFNCS('addElement: no existe "' + IDobjTo + '" o "' + IDobjclone + '"');
            return;
        }
        //se clonan los div
        const clone = objclone.cloneNode(true);
        clone.id = Node + NInt;
        //inserto dentro del div deseado
        objTo.appendChild(clone);
        //Si hay que reactivar los select
        if (select2) {
            try {
                //Solo se inicializa el select del clon. Antes se re-inicializaban
                //todos los clones anteriores en cada llamada (O(n^2), y Select2
                //reconstruye el DOM en cada init).
                $(clone).find("select")
                        .addClass(select2 + NInt)
                        .select2({
                            dropdownParent: modalID ? $("#" + modalID) : $(document.body),
                            width: "100%",
                            language: "es"
                        });
            } catch (error) {
                console.error(error);
            }
        }
    }

    /**********************************************************/
    //Se ejecuta formulario
    function SendDataForms(Metodo, Direccion, Informacion, Options) {
        sendAjaxForm(false, Metodo, Direccion, Informacion, Options);
    }

    /**********************************************************/
    //Se ejecuta formulario para archivos
    function SendDataFormsFiles(Metodo, Direccion, Informacion, Options) {
        sendAjaxForm(true, Metodo, Direccion, Informacion, Options);
    }

    /**********************************************************/
    //Se ejecutan las opciones
    function SendDataOptions(Options, jqXHR) {
        //Blindaje: permite llamarla sin opciones
        Options = Options || {};
        //log
        logFNCS('SendDataForms: ok');
        //Limpia el formulario
        if (optSet(Options, 'ClearForm')) {
            const formulario = document.getElementById(Options.ClearForm);
            if (formulario) { formulario.reset(); }
            else { warnFNCS('SendDataOptions: no existe el formulario "' + Options.ClearForm + '"'); }
        }
        //redirige a otra ventana
        if (optSet(Options, 'Destino')) { window.location = Options.Destino; }
        //redirige a otra ventana desde una respuesta
        if (optSet(Options, 'DestinoFrom')) {
            //obtengo la respuesta (protegida: antes un HTML de error cortaba el flujo)
            const jsonData = parseJsonSeguro(jqXHR ? jqXHR.responseText : '');
            if (jsonData) { window.location = Options.DestinoFrom + jsonData.message; }
            else { warnFNCS('SendDataOptions: respuesta invalida para DestinoFrom'); }
        }
        //Permite cargar informacion en un div
        if (optSet(Options, 'UpdateDiv')) {
            //Se recorre el array
            Options.UpdateDiv.forEach(element => {
                //Ejecuto
                const Div2     = element.Div;
                const URL2     = element.fromData;
                const Options2 = {};
                if (optSet(element, 'refreshTbl')) {
                    Options2.refreshTables = element.refreshTbl;
                }
                if (optSet(element, 'callFNC')) {
                    Options2.callFNC = element.callFNC;
                }
                //se propaga los datos de la funcion
                if (optSet(element, 'callFNCData')) {
                    Options2.callFNCData = element.callFNCData;
                }
                //Se envian los datos al formulario
                UpdateContentId(Div2, URL2, Options2);
            });
        }
        //Permite cargar informacion en un div desde una respuesta
        if (optSet(Options, 'UpdateDivFrom')) {
            const destino = document.getElementById(Options.UpdateDivFrom);
            if (destino) { destino.innerHTML = jqXHR.responseText; }
            else { warnFNCS('SendDataOptions: no existe el div "' + Options.UpdateDivFrom + '"'); }
        }
        //Mostrar Notificacion
        if (optSet(Options, 'showNoti')) {
            Swal.fire({icon: 'success', title: 'Notificación', html: Options.showNoti});
        }
        //Mueve a otro tab
        if (optSet(Options, 'triggerTab')) { $(Options.triggerTab).tab('show'); }
        //Se muestra/oculta el modal y las vistas colapsadas
        aplicarOpcionesModal(Options);
        //Se refrescan las tablas
        if (optSet(Options, 'refreshTables')) { initDataTables(); }
        //se llama a otra funcion
        ejecutarCallFNC(Options, 'SendDataOptions');
        //Se muestra/oculta el objeto
        aplicarOpcionesObjeto(Options);
        //Se abre nueva pestaña
        if (optSet(Options, 'openNewTab')) { window.open(Options.openNewTab, '_blank'); }
        //Se cambia el valor de una variable
        liberarFormulario(Options);
    }

    /**********************************************************/
    //Se ejecutan las opciones de error
    //(los parametros textStatus/errorThrown del original no se usaban)
    function SendDataErrors(Options, jqXHR) {
        //Blindaje: permite llamarla sin opciones
        Options = Options || {};
        //log
        logFNCS('SendDataForms: error');
        //Se muestra/oculta el modal y las vistas colapsadas
        aplicarOpcionesModal(Options);
        //Se muestra/oculta el objeto
        aplicarOpcionesObjeto(Options);
        //Se cambia el valor de una variable
        liberarFormulario(Options);
        /******************************/
        // Se verifica la respuesta
        const message = mensajeDeError(jqXHR);
        //se muestra el mensaje (o el aviso generico si no hay datos)
        if (message) { notiBubble(message, 'error', true); }
        else { notiBubble('No existen datos.', 'error', false); }
    }

    /**********************************************************/
    //Se actualiza contenido del div
    function UpdateContentId(Div, URL, Options = null) {
        //Blindaje: el parametro es opcional, pero el callback usa Options.x
        Options = Options || {};
        $(Div).load(URL, function (responseTxt, statusTxt, xhr) {
            if (statusTxt == "success") {
                //log
                logFNCS('UpdateContentId: success');
                //Mueve a otro tab
                if (optSet(Options, 'triggerTab')) { $(Options.triggerTab).tab('show'); }
                //Se muestra/oculta el modal y las vistas colapsadas
                aplicarOpcionesModal(Options);
                //Se refrescan las tablas
                if (optSet(Options, 'refreshTables')) { initDataTables(); }
                //se llama a otra funcion
                ejecutarCallFNC(Options, 'UpdateContentId');
                //Se muestra/oculta el objeto
                aplicarOpcionesObjeto(Options);
                //Se cambia el valor de una variable
                liberarFormulario(Options);
            }
            if (statusTxt == "error") {
                //log
                logFNCS('UpdateContentId: error');
                //Mismo criterio de mensaje que SendDataErrors: si el backend
                //devuelve un detalle se muestra; si no, el estado HTTP.
                const message = mensajeDeError(xhr);
                if (message) { notiBubble(message, 'error', true); }
                else { notiBubble(xhr.status + ": " + xhr.statusText, 'error', false); }
            }
        });
    }

    /**********************************************************/
    //Se redirecciona
    function showConfirmRedirect(Icono, Titulo, BtnText, Destino) {
        Swal.fire({
            icon: Icono,
            title: Titulo,
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> "+BtnText,
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result) => {
            //Si se confirma
            if (result.isConfirmed) {
                window.location = Destino;
            }
        });
    }

    /**********************************************************/
    //Se ejecuta formulario para archivos
    function appendFiles(Formulario, InputFile, NFiles) {
        //Variable vacia
        var data = new FormData();

        //Recorro y agrego los elementos del formulario
        var form_data = $(Formulario).serializeArray();
        $.each(form_data, function (key, input) {
            data.append(input.name, input.value);
        });

        //Indico el input a usar
        var file_data = $('input[name="'+InputFile+'"]')[0].files;

        //Verifico el numero de archivos
        if(NFiles == 1){
            //Si es solo uno
            data.append(InputFile, file_data[0]);
        }else{
            //Si sin varios
            for (var i = 0; i < file_data.length; i++) {
                data.append(InputFile+"[]", file_data[i]);
            }
        }

        //Devuelvo
        return data;
    }

    /**********************************************************/
    //devolver el valor
    function return_value(value) {
        //parseInt con radix y sin crear una variable global implicita (antes
        //`Valor = parseInt(value)` contaminaba window)
        const valor = parseInt(value, 10);
        //Si no es un numero se muestra 0 en lugar de "NaN"
        return "$ " + (Number.isNaN(valor) ? 0 : valor).toLocaleString('es-CL');
    }

    /**********************************************************/
    //permite exportar a excel
    function exportTableToExcel(tableID, filename = ''){
        //Localizo la tabla (antes lanzaba TypeError si no existia)
        const tableSelect = document.getElementById(tableID);
        if (!tableSelect) {
            warnFNCS('exportTableToExcel: no existe la tabla "' + tableID + '"');
            return;
        }
        const dataType = 'application/vnd.ms-excel';
        // Optimiza el reemplazo de caracteres especiales usando un objeto de mapeo y expresión regular
        const charMap = {
            'á': '&aacute;', 'Á': '&Aacute;',
            'é': '&eacute;', 'É': '&Eacute;',
            'í': '&iacute;', 'Í': '&Iacute;',
            'ó': '&oacute;', 'Ó': '&Oacute;',
            'ú': '&uacute;', 'Ú': '&Uacute;',
            'º': '&ordm;',
            'ñ': '&ntilde;', 'Ñ': '&Ntilde;'
        };
        let tableHTML = tableSelect.outerHTML.replace(/[áÁéÉíÍóÓúÚºñÑ]/g, match => charMap[match]);
        //se eliminan los saltos de linea
        tableHTML = tableHTML.replace(/<br *\/?>/gi, ' | ');

        // Specify file name
        filename = filename ? filename + '.xls' : 'excel_data.xls';

        //Se usa siempre Blob + createObjectURL: funciona en los navegadores
        //actuales, permite el BOM (acentos correctos en Excel) y evita el
        //data-URI, que obligaba a reemplazar los espacios por %20.
        const blob         = new Blob(['\ufeff', tableHTML], { type: dataType });
        const url          = URL.createObjectURL(blob);
        const downloadLink = document.createElement("a");

        // Setting the file name
        downloadLink.href     = url;
        downloadLink.download = filename;

        //se agrega al documento y se dispara la descarga
        document.body.appendChild(downloadLink);
        downloadLink.click();
        //se limpia el DOM y la memoria
        document.body.removeChild(downloadLink);
        setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
    }

    /**********************************************************/
    //Marca una única opción (desmarca explícitamente el resto; no depende del navegador)
    function _marcarOpcion(select, opcion) {
        for (var i = 0; i < select.options.length; i++) {
            select.options[i].selected = (select.options[i] === opcion);
        }
        select.selectedIndex = opcion ? opcion.index : -1;
    }

    /**********************************************************/
    //Busca una opción por su value (comparando como texto)
    function _buscarOpcion(select, valor) {
        for (var i = 0; i < select.options.length; i++) {
            if (String(select.options[i].value) === String(valor)) {
                return select.options[i];
            }
        }
        return null;
    }

    /**********************************************************/
    //Autoselecciona una opción de un <select> por su id y lo bloquea
    /**
     * @param {string} id             id del <select> (sin "#")
     * @param {string} valor          value de la opción a marcar (por defecto "1")
     * @param {number} indiceRespaldo índice a usar si no existe esa opción (0 = primera)
     * @returns {boolean}             true si encontró y bloqueó el select
     */
    function seleccionarOpcionYBloquear(id, valor, indiceRespaldo) {
        valor          = (valor === undefined) ? "1" : valor;
        indiceRespaldo = (indiceRespaldo === undefined) ? 0 : indiceRespaldo;

        // 1) Localizo el select por su id
        var select = document.getElementById(id);
        if (!select) {
            warnFNCS('seleccionarOpcionYBloquear: no existe el <select> con id "' + id + '"');
            return false;
        }

        // 2) Marco la opción pedida (o el índice de respaldo)
        var opcion = _buscarOpcion(select, valor) ||
                     select.options[indiceRespaldo] ||
                     select.options[0] || null;
        if (opcion) {
            _marcarOpcion(select, opcion);
        }

        // 3) Bloqueo el selector para impedir el cambio
        select.style.pointerEvents = 'none';
        select.setAttribute('aria-disabled', 'true');
        select.classList.add('select-bloqueado');

        // 4) Aviso del cambio a los listeners (Select2 escucha "change" en el <select>)
        select.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
    }

    /**********************************************************/
    //Reestablece (desbloquea) el select para permitir volver a cambiarlo
    /**
     * @param {string} id     id del <select> (sin "#")
     * @param {mixed}  valor  (opcional) value a dejar seleccionado:
     *                        - undefined (por defecto) => mantiene la selección actual
     *                        - null                    => limpia la selección (selectedIndex = -1)
     *                        - "1" | "2" | ...         => selecciona ese value si existe
     * @returns {boolean}     true si encontró y desbloqueó el select
     */
    function reestablecerSelect(id, valor) {
        var select = document.getElementById(id);
        if (!select) {
            warnFNCS('reestablecerSelect: no existe el <select> con id "' + id + '"');
            return false;
        }

        // 1) Desbloqueo
        select.style.pointerEvents = '';
        select.removeAttribute('aria-disabled');
        select.classList.remove('select-bloqueado');

        // 2) Ajusto la selección según el parámetro recibido
        if (valor === null) {
            _marcarOpcion(select, null);          // limpiar selección
        } else if (valor !== undefined) {
            var opcion = _buscarOpcion(select, valor);
            if (opcion) { _marcarOpcion(select, opcion); }
        }
        // Si valor === undefined, se deja la opción que estaba seleccionada

        // 3 Aviso del cambio (Select2 vuelve a quedar operativo)
        select.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
    }

    /**********************************************************/
    //Normaliza una URL evitando las barras duplicadas (respeta el "//" de
    //"http://"). Se usa desde el JS inline del widget widget_fileExplorer()
    //de vendors/application/functions/UIWidgetsCommon.php
    function normalizarURL(u){ return u.replace(/([^:])\/\/+/g,'$1/'); }
    /**********************************************************/
    //Escapa caracteres conflictivos para insertar texto en HTML/atributos
    //(antes solo escapaba & y <, lo que rompía atributos con comillas)
    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /**********************************************************/
    /* ============================================================
       PopupModal
       ============================================================ */
    //Estado privado de los PopupModal
    let popupsAbiertos = 0;   //cuantos overlays estan abiertos
    let overflowPrevio = '';  //valor de body.style.overflow antes de bloquear
    const pilaFoco     = [];  //elementos con foco antes de abrir cada popup

    //Bloquea el scroll del documento (se libera al cerrar el ultimo popup)
    function bloquearScroll() {
        if (popupsAbiertos === 0) {
            overflowPrevio = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
        }
        popupsAbiertos++;
    }

    //Libera el scroll del documento
    function liberarScroll() {
        if (popupsAbiertos > 0) { popupsAbiertos--; }
        if (popupsAbiertos === 0) { document.body.style.overflow = overflowPrevio; }
    }

    //Cierra un overlay concreto (unico punto de cierre) y restaura el foco
    function cerrarOverlay(overlay) {
        if (!overlay || !overlay.classList.contains('show')) { return; }
        overlay.classList.remove('show');
        liberarScroll();
        //Devuelvo el foco al elemento que lo tenia antes de abrir
        const previo = pilaFoco.pop();
        if (previo && previo !== document.body && typeof previo.focus === 'function') {
            try { previo.focus({ preventScroll: true }); } catch (error) { previo.focus(); }
        }
    }

    /* ABRIR PopupModal */
    function openPopupModal(idModal) {
        const popupModal = document.getElementById(idModal);
        if (!popupModal) {
            warnFNCS('openPopupModal: no existe el modal "' + idModal + '"');
            return;
        }
        //Guardo el foco actual para devolverlo al cerrar
        pilaFoco.push(document.activeElement);
        //Se muestra
        popupModal.classList.add('show');
        //Accesibilidad basica (antes no se marcaba nada)
        popupModal.setAttribute('role', 'dialog');
        popupModal.setAttribute('aria-modal', 'true');
        bloquearScroll();
        //Foco al contenido del popup
        const contenedor = popupModal.querySelector('.PopupModal') || popupModal;
        if (!contenedor.hasAttribute('tabindex')) { contenedor.setAttribute('tabindex', '-1'); }
        try { contenedor.focus({ preventScroll: true }); } catch (error) { contenedor.focus(); }
    }

    /* CERRAR PopupModal */
    function closePopupModal(idModal) {
        const popupModal = document.getElementById(idModal);
        if (!popupModal) {
            warnFNCS('closePopupModal: no existe el modal "' + idModal + '"');
            return;
        }
        cerrarOverlay(popupModal);
    }

    /**********************************************************/
    /* CERRAR AL HACER CLICK FUERA DEL MODAL */
    document.querySelectorAll('.PopupModal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) { cerrarOverlay(overlay); }
        });
    });

    /* CERRAR CON ESCAPE */
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') { return; }
        document.querySelectorAll('.PopupModal-overlay.show').forEach(function (overlay) {
            cerrarOverlay(overlay);
        });
    });

    /* BOTONES DE CIERRE (delegado: cubre tambien las vistas cargadas con .load()) */
    document.addEventListener('click', function (event) {
        //El target puede no ser un Element (documento, nodo de texto, etc.)
        const target = event.target;
        if (!target || typeof target.closest !== 'function') { return; }
        //Busco el boton de cierre
        const button = target.closest('[data-modal-close]');
        if (!button) { return; }
        //Si esta dentro de un modal de Bootstrap se delega en Bootstrap
        const modal = button.closest('.modal');
        if (modal) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bootstrapModal = bootstrap.Modal.getInstance(modal);
                if (bootstrapModal) { bootstrapModal.hide(); }
            } else {
                warnFNCS('functions: Bootstrap no esta disponible para cerrar el modal');
            }
            return;
        }
        //Si esta dentro de un PopupModal se cierra el overlay
        const popupModal = button.closest('.PopupModal-overlay');
        if (popupModal) { cerrarOverlay(popupModal); }
    });

    /**********************************************************/
    //Copia el texto indicado al portapapeles (requiere contexto seguro: HTTPS o localhost)
    function copiarTexto(texto) {
        navigator.clipboard.writeText(texto)
            .then(() => {
                console.log('Texto copiado correctamente');
                notiBubble('Texto copiado correctamente.', 'success', false);
            })
            .catch((error) => {
                console.error('No fue posible copiar el texto:', error);
                notiBubble('No fue posible copiar el texto.', 'error', false);
            });
    }

    /**********************************************************/
    //API publica: se expone en window para poder usarla desde las vistas PHP
    //(onclick="...", addEventListener, etc.). Los helpers nuevos quedan privados.
    window.addElement                 = addElement;
    window.SendDataForms              = SendDataForms;
    window.SendDataFormsFiles         = SendDataFormsFiles;
    window.SendDataOptions            = SendDataOptions;
    window.SendDataErrors             = SendDataErrors;
    window.UpdateContentId            = UpdateContentId;
    window.showConfirmRedirect        = showConfirmRedirect;
    window.appendFiles                = appendFiles;
    window.return_value               = return_value;
    window.exportTableToExcel         = exportTableToExcel;
    window._marcarOpcion              = _marcarOpcion;
    window._buscarOpcion              = _buscarOpcion;
    window.seleccionarOpcionYBloquear = seleccionarOpcionYBloquear;
    window.reestablecerSelect         = reestablecerSelect;
    window.normalizarURL              = normalizarURL;
    window.escapeHtml                 = escapeHtml;
    window.openPopupModal             = openPopupModal;
    window.closePopupModal            = closePopupModal;
    //Nuevo helper publico: permite que main.js reutilice la configuracion de
    //tablas en lugar de mantener su propia copia (llamado dentro de DOMContentLoaded)
    window.initDataTables             = initDataTables;
    window.copiarTexto                = copiarTexto;

    /**********************************************************/
    //CSRF: inyecta el token en todas las peticiones y formularios.
    //- Encabezado X-CSRF-Token global para jQuery ($.ajax / SendDataForms).
    //- Campo oculto _token en todos los formularios (respaldo para envios nativos
    //  y para serialize()/FormData que no pasan por el encabezado).
    (function initCsrf() {
        //Nombre del campo oculto (debe coincidir con ConfigAPP::APP['csrfTokenName'])
        const FIELD = '_token';
        //Encabezado por defecto (se refresca por peticion con el prefilter de abajo)
        if (typeof $ !== 'undefined' && $.ajaxSetup) {
            $.ajaxSetup({ headers: { 'X-CSRF-Token': currentCsrfToken() } });
        }
        //Prefilter global: inyecta el token ACTUAL en TODAS las peticiones jQuery
        // (cubre SendDataForms, $.ajax directo de las vistas, etc.) y, si la
        // respuesta trae un token nuevo, lo adopta (p. ej. tras csrfTest, que rota).
        if (typeof $ !== 'undefined' && $.ajaxPrefilter) {
            $.ajaxPrefilter(function (opts) {
                const verb = String(opts.type || opts.method || 'GET').toUpperCase();
                if (verb === 'GET' || verb === 'HEAD' || verb === 'OPTIONS') { return; }
                const tok = currentCsrfToken();
                if (tok) {
                    opts.headers = opts.headers || {};
                    opts.headers['X-CSRF-Token'] = tok;
                    if (opts.data && typeof opts.data === 'object' && !(window.FormData && opts.data instanceof window.FormData)) {
                        opts.data._token = tok;
                    }
                }
                //Si la respuesta trae un token nuevo, se adopta
                const prev = opts.success;
                opts.success = function (data, textStatus, jqXHR) {
                    const newTok = (jqXHR && jqXHR.getResponseHeader && jqXHR.getResponseHeader('X-CSRF-Token'))
                                || (data && data.data && data.data.Token);
                    if (newTok && typeof window !== 'undefined') { window.CSRF_TOKEN = newTok; }
                    if (typeof prev === 'function') { prev.apply(this, arguments); }
                };
            });
        }
        //Agrega/actualiza el campo oculto del formulario con el token ACTUAL
        function ensureField(form) {
            if (!form || form.nodeName !== 'FORM') { return; }
            let input = form.querySelector('input[data-csrf="1"]');
            if (!input) {
                input  = document.createElement('input');
                input.type   = 'hidden';
                input.name   = FIELD;
                input.setAttribute('data-csrf', '1');
                form.appendChild(input);
            }
            //Siempre se refresca: evita un token viejo si la sesion lo roto
            input.value = currentCsrfToken();
        }
        //Agrega el campo a los formularios indicados (por defecto, todos los del documento)
        window.injectCsrfFields = function (root) {
            const scope = root || document;
            if (!scope.querySelectorAll) { return; }
            scope.querySelectorAll('form').forEach(ensureField);
        };
        //Inyeccion inicial (soporta scripts cargados antes o despues del DOM)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => window.injectCsrfFields(document));
        } else {
            window.injectCsrfFields(document);
        }
        //Inyeccion justo antes del submit (fase de captura: corre antes de los
        //handlers de las vistas, de modo que serialize() ya incluye el token).
        //Cubre tambien formularios creados dinamicamente (modales, fragmentos).
        document.addEventListener('submit', (e) => ensureField(e.target), true);
    })();
})();
