<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class ConfigData{
    /*****************************************************/
    //Variables para MySQL
    const MySQL_1 = [
        'HOSTNAME' => 'database',
        'USERNAME' => 'root',
        'PASSWORD' => 'docker',
        'DATABASE' => 'core_engine',
        'PORT'     => 3306,
    ];
    /*****************************************************/
    //Variables para MySQL
    const PostGreSQL_1 = [
        'HOSTNAME' => '127.0.0.1',
        'USERNAME' => 'root',
        'PASSWORD' => 'docker',
        'DATABASE' => 'core_engine',
        'PORT'     => 5432,
    ];
    /*****************************************************/
    //Variables para SQLite
    const SQLite_1 = [
        'ROUTE' => '/absolute/path/to/your/database.sqlite',
    ];
    /*****************************************************/
    //Variables para Mongo DB
    const MongoDB_1 = [
        'HOST'     => '127.0.0.1:27017',
        'DATABASE' => 'core_engine',
    ];
    /*****************************************************/
    //Variables para Jig
    const Jig_1 = [
        'ROUTE' => 'db/data/',
    ];
    /*****************************************************/
    //Variables para Redis
    const Redis_1 = [
        'ROUTE' => 'db/data/',
    ];


}