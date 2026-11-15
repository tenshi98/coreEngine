<?php

declare(strict_types=1);

/**
 * Autoloader PSR-4 para PHP-ML (php-ai/php-ml 0.10.0), sin Composer.
 * Mapea el namespace 'Phpml\' al directorio 'src/'.
 *
 * USO: require_once __DIR__ . '/php-ml/autoload.php';
 *
 * Nota: SVC/SVR usan los binarios libsvm (bin/libsvm) que NO se incluyen. Los demás
 * algoritmos (RandomForest, KNN, NaiveBayes, KMeans, regresión, etc.) funcionan sin ellos.
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'Phpml\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});
