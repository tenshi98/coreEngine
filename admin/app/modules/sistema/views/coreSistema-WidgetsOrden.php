<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">

    <div class="card">
        <div class="card-body pt-3">

            <h5 class="card-title"><i class="bi bi-grid-1x2"></i> Orden de Widgets de la Pantalla Principal</h5>
            <p class="text-muted">
                Arrastre los widgets para definir el orden en que se muestran y use el interruptor para activarlos o desactivarlos.
                Los widgets nuevos que se agreguen al sistema quedan automáticamente <strong>al final</strong> de la lista.
                <?php if(empty($data['isConfigured'])){ ?>
                    <br><em>Actualmente se usa el orden por defecto (alfabético por módulo).</em>
                <?php } ?>
            </p>

            <?php if(empty($data['arrWidgets'])){
                // Mensaje
                $Btn_Text = 'No hay widgets disponibles.';
                // Mostrar alerta
                return $data['Fnc_WidgetsCommon']->alertPostData(5, 4, 'bi bi-exclamation-triangle-fill', 0, $Btn_Text);
            } else { ?>

                <ul class="list-group mb-3" id="listaWidgets">
                    <?php foreach ($data['arrWidgets'] as $bloque) { ?>
                        <li class="list-group-item p-0 mb-2 widget-bloque" data-bloque="<?php echo htmlspecialchars($bloque['key'], ENT_QUOTES, 'UTF-8'); ?>">
                            <!-- Fila del bloque: manija para mover la unidad + interruptor maestro -->
                            <div class="d-flex align-items-center justify-content-between bg-light border-bottom px-3 py-2 bloque-header">
                                <span>
                                    <i class="bi bi-grip-vertical handle-bloque" style="cursor:move;" title="Arrastrar bloque"></i>
                                    <i class="bi bi-folder2-open me-1"></i>
                                    <strong><?php echo htmlspecialchars($bloque['key'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </span>
                                <span class="form-check form-switch m-0">
                                    <input class="form-check-input bloque-activo" type="checkbox" role="switch" <?php echo ((int)$bloque['activo']===1) ? 'checked' : ''; ?> aria-label="Activo">
                                </span>
                            </div>
                            <!-- Sub-widgets del bloque: cada uno con su interruptor y su manija -->
                            <ul class="list-group list-group-flush lista-items">
                                <?php foreach ($bloque['items'] as $item) { ?>
                                    <li class="list-group-item d-flex align-items-center justify-content-between ps-4 widget-item" data-key="<?php echo htmlspecialchars($item['key'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <span>
                                            <i class="bi bi-grip-vertical handle-item" style="cursor:move;" title="Arrastrar widget"></i>
                                            <?php echo htmlspecialchars($item['Nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <span class="form-check form-switch m-0">
                                            <input class="form-check-input item-activo" type="checkbox" role="switch" <?php echo ((int)$item['activo']===1) ? 'checked' : ''; ?> aria-label="Activo">
                                        </span>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                </ul>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" id="btnResetWidgets"><i class="bi bi-arrow-counterclockwise"></i> Restablecer orden por defecto</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarWidgets"><i class="bi bi-check-circle"></i> Guardar</button>
                </div>

            <?php } ?>

        </div>
    </div>

</div>

<style>
    /* Bloque completo durante el arrastre: invisible, pero conserva su alto porque
       Sortable calcula el reordenamiento con sus medidas (display:none lo rompe).
       Solo se ve el hueco donde caera; al soltar reaparece en su nueva posicion. */
    #listaWidgets .widget-placeholder { background: transparent; border: 2px dashed #EA5757 !important; }
    #listaWidgets .widget-placeholder > * { visibility: hidden; }
    #listaWidgets .widget-item.widget-placeholder > * { visibility: hidden; }
    /* Copia que sigue al mouse (fallbackOnBody: vive en el body, fuera del <ul>).
       Sortable fija su ancho en linea; se ensancha 30px sobre --widget-drag-width y se
       desplaza a la izquierda la mitad para que quede centrada sobre la lista. */
    .widget-dragging { list-style: none; opacity: .95 !important; background: #fff; border: 1px solid rgba(0,0,0,.125); border-radius: .25rem; box-shadow: 0 .5rem 1rem rgba(0,0,0,.15); width: var(--widget-drag-width) !important; transform: translateX(-15px); }
    /* Los sub-items quedan visualmente subordinados a su bloque */
    .widget-bloque .lista-items .widget-item { background: #fff; }
</style>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    const urlSaveWidgets = '<?php echo $BASE.'/mantencionSistema/sistema/widgetsOrden/save'; ?>';
    const urlListWidgets = '<?php echo $BASE.'/mantencionSistema/sistema/widgetsOrden/listAll'; ?>';
    /************************************/
    // Se activa el arrastre (Sortable anidado). El plugin cargado en el header
    // (bootstrap_fileinput) expone la libreria como KvSortable 1.5.1 (no como Sortable).
    // IMPORTANTE: esta version NO soporta el selector '> .clase' en 'draggable'
    // (su _closest() solo entiende '>*' exacto o un selector valido para matches();
    // '> .widget-bloque' nunca hace match y el drag no arranca). Por eso se usan
    // clases simples '.widget-bloque' / '.widget-item' y grupos distintos por nivel
    // para que bloques y sub-widgets no se mezclen entre si.
    // Se inicializa de inmediato, sin esperar DOMContentLoaded, porque este script
    // se ejecuta con la pagina ya cargada.
    //   - Nivel 1: se arrastran los bloques entre si (handle .handle-bloque).
    //   - Nivel 2: se arrastran los sub-widgets dentro de su bloque (handle .handle-item).
    function initWidgetsSortable() {
        const lista  = document.getElementById('listaWidgets');
        const Lib    = window.KvSortable || window.Sortable;
        if (!lista) {
            return false;
        }
        if (!Lib) {
            console.error('Orden de Widgets: no se encontro la libreria de arrastre (KvSortable).');
            return false;
        }
        // Si la vista se recarga por AJAX, se destruyen instancias previas
        // para no duplicar listeners (KvSortable guarda la instancia en el DOM).
        const destruirPrevias = function (el) {
            if (el && el._sortable && typeof el._sortable.destroy === 'function') {
                try { el._sortable.destroy(); } catch (e) { /* se reintenta crear */ }
            }
            // KvSortable 1.5.1 usa expando 'KvSortable<timestamp>' (no '_sortable');
            // se barren las propiedades del nodo buscando la instancia viva.
            for (const k in el) {
                if (Object.prototype.hasOwnProperty.call(el, k)
                    && k.indexOf('KvSortable') === 0
                    && el[k] && typeof el[k].destroy === 'function') {
                    try { el[k].destroy(); } catch (e) { /* se reintenta crear */ }
                }
            }
        };
        destruirPrevias(lista);
        Array.prototype.forEach.call(lista.querySelectorAll('.lista-items'), destruirPrevias);
        // Opciones comunes (fallback: el arrastre nativo HTML5 de esta version
        // 1.5.1 toma el elemento pero no reordena en este layout; el modo fallback
        // mueve con el mouse/touch).
        const commonOpts = {
            animation: 150, forceFallback: true, fallbackTolerance: 3,
            ghostClass: 'widget-placeholder', chosenClass: 'widget-chosen',
            fallbackClass: 'widget-dragging', fallbackOnBody: true,
            onStart: function (evt) {
                document.documentElement.style.setProperty('--widget-drag-width', evt.item.getBoundingClientRect().width + 'px');
            }
        };
        // Nivel 1: orden entre bloques (solo hijos directos con esa clase)
        new Lib(lista, Object.assign({}, commonOpts, {
            handle: '.handle-bloque',
            draggable: '.widget-bloque',
            group: 'widgets-bloques'
        }));
        // Nivel 2: orden de sub-widgets dentro de cada bloque
        Array.prototype.forEach.call(lista.querySelectorAll('.lista-items'), function (sublista) {
            new Lib(sublista, Object.assign({}, commonOpts, {
                handle: '.handle-item',
                draggable: '.widget-item',
                group: 'widgets-items'
            }));
        });
        return true;
    }
    initWidgetsSortable();
    /************************************/
    // Se envian los datos
    function enviarWidgets(Informacion) {
        // Si ya se está ejecutando, salimos
        if (ejecutandoForm.valor) return;
        //Cambio los valores
        ejecutandoForm.valor = true;
        //Cargo el loader
        $('#PDloader').show();
        const Options = {
            Destino: urlListWidgets,
            showNoti: 'Orden de widgets guardado correctamente',
            closeObject: '#PDloader',
            changeValForm: ejecutandoForm,
        };
        SendDataForms('POST', urlSaveWidgets, Informacion, Options);
    }
    /************************************/
    // Guardar: se serializa la jerarquia bloques -> sub-widgets respetando el
    // orden del DOM (que refleja los arrastres) y el estado de cada interruptor.
    $("#btnGuardarWidgets").on('click', function () {
        const bloques = [];
        $("#listaWidgets > .widget-bloque").each(function () {
            const bloque = {
                key:    $(this).attr('data-bloque'),
                activo: $(this).find('> .bloque-header .bloque-activo').is(':checked') ? 1 : 0,
                items:  []
            };
            $(this).find('> .lista-items > .widget-item').each(function () {
                bloque.items.push({
                    key:    $(this).attr('data-key'),
                    activo: $(this).find('.item-activo').is(':checked') ? 1 : 0
                });
            });
            bloques.push(bloque);
        });
        enviarWidgets({ Config_Principal_Widgets: JSON.stringify(bloques) });
    });
    /************************************/
    // Restablecer orden por defecto
    $("#btnResetWidgets").on('click', function () {
        Swal.fire({
            title: "Restablecer orden",
            text: "Se volverá al orden por defecto y se activarán todos los widgets. ¿Desea continuar?",
            icon: "warning",
            confirmButtonColor: "#81A1C1",
            confirmButtonText: "<i class='bi bi-check-circle'></i> Si, restablecer",
            showCancelButton: true,
            cancelButtonText: "<i class='bi bi-x-circle'></i> Cancelar",
            cancelButtonColor: "#EA5757",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                enviarWidgets({ reset: 1 });
            }
        });
    });
</script>
