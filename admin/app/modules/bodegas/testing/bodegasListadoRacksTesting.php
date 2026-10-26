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

    private const CONTROLADOR          = 'bodegasListadoRacks';
    private const CONTROLADOR_PERMISOS = 'bodegasListado';
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

    private function assertTransaccion(array &$results, string $metodo){
        $fuente = $this->fuenteControlador();
        $this->addCase($results, 'transaccion_'.$metodo.'_begin', substr_count($fuente, 'Base_transactionBegin()') === 3,
            $metodo.'(): expected 3 Base_transactionBegin(), found '.substr_count($fuente, 'Base_transactionBegin()'));
        $this->addCase($results, 'transaccion_'.$metodo.'_commit', substr_count($fuente, 'Base_transactionCommit()') === 3,
            $metodo.'(): expected 3 Base_transactionCommit(), found '.substr_count($fuente, 'Base_transactionCommit()'));
        $this->addCase($results, 'transaccion_'.$metodo.'_rollback', substr_count($fuente, 'Base_transactionRollback()') === 0,
            $metodo.'(): expected 3 Base_transactionRollback(), found '.substr_count($fuente, 'Base_transactionRollback()'));

        $this->addCase($results, 'transaccion_'.$metodo.'_rollback_antes_error',
            str_contains($fuente, 'failOperation('));
        $this->addCase($results, 'transaccion_'.$metodo.'_commit_antes_exito',
            strpos($fuente, 'Base_transactionCommit()') !== false &&
            strpos($fuente, 'Response::success(') !== false &&
            strpos($fuente, 'Base_transactionCommit()') < strpos($fuente, 'Response::success('));
    }

    public function contratoMetodosTest(){
        $results = [];
        foreach (['Insert', 'Update', 'Delete'] as $metodo) {
            $this->assertTransaccion($results, $metodo);
        }
        return $results;
    }

    public function firmaMetodosTest(){
        $results = [];
        $this->assertMetodoFirma($results, 'Insert', 0, 1);
        $this->assertMetodoFirma($results, 'Update', 0, 1);
        $this->assertMetodoFirma($results, 'Delete', 0, 1);
        return $results;
    }

    public function permisosYEstructuraTest(){
        $results = [];
        $this->addCase($results, 'controller_nombre', self::CONTROLADOR_PERMISOS === 'bodegasListado',
            'el nombre del controlador de permisos no coincide');
        $this->addCase($results, 'vista_base_existe', is_file($this->rutaModulo('../views/'.self::VISTA_BASE.'-View.php')),
            'no existe la vista base '.self::VISTA_BASE.'-View.php');
        return $results;
    }
}
