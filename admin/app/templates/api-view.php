<?php
// Se eliminan datos innecesarios
function filterKeysByPrefix(array $data, string $prefix): array {
    return array_filter(
        $data,
        fn($key) => strpos($key, $prefix) !== 0,
        ARRAY_FILTER_USE_KEY
    );
}

// Se ejecuta
$dataX = filterKeysByPrefix($data, 'Fnc_');

// Se imprime
Response::success('Success', $dataX);
