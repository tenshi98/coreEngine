<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class bodegasListadoDistribucionTesting
 *
 * Pruebas del modulo `bodegas` - controlador `bodegasListadoDistribucion`.
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon'].
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Nota de diseno: el controlador usa `$this->controllerName = 'bodegasListadoDistribucion'`
 * y los metodos de escritura abren transaccion, escriben en BD y terminan en `Response::*()` (`exit`).
 * Las verificaciones son por reflexion y lectura del fuente (sin ejecutar la logica de base de datos).
 *
 * @see skills/testing/SKILL.md
 */
class bodegasListadoDistribucionTesting extends ControllerBase {

    private const CONTROLADOR          = 'bodegasListadoDistribucion';
    private const CONTROLADOR_PERMISOS = 'bodegasListadoDistribucion';
    private const RUTA_WEB             = 'administracion/bodegas/distribucion';
    private const VISTA_BASE           = 'bodegasListado-Resumen-Distribucion';
    private const NIVELES              = [1, 2, 3, 4];

    public function __construct(){
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    private function addCase(array &$results, string $caso, bool $ok, string $razon = ''){
        $results[$caso] = $ok ? 'OK' : 'FALLO: '.$razon;
    }

    private function rutaModulo(string $sub = ''): string{
        return rtrim(__DIR__.'/'.$sub, '/');
    }

    private function codigoFuente(string $ruta): string{
        return is_file($ruta) ? file_get_contents($ruta) : '';
    }

    private function fuenteControlador(): string{
        return $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));
    }

    private function assertTransaccion(array &$results, string $metodo){
        $fuente = $this->fuenteControlador();
        $this->addCase($results, 'transaccion_'.$metodo.'_begin', substr_count($fuente, 'Base_transactionBegin()') === 1,
            $metodo.'(): expected 1 begin, found '.substr_count($fuente, 'Base_transactionBegin()'));
        $this->addCase($results, 'transaccion_'.$metodo.'_commit', substr_count($fuente, 'Base_transactionCommit()') === 1,
            $metodo.'(): expected 1 commit, found '.substr_count($fuente, 'Base_transactionCommit()'));
        $this->addCase($results, 'transaccion_'.$metodo.'_rollback', substr_count($fuente, 'Base_transactionRollback()') === 0,
            $metodo.'(): expected 0 inline rollback (failOperation), found '.substr_count($fuente, 'Base_transactionRollback()'));

        $this->addCase($results, 'transaccion_'.$metodo.'_rollback_antes_error',
            str_contains($fuente, 'failOperation('));
        $this->addCase($results, 'transaccion_'.$metodo.'_commit_antes_exito',
            strpos($fuente, 'Base_transactionCommit()') !== false &&
            strpos($fuente, 'Response::success(') !== false &&
            strpos($fuente, 'Base_transactionCommit()') < strpos($fuente, 'Response::success('));
    }

    public function contratoMetodosTest(){
        $results = [];
        foreach (['PosicionUpdate'] as $metodo) {
            $this->assertTransaccion($results, $metodo);
        }
        return $results;
    }



    // Helper que verifica la firma de un método del controlador
    private function assertMetodoFirma(array &$results, string $metodo, int $parametrosEsperados, int $valoresRetornadosEsperados){
        $results[$metodo.'_ firma'] = false;
        $fuente = $this->fuenteControlador();
        // Buscar la definición del método en el fuente del controlador
        $patron = "/function " . $metodo . "(\\([^)]*\\))/";
        if (preg_match($patron, $fuente, $matches)) {
            $parametrosActuales = count(explode(",", $matches[1]));
            // Contar return simples en el método
            $contador = 0;
            $lineas = explode("\\n", $fuente);
            foreach ($lineas as $linea) {
                if (trim(substr($linea, 0, 6)) === "return") {
                    $contador++;
                }
            }
            if ($parametrosActuales === $parametrosEsperados && $contador === $valoresRetornadosEsperados) {
                $results[$metodo.'_ firma'] = true;
            } else {
                $razon = "método " . $metodo . ": esperado " . $parametrosEsperados . " parámetros y " . $valoresRetornadosEsperados . " retorno, ";
                $razon .= "encontrados " . (isset($matches[1]) ? count(explode(",", $matches[1])) : 0) . " parámetros y " . $contador . " retorno";
                $results[$metodo.'_ firma'] = "FALLO: " . $razon;
            }
        } else {
            $results[$metodo.'_ firma'] = "FALLO: método " . $metodo . " no encontrado en el controlador";
        }
    }

    public function firmaMetodosTest(){
        $results = [];
        // PosicionUpdate tiene 1 parámetro ($f3) y 0 return simples (usa Response:: calls)
        $this->assertMetodoFirma($results, 'PosicionUpdate', 1, 0);
        return $results;
    }

    public function permisosYEstructuraTest(){
        $results = [];
        $this->addCase($results, 'controller_nombre', self::CONTROLADOR_PERMISOS === 'bodegasListadoDistribucion',
            'el nombre del controlador de permisos no coincide');
        $this->addCase($results, 'vista_base_existe', is_file($this->rutaModulo('../views/'.self::VISTA_BASE.'-View.php')),
            'no existe la vista base '.self::VISTA_BASE.'-View.php');
        return $results;
    }
}
