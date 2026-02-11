<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class LoadConexionDB{
    /*****************************************************/
    //Conexion para MySQL
    public static function loadSQLConnection($arrConn){

        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%d;charset=%s",
                $arrConn["HOSTNAME"],
                $arrConn["PORT"],
                $arrConn["CHARSET"]
            );

            $options = [
                PDO::ATTR_ERRMODE             => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES    => false,
            ];

            $db_conn = new PDO($dsn, $arrConn["USERNAME"], $arrConn["PASSWORD"], $options);

            return true;
        } catch (PDOException $e) {

            return $e;
        }


    }


}
