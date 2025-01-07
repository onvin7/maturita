<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/'; // Základní složka pro aplikaci

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // Třída nepatří do prostoru App
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
