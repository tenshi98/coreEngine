<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class coreSistemaTesting extends ControllerBase {

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                 EJECUCION                                  */
    /******************************************************************************/


    /*******************************************************************/
    // Pruebas de la proteccion CSRF (CsrfToken)
    // Devuelve un arreglo [caso => 'OK'|'FALLO: ...'] para verificacion manual.
    /*******************************************************************/
    public function csrfTest($f3){

        // Resultados
        $results = [];

        // Token de partida
        $original = CsrfToken::getToken($f3);

        // 1) Persistencia: dos llamadas devuelven el mismo token
        $results['persistencia'] = (CsrfToken::getToken($f3) === $original) ? 'OK' : 'FALLO: el token no es estable en la sesion';

        // 2) Validacion correcta
        $results['valida_token_correcto'] = (CsrfToken::validate($f3, $original) === true) ? 'OK' : 'FALLO: no valida un token correcto';

        // 3) Rechazo de token incorrecto
        $results['rechaza_token_incorrecto'] = (CsrfToken::validate($f3, 'token-invalido') === false) ? 'OK' : 'FALLO: acepta un token incorrecto';

        // 4) Rechazo de token vacio
        $results['rechaza_token_vacio'] = (CsrfToken::validate($f3, '') === false) ? 'OK' : 'FALLO: acepta un token vacio';

        // 5) Rotacion: genera un token distinto y valido
        $nuevo = CsrfToken::rotate($f3);
        $results['rotacion'] = ($nuevo !== $original && CsrfToken::validate($f3, $nuevo) === true) ? 'OK' : 'FALLO: la rotacion no genero un token valido';

        // 6) Limpieza: el token rotado deja de ser valido tras clear
        CsrfToken::clear($f3);
        $results['limpieza'] = (CsrfToken::validate($f3, $nuevo) === false) ? 'OK' : 'FALLO: el token sigue valido tras clear';

        // Se regenera un token limpio para la sesion
        CsrfToken::rotate($f3);

        // Retorno
        return $results;
    }

}
