<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class Database{
    /*****************************************************/
    //Conexion para MySQL
    public static function getSQLConnection($arrConn){
        //declaro
        $db_conn = new DB\SQL(
            'mysql:host='.$arrConn["HOSTNAME"].';port='.$arrConn["PORT"].';dbname='.$arrConn["DATABASE"],
            $arrConn["USERNAME"],
            $arrConn["PASSWORD"],
            array(\PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES utf8;')
        );
        //devuelvo la conexion
        return $db_conn;
    }
    /*****************************************************/
    //Conexion para SQLite
    public static function getSQLiteConnection($arrConn){
        //declaro
        $db_conn = new DB\SQL('sqlite:'.$arrConn["ROUTE"]);
        //devuelvo la conexion
        return $db_conn;
    }
    /*****************************************************/
    //Conexion para Mongo DB
    public static function getMongoDBConnection($arrConn){
        //declaro
        $db_conn = new DB\Mongo('mongodb:'.$arrConn["HOST"],$arrConn["DATABASE"]);
        //devuelvo la conexion
        return $db_conn;
    }
    /*****************************************************/
    //Conexion para Jig
    public static function getJigConnection($arrConn){
        //declaro
        $db_conn = new DB\Jig($arrConn["ROUTE"],DB\Jig::FORMAT_JSON);
        //devuelvo la conexion
        return $db_conn;
    }

}
