/**********************************************************/
//Se agrega elemento
function addElement(IDobjTo, IDobjclone, Node, select2, modalID, NInt){
    //se estancian los objetos a clonar
    let objTo    = document.getElementById(IDobjTo);
    let objclone = document.getElementById(IDobjclone),
    //se clonan los div
    clone = objclone.cloneNode(true);
    clone.id = Node+NInt;
    //inserto dentro del div deseado
    objTo.appendChild(clone);
    //Si hay que reactivar los select
    if (select2) {
        //reactivo
        let divSelect1 = $("#"+Node+NInt).find("select");
        divSelect1.addClass(select2+NInt);
        //intento
        try {
            //recorro
            for (var count = 1; count <= NInt; count++) {
                $("."+select2+count).select2({
                    dropdownParent: $("#"+modalID),
                    width: "100%",
                    language: "es"
                });
            }
        } catch (error) {
            console.log(error);
        }
    }
}
/**********************************************************/
//Se ejecuta formulario
function SendDataForms(Metodo, Direccion, Informacion, Options) {
    //log
    console.log('SendDataForms: ingreso');
    //consulto los datos
    $.ajax({
        method: Metodo,
        url: Direccion,
        data: Informacion,
    }).done(function(data, textStatus, jqXHR) {
        //Se ejecutan las opciones
        SendDataOptions(Options, jqXHR);
    }).fail(function(jqXHR, textStatus, errorThrown) {
        //Se ejecutan los errores
        SendDataErrors(Options, jqXHR, textStatus, errorThrown);
    });
}
/**********************************************************/
//Se ejecuta formulario para archivos
function SendDataFormsFiles(Metodo, Direccion, Informacion, Options) {
    //log
    console.log('SendDataForms: ingreso');
    //consulto los datos
    $.ajax({
        method: Metodo,
        url: Direccion,
        data: Informacion,
        processData: false,
        contentType: false,
    }).done(function(data, textStatus, jqXHR) {
        //Se ejecutan las opciones
        SendDataOptions(Options, jqXHR);
    }).fail(function(jqXHR, textStatus, errorThrown) {
        //Se ejecutan los errores
        SendDataErrors(Options, jqXHR, textStatus, errorThrown);
    });
}
/**********************************************************/
//Se ejecutan las opciones
function SendDataOptions(Options, jqXHR) {
    //log
    console.log('SendDataForms: ok');
    //Limpia el formulario
    if(typeof Options.ClearForm !== 'undefined' && Options.ClearForm != null && Options.ClearForm!=''){document.getElementById(Options.ClearForm).reset();}
    //redirige a otra ventana
    if(typeof Options.Destino !== 'undefined' && Options.Destino != null && Options.Destino!=''){window.location = Options.Destino;}
    //redirige a otra ventana desde una respuesta
    if(typeof Options.DestinoFrom !== 'undefined' && Options.DestinoFrom != null && Options.DestinoFrom!=''){
        //obtengo la respuesta
        var jsonData = JSON.parse(jqXHR.responseText);
        //redirijo a la nueva ubicacion
        window.location = Options.DestinoFrom + jsonData.message;
    }
    //Permite cargar informacion en un div
    if(typeof Options.UpdateDiv !== 'undefined' && Options.UpdateDiv != null && Options.UpdateDiv!=''){
        //Se recorre el array
        Options.UpdateDiv.forEach(element => {
            //Ejecuto
            let Div2     = element.Div;
            let URL2     = element.fromData;
            var Options2 = {};
            if(typeof element.refreshTbl !== 'undefined' && element.refreshTbl != null && element.refreshTbl!=''){
                Options2.refreshTables = element.refreshTbl;
            }
            if(typeof element.callFNC !== 'undefined' && element.callFNC != null && element.callFNC!=''){
                Options2.callFNC = element.callFNC;
            }
            //Se envian los datos al formulario
            UpdateContentId(Div2, URL2, Options2);
        });
    }
    //Permite cargar informacion en un div desde una respuesta
    if(typeof Options.UpdateDivFrom !== 'undefined' && Options.UpdateDivFrom != null && Options.UpdateDivFrom!=''){
        document.getElementById(Options.UpdateDivFrom).innerHTML = jqXHR.responseText;
    }
    //Mostrar Notificacion
    if(typeof Options.showNoti !== 'undefined' && Options.showNoti != null && Options.showNoti!=''){Swal.fire({icon: 'success',title: 'Notificación',html: Options.showNoti});}
    //Mueve a otro tab
    if(typeof Options.triggerTab !== 'undefined' && Options.triggerTab != null && Options.triggerTab!=''){$(Options.triggerTab).tab('show');}
    //Se muestra el modal
    if(typeof Options.showModal !== 'undefined' && Options.showModal != null && Options.showModal!=''){$(Options.showModal).modal('show');}
    //Se oculta el modal
    if(typeof Options.closeModal !== 'undefined' && Options.closeModal != null && Options.closeModal!=''){$(Options.closeModal).modal('hide');}
    //oculta todas las vistas
    if(typeof Options.colapseDiv !== 'undefined' && Options.colapseDiv != null && Options.colapseDiv!=''){$('.collapse').collapse("toggle");}
    //Se refrescan las tablas
    if(typeof Options.refreshTables !== 'undefined' && Options.refreshTables != null && Options.refreshTables!=''){
        //Se inician los selectores
        const select = (el, all = false) => {
            el = el.trim()
            if (all) {
                return [...document.querySelectorAll(el)]
            } else {
                return document.querySelector(el)
            }
        }
        //Reinicia las tablas
        const datatables = select('.datatable', true);
        datatables.forEach(datatable => {
            new simpleDatatables.DataTable(datatable, {
                perPage: 100,
                perPageSelect: [5, 10, 25, 50, 100, ["Todas", -1]],
                labels:{
                    placeholder:"Buscar...",
                    searchTitle:"Buscar dentro de la tabla",
                    perPage:"entradas por página",
                    pageTitle:"Página {page}",
                    noRows:"No se encontraron entradas",
                    noResults:"No hay resultados que coincidan con tu consulta de búsqueda",
                    info:"Mostrando {start} a {end} de {rows} entradas"
                }
            });
        })
    }
    //se llama a otra funcion
    if(typeof Options.callFNC !== 'undefined' && Options.callFNC != null && Options.callFNC!=''){
        //Evaluo si hay datos relacionados
        if(typeof Options.callFNCData !== 'undefined' && Options.callFNCData != null && Options.callFNCData!=''){
            //eval(Options.callFNC + "()");
            let functionObj   = window[Options.callFNC];
            let functionData  = Options.callFNCData;
            functionObj(functionData);
        }else{
            //eval(Options.callFNC + "()");
            let functionObj = window[Options.callFNC];
            functionObj();
        }
    }
    //Se muestra el objeto
    if(typeof Options.showObject !== 'undefined' && Options.showObject != null && Options.showObject!=''){$(Options.showObject).show();}
    //Se oculta el objeto
    if(typeof Options.closeObject !== 'undefined' && Options.closeObject != null && Options.closeObject!=''){$(Options.closeObject).hide();}
    //Se abre nueva pestaña
    if(typeof Options.openNewTab !== 'undefined' && Options.openNewTab != null && Options.openNewTab!=''){window.open(Options.openNewTab, '_blank');}
}
/**********************************************************/
//Se ejecutan las opciones
function SendDataErrors(Options, jqXHR, textStatus, errorThrown) {
    //log
    console.log('SendDataForms: error');
    //Se muestra el modal
    if(typeof Options.showModal !== 'undefined' && Options.showModal != null && Options.showModal!=''){$(Options.showModal).modal('show');}
    //Se oculta el modal
    if(typeof Options.closeModal !== 'undefined' && Options.closeModal != null && Options.closeModal!=''){$(Options.closeModal).modal('hide');}
    //oculta todas las vistas
    if(typeof Options.colapseDiv !== 'undefined' && Options.colapseDiv != null && Options.colapseDiv!=''){$('.collapse').collapse("toggle");}
    //Se muestra el objeto
    if(typeof Options.showObject !== 'undefined' && Options.showObject != null && Options.showObject!=''){$(Options.showObject).show();}
    //Se oculta el objeto
    if(typeof Options.closeObject !== 'undefined' && Options.closeObject != null && Options.closeObject!=''){$(Options.closeObject).hide();}
    /******************************/
    // Se verifica la respuesta
    if (jqXHR.responseText) {
        //variable
        let message = '';
        var jsonData = JSON.parse(jqXHR.responseText);
        //se verifica si resultado es un array
        if(Array.isArray(jsonData.message)===true){
            for (var i = 0; i < jsonData.message.length; i++) {
                var counter = jsonData.message[i];
                message += counter.message + '<br>';
            }
        //si no lo es solo se muestra
        }else{
            message = jsonData.message;
        }
        /******************************/
        //se muestra el mensaje
        Swal.fire({position: "top-end",timer: 5000,showConfirmButton: false,timerProgressBar: true,icon: 'error',html: message});
    }else {
        // jqXHR.responseText is not JSON data
        Swal.fire({position: "top-end",timer: 5000,showConfirmButton: false,timerProgressBar: true,icon: 'error',text: 'No existen datos.'});
    }
}
/**********************************************************/
//Se actualiza contenido del div
function UpdateContentId(Div, URL, Options=null) {
    $(Div).load(URL, function(responseTxt, statusTxt, xhr){
        if(statusTxt == "success"){
            //log
            console.log('UpdateContentId: success');
            //Mueve a otro tab
            if(typeof Options.triggerTab !== 'undefined' && Options.triggerTab != null && Options.triggerTab!=''){$(Options.triggerTab).tab('show');}
            //Se muestra el modal
            if(typeof Options.showModal !== 'undefined' && Options.showModal != null && Options.showModal!=''){$(Options.showModal).modal('show');}
            //Se oculta el modal
            if(typeof Options.closeModal !== 'undefined' && Options.closeModal != null && Options.closeModal!=''){$(Options.closeModal).modal('hide');}
            //Se refrescan las tablas
            if(typeof Options.refreshTables !== 'undefined' && Options.refreshTables != null && Options.refreshTables!=''){
                //Se inician los selectores
                const select = (el, all = false) => {
                    el = el.trim()
                    if (all) {
                        return [...document.querySelectorAll(el)]
                    } else {
                        return document.querySelector(el)
                    }
                }
                //Reinicia las tablas
                const datatables = select('.datatable', true);
                datatables.forEach(datatable => {
                    new simpleDatatables.DataTable(datatable, {
                        perPage: 100,
                        perPageSelect: [5, 10, 25, 50, 100, ["Todas", -1]],
                        labels:{
                            placeholder:"Buscar...",
                            searchTitle:"Buscar dentro de la tabla",
                            perPage:"entradas por página",
                            pageTitle:"Página {page}",
                            noRows:"No se encontraron entradas",
                            noResults:"No hay resultados que coincidan con tu consulta de búsqueda",
                            info:"Mostrando {start} a {end} de {rows} entradas"
                        }
                    });
                })
            }
            //se llama a otra funcion
            if(typeof Options.callFNC !== 'undefined' && Options.callFNC != null && Options.callFNC!=''){
                //eval(Options.callFNC + "()");
                let functionObj = window[Options.callFNC];
                functionObj();
            }
            //Se muestra el objeto
            if(typeof Options.showObject !== 'undefined' && Options.showObject != null && Options.showObject!=''){$(Options.showObject).show();}
            //Se oculta el objeto
            if(typeof Options.closeObject !== 'undefined' && Options.closeObject != null && Options.closeObject!=''){$(Options.closeObject).hide();}
        }
        if(statusTxt == "error"){
            //log
            console.log('UpdateContentId: error');
            // jqXHR.responseText is not JSON data
            Swal.fire({position: "top-end",timer: 5000,showConfirmButton: false,timerProgressBar: true,icon: 'error',text: xhr.status + ": " + xhr.statusText});
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
function return_value (value) {
	Valor = parseInt(value);
	return "$ " + Valor.toLocaleString('es-CL');
}
/**********************************************************/
//permite exportar a excel
function exportTableToExcel(tableID, filename = ''){
    var downloadLink;
    var dataType    = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML   = tableSelect.outerHTML.replace(/ /g, '%20');
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
    tableHTML = tableHTML.replace(/[áÁéÉíÍóÓúÚºñÑ]/g, match => charMap[match]);
    //se eliminan los saltos de linea
    var re = /<br *\/?>/gi;
    tableHTML = tableHTML.replace(re, ' | ');

    // Specify file name
    filename = filename?filename+'.xls':'excel_data.xls';

    // Create download link element
    downloadLink = document.createElement("a");

    document.body.appendChild(downloadLink);

    if(navigator.msSaveOrOpenBlob){
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob( blob, filename);
    }else{
        // Create a link to the file
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;

        // Setting the file name
        downloadLink.download = filename;

        //triggering the function
        downloadLink.click();
    }
}

















