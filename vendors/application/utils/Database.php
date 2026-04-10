<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Clase Database
 * * Proporciona métodos estáticos para la creación y configuración de conexiones
 * a diversos motores de base de datos (MySQL, SQLite, MongoDB y Jig).
 * Utiliza una estructura de adaptador para abstraer la instanciación de controladores.
 */
class Database{
    /*****************************************************/
    /**
     * Establece una conexión con un servidor MySQL.
     * @param array $arrConn Arreglo con claves: HOSTNAME, USERNAME, PASSWORD, PORT, CHARSET, DATABASE.
     * @return DB\SQL Objeto de conexión SQL configurado para MySQL.
     */
    public static function getSQLConnection($arrConn){

        // Mapeo de parámetros desde el arreglo de configuración
        $BD_host      = $arrConn['HOSTNAME'];
        $BD_username  = $arrConn['USERNAME'];
        $BD_password  = $arrConn['PASSWORD'];
        // Asignación de valores por defecto si las claves son nulas o inexistentes
        $BD_port      = $arrConn['PORT'] ?? 3306;
        $BD_charset   = $arrConn['CHARSET'] ?? 'utf8mb4';
        $BD_database  = $arrConn['DATABASE'];

        // Instanciación del driver SQL con el DSN de MySQL y comandos de inicialización PDO
        $db_conn = new DB\SQL(
            'mysql:host='.$BD_host.';port='.$BD_port.';charset='.$BD_charset.';dbname='.$BD_database,
            $BD_username,
            $BD_password,
            array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8;')
        );

        // Retorno del recurso de conexión
        return $db_conn;
    }

    /*****************************************************/
    /**
     * Establece una conexión con una base de datos SQLite.
     * @param array $arrConn Arreglo con clave: ROUTE (ruta al archivo .db).
     * @return DB\SQL Objeto de conexión SQL configurado para SQLite.
     */
    public static function getSQLiteConnection($arrConn){

        // Recuperación de la ruta del archivo de base de datos
        $BD_route = $arrConn['ROUTE'];

        // Definición del DSN prefijado con el driver sqlite
        $db_conn = new DB\SQL('sqlite:'.$BD_route);

        // Retorno del recurso de conexión
        return $db_conn;
    }

    /*****************************************************/
    /**
     * Establece una conexión con un servidor MongoDB.
     * @param array $arrConn Arreglo con claves: HOST, DATABASE.
     * @return DB\Mongo Objeto de conexión específico para MongoDB.
     */
    public static function getMongoDBConnection($arrConn){

        // Extracción de parámetros de red y esquema
        $BD_host      = $arrConn['HOST'];
        $BD_database  = $arrConn['DATABASE'];

        // Instanciación del driver NoSQL con el protocolo mongodb
        $db_conn = new DB\Mongo('mongodb:'.$BD_host,$BD_database);

        // Retorno del recurso de conexión
        return $db_conn;
    }

    /*****************************************************/
    /**
     * Establece una conexión para el motor de base de datos plano Jig.
     * @param array $arrConn Arreglo con clave: ROUTE (directorio de almacenamiento).
     * @return DB\Jig Objeto de conexión Jig configurado para formato JSON.
     */
    public static function getJigConnection($arrConn){

        // Definición del directorio donde se alojan los archivos de datos
        $BD_route = $arrConn['ROUTE'];

        // Instanciación con persistencia forzada en formato JSON
        $db_conn = new DB\Jig($BD_route,DB\Jig::FORMAT_JSON);

        // Retorno del recurso de conexión
        return $db_conn;
    }

}
