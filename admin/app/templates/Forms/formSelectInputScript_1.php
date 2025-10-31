<script>

    <?php echo $MatrixData; ?>

    //Definición para los IDs
    const SELECT_PRINCIPAL_ID_<?php echo $RandName; ?>       = "<?php echo $name1; ?>";
    const SELECT_DEPENDIENTE_ID_<?php echo $RandName; ?>     = "<?php echo $name2; ?>";
    const SELECT_DEPENDIENTE_DIV_ID_<?php echo $RandName; ?> = "div_<?php echo $name2; ?>";
    const DEFAULT_DISPLAY_STYLE_<?php echo $RandName; ?>     = "<?php echo $XDisplay; ?>";

    // Valor preseleccionado
    let preselectedValue_<?php echo $RandName; ?> = 0; // Se inicializa con el valor 0 o el valor que venga del backend

    /**
     * Función que carga las opciones en el select dependiente.
     * @param {string} selectedValue - El valor seleccionado en el select principal.
     * @param {boolean} isInitialLoad - Indica si es la carga inicial (para aplicar preselectedValue_<?php echo $RandName; ?>).
     */
    function updateDependentSelect_<?php echo $RandName; ?>(selectedValue, isInitialLoad = false) {
        const selectDependiente = document.getElementById(SELECT_DEPENDIENTE_ID_<?php echo $RandName; ?>);
        const divDependiente = document.getElementById(SELECT_DEPENDIENTE_DIV_ID_<?php echo $RandName; ?>);

        // 1. Limpiar el select dependiente (forma moderna y rápida)
        selectDependiente.length = 0;

        // 2. Comprobar si hay un valor válido y datos
        const dataSet = MatrixData<?php echo $RandName; ?>[selectedValue];

        if (!selectedValue || !dataSet) {
            // No hay selección o no hay datos, ocultar y poner opción por defecto
            divDependiente.style.display = "none";

            // Agregar la opción por defecto
            const defaultOption = new Option("Seleccione una Opción", ""); // value: ""
            selectDependiente.add(defaultOption);

            return;
        }

        // 3. Insertar nuevos elementos (recorriendo los arrays internos)
        const { ids, data } = dataSet;
        let selectedIndexToSet_<?php echo $RandName; ?> = 0;

        // Recorrer e insertar (asumiendo que ids[i] y data[i] corresponden)
        for (let i = 0; i < ids.length; i++) {
            const value = ids[i];
            const text = data[i];

            const option = new Option(text, value);
            selectDependiente.add(option);

            // Si es la carga inicial, buscar el valor preseleccionado
            if (isInitialLoad && String(value) === String(preselectedValue_<?php echo $RandName; ?>) && value !== "") {
                // El índice i es la posición en el array de datos, que es el mismo índice en el <select>
                selectedIndexToSet_<?php echo $RandName; ?> = i;
            }
        }

        // 4. Mostrar el select y establecer la selección
        divDependiente.style.display = DEFAULT_DISPLAY_STYLE_<?php echo $RandName; ?>;

        // Para el cambio (onchange), siempre ir al primer elemento (índice 0, "Seleccione...")
        // Para la carga inicial, usar el índice encontrado.
        selectDependiente.selectedIndex = isInitialLoad ? selectedIndexToSet_<?php echo $RandName; ?> : 0;
    }


    // --- Inicialización y Eventos ---

    // 1. Asignar el evento onchange al select principal
    document.getElementById(SELECT_PRINCIPAL_ID_<?php echo $RandName; ?>).onchange = function() {
        updateDependentSelect_<?php echo $RandName; ?>(this.value, false); // No es carga inicial
    };

    // 2. Carga inicial al cargar la página (usando jQuery, ya que se está usando)
    $(document).ready(function(){
        const initialValue = document.getElementById(SELECT_PRINCIPAL_ID_<?php echo $RandName; ?>).value;
        // Si hay un valor inicial en el select principal, se intenta cargar el dependiente.
        if (initialValue) {
            // del ID de la comuna antes de este bloque.
            updateDependentSelect_<?php echo $RandName; ?>(initialValue, true); // Es carga inicial
        } else {
            // Asegurar que el select dependiente esté oculto si el principal está vacío
            document.getElementById(SELECT_DEPENDIENTE_DIV_ID_<?php echo $RandName; ?>).style.display = "none";
        }
    });


</script>
