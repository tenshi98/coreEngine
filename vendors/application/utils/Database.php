<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class Database{
    /*****************************************************/
    //Conexion para MySQL
    public static function getSQLConnection($arrConn){

        //Variables
        $BD_host      = $arrConn['HOSTNAME'];
        $BD_username  = $arrConn['USERNAME'];
        $BD_password  = $arrConn['PASSWORD'];
        $BD_port      = $arrConn['PORT'] ?? 3306;
        $BD_charset   = $arrConn['CHARSET'] ?? 'utf8mb4';
        $BD_database  = $arrConn['DATABASE'];

        //declaro
        $db_conn = new DB\SQL(
            'mysql:host='.$BD_host.';port='.$BD_port.';charset='.$BD_charset.';dbname='.$BD_database,
            $BD_username,
            $BD_password,
            array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8;')
        );

        //devuelvo la conexion
        return $db_conn;
    }
    /*****************************************************/
    //Conexion para SQLite
    public static function getSQLiteConnection($arrConn){

        //Variables
        $BD_route = $arrConn['ROUTE'];

        //declaro
        $db_conn = new DB\SQL('sqlite:'.$BD_route);

        //devuelvo la conexion
        return $db_conn;
    }
    /*****************************************************/
    //Conexion para Mongo DB
    public static function getMongoDBConnection($arrConn){

        //Variables
        $BD_host      = $arrConn['HOST'];
        $BD_database  = $arrConn['DATABASE'];

        //declaro
        $db_conn = new DB\Mongo('mongodb:'.$BD_host,$BD_database);

        //devuelvo la conexion
        return $db_conn;
    }
    /*****************************************************/
    //Conexion para Jig
    public static function getJigConnection($arrConn){

        //Variables
        $BD_route = $arrConn['ROUTE'];

        //declaro
        $db_conn = new DB\Jig($BD_route,DB\Jig::FORMAT_JSON);

        //devuelvo la conexion
        return $db_conn;
    }

}
