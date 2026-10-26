<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/

class subpointLocation {

    var $pointOnVertex = true; // Check if the point sits exactly on one of the vertices?

    /**
     * Permite verificar si un punto de georeferencia se ubica dentro de una geocerca referenciada.
     * * Convierte las cadenas de coordenadas a arreglos, detecta coincidencia exacta con un
     * vértice y aplica el algoritmo de ray casting para determinar la posición del punto.
     *
     * @param string $point         Latitud y longitud separadas por un espacio (ej: '-37.085118 -72.739278').
     * @param array  $polygon       Geocerca definida: arreglo de puntos con el mismo formato que $point.
     * @param bool   $pointOnVertex Indica si se verifica la coincidencia exacta con un vértice (por defecto: true).
     *
     * @return string Posición del punto: 'inside', 'outside', 'boundary' o 'vertex'.
     *
     * @example
     * ```php
     * $polygon = [
     *     '-37.085118 -72.739278',
     *     '-37.281183 -72.832662',
     *     '-37.267195 -71.992208',
     *     '-36.858664 -71.964742',
     *     '-37.085118 -72.739278', //Se cierra la figura
     * ];
     * $pointLocation = new subpointLocation();
     * $pointLocation->pointInPolygon('-37.085118 -72.739278', $polygon); // Devuelve 'vertex'
     * $pointLocation->pointInPolygon('-37.1 -72.5', $polygon);           // Devuelve 'inside'
     * $pointLocation->pointInPolygon('-40.0 -70.0', $polygon);           // Devuelve 'outside'
     * ```
     */
    function pointInPolygon($point, $polygon, $pointOnVertex = true) {
        $this->pointOnVertex = $pointOnVertex;

        // Transform string coordinates into arrays with x and y values
        $point = $this->pointStringToCoordinates($point);
        $vertices = array();
        foreach ($polygon as $vertex) {
            $vertices[] = $this->pointStringToCoordinates($vertex);
        }

        // Check if the point sits exactly on a vertex
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }

        // Check if the point is inside the polygon or on the boundary
        $intersections = 0;
        $vertices_count = count($vertices);

        for ($i=1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i-1];
            $vertex2 = $vertices[$i];
            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Check if point is on an horizontal polygon boundary
                return "boundary";
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) {
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x'];
                if ($xinters == $point['x']) { // Check if point is on the polygon boundary (other than horizontal)
                    return "boundary";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++;
                }
            }
        }
        // If the number of edges we passed through is odd, then it's in the polygon.
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }

    /**
     * Verifica si un punto se ubica exactamente sobre alguno de los vértices de la geocerca.
     *
     * @param array $point   Coordenadas del punto con las claves 'x' e 'y'.
     * @param array $vertices Arreglo de coordenadas de los vértices a comparar.
     *
     * @return bool|null True si el punto coincide con un vértice; null si no coincide.
     *
     * @example
     * ```php
     * $pointLocation = new subpointLocation();
     * $pointLocation->pointOnVertex(['x' => '-37.085118', 'y' => '-72.739278'], [['x' => '-37.085118', 'y' => '-72.739278']]);
     * // Devuelve true
     * ```
     */
    function pointOnVertex($point, $vertices) {
        foreach($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }

    }

    /**
     * Convierte una cadena de coordenadas en un arreglo con los valores 'x' e 'y'.
     *
     * @param string $pointString Cadena con latitud y longitud separadas por un espacio
     *                            (ej: '-37.085118 -72.739278').
     *
     * @return array Arreglo asociativo ['x' => ..., 'y' => ...] con las coordenadas separadas.
     *
     * @example
     * ```php
     * $pointLocation = new subpointLocation();
     * $pointLocation->pointStringToCoordinates('-37.085118 -72.739278');
     * // Devuelve ['x' => '-37.085118', 'y' => '-72.739278']
     * ```
     */
    function pointStringToCoordinates($pointString) {
        $coordinates = explode(" ", $pointString);
        return array("x" => $coordinates[0], "y" => $coordinates[1]);
    }

    /**
     * Punto de entrada reservado del módulo (sin implementación por ahora).
     *
     * @return void
     *
     * @example
     * ```php
     * $pointLocation = new subpointLocation();
     * $pointLocation->pointLocation(); //No realiza ninguna acción
     * ```
     */
    function pointLocation() {
        //Nada de momento
    }

}
