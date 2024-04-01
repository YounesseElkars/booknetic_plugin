<?php

namespace BookneticVendor;

\spl_autoload_register(function ($class) {
    if (\strpos($class, 'BookneticVendor\\Gettext\\Languages\\') !== 0) {
        return;
    }
    $file = __DIR__ . \str_replace('\\', \DIRECTORY_SEPARATOR, \substr($class, \strlen('BookneticVendor\\Gettext\\Languages'))) . '.php';
    if (\is_file($file)) {
        require_once $file;
    }
});
