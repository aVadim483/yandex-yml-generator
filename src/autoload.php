<?php

spl_autoload_register(static function ($class) {
    $namespace = 'avadim\\YandexYmlGenerator\\';
    if (0 === strpos($class, $namespace)) {
        include __DIR__ . '/YandexYmlGenerator/' . str_replace($namespace, '', $class) . '.php';
    }
});

// EOF