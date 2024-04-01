<?php

namespace BookneticVendor;

\spl_autoload_register(function ($class) {
    if (\strpos($class, 'BookneticVendor\\Gettext\\') !== 0) {
        return;
    }
    $file = __DIR__ . \str_replace('\\', \DIRECTORY_SEPARATOR, \substr($class, \strlen('BookneticVendor\\Gettext'))) . '.php';
    if (\is_file($file)) {
        require_once $file;
    }
});
