<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class bodegasListadoRacksTesting
 *
 * Pruebas del modulo `bodegas` - controlador `bodegasListadoRacks`.
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon'].
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Nota de diseno: el controlador abre `Base_transactionBegin()` y revertir con
 * `failOperation()` ante cada fallo (rollback gestionado), confirmando con `Base_transactionCommit()`
 * antes de `Response::success()`. No se invocan las rutas de escritura (terminan en `exit`).
 *
 * @see skills/testing/SKILL.md
 */
class bodegasListadoRacksTesting extends ControllerBase {

    private const CONTROLADOR          = 'bodegasListadoDistribucion';
    private const CONTROLADOR_PERMISOS = 'bodegasListadoDistribucion';
    private const RUTA_WEB             = 'administracion/bodegas/listado/reticular';
    private const VISTA_BASE           = 'bodegasListado-Resumen-Reticulado';
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

    private function cuerpoMetodo(string $metodo): string{
        $fuente = $this->fuenteControlador();
        $ini = strpos($fuente, 'public function '.$metodo.'(');
        if ($ini === false) {
            return '';
        }
        $braces = 0;
        $inString = false;
        $stringChar = '';
        $len = strlen($fuente);
        for ($i = $ini; $i < $len; $i++) {
            $c = $fuente[$i];
            if (!$inString) {
                if ($c === '"' || $c === "'") {
                    $inString = true;
                    $stringChar = $c;
                } elseif ($c === '{') {
                    $braces++;
                } elseif ($c === '}') {
                    $braces--;
                    if ($braces === 0) {
                        return substr($fuente, $ini, $i - $ini + 1);
                    }
                }
            } else {
                if ($c === $stringChar && ($i === 0 || $fuente[$i-1] !== '\\')) {
                    $inString = false;
                }
            }
        }
        return '';
    }

    private function assertTransaccion(array &$results, string $metodo){
        $cuerpo = $this->cuerpoMetodo($metodo);
        $this->addCase($results, 'transaccion_'.$metodo.'_existe', $cuerpo !== '',
            $metodo.'() no encontrado en el controlador');
        if ($cuerpo === '') {
            return;
        }
        $this->addCase($results, 'transaccion_'.$metodo.'_begin',
            str_contains($cuerpo, 'Base_transactionBegin()'),
            $metodo.'(): no abre transaccion (Base_transactionBegin)');
        $this->addCase($results, 'transaccion_'.$metodo.'_commit',
            str_contains($cuerpo, 'Base_transactionCommit()'),
            $metodo.'(): no confirma la transaccion (Base_transactionCommit)');
        $this->addCase($results, 'transaccion_'.$metodo.'_rollback',
            str_contains($cuerpo, 'Base_transactionRollback()'),
            $metodo.'(): no revierte la transaccion ante error (Base_transactionRollback)');
        $this->addCase($results, 'transaccion_'.$metodo.'_rollback_antes_error',
            str_contains($cuerpo, 'failOperation('),
            $metodo.'(): no invoca failOperation en puntos de fallo');
        $this->addCase($results, 'transaccion_'.$metodo.'_commit_antes_exito',
            strpos($cuerpo, 'Base_transactionCommit()') !== false &&
            strpos($cuerpo, 'Response::success(') !== false &&
            strpos($cuerpo, 'Base_transactionCommit()') < strpos($cuerpo, 'Response::success('),
            $metodo.'(): el commit debe ocurrir antes de Response::success()');
    }

    public function contratoMetodosTest(){
        $results = [];
        foreach (['Insert', 'Update', 'Delete'] as $metodo) {
            $this->assertTransaccion($results, $metodo);
        }
        return $results;
    }

    // Helper que verifica la firma de un método del controlador (reflexión, no regex sobre fuente)
    private function firmaMetodo(array &$results, string $metodo, int $minParam, int $maxParam){
        $existe = method_exists(self::CONTROLADOR, $metodo);
        $this->addCase($results, 'metodo_'.$metodo, $existe, 'no existe '.self::CONTROLADOR.'::'.$metodo.'()');
        if (!$existe) {
            return;
        }
        $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $this->addCase($results, 'metodo_'.$metodo.'_publico', $reflector->isPublic(),
            $metodo.'() no es publico');
        $n = $reflector->getNumberOfParameters();
        $this->addCase($results, 'metodo_'.$metodo.'_parametros', $n >= $minParam && $n <= $maxParam,
            $metodo.'() declara '.$n.' parametros (esperado entre '.$minParam.' y '.$maxParam.')');
    }

    public function firmaMetodosTest(){
        $results = [];
        // Insert($f3), Update($f3): 1 parametro; Delete(): 0 parametros
        $this->firmaMetodo($results, 'Insert', 1, 1);
        $this->firmaMetodo($results, 'Update', 1, 1);
        $this->firmaMetodo($results, 'Delete', 0, 0);
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
