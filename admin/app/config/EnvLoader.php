<?php
/*******************************************************************************************************************/
/*                    Carga las variables de entorno definidas en admin/app/config/.env                            */
/*       Se incluye (require_once) desde cada Config*.php de este directorio antes de usar getenv().               */
/*******************************************************************************************************************/
require_once __DIR__ . '/../../../vendors/libs/phpdotenv/vendor/autoload.php';

// Por defecto phpdotenv solo escribe en $_ENV/$_SERVER; se agrega el adapter de putenv()
// para que getenv() (usado en los Config*.php de este directorio) también vea los valores.
$repository = Dotenv\Repository\RepositoryBuilder::createWithDefaultAdapters()
    ->addAdapter(Dotenv\Repository\Adapter\PutenvAdapter::class)
    ->immutable()
    ->make();

Dotenv\Dotenv::create($repository, __DIR__)->load();
