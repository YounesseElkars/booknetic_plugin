<?php

namespace BookneticVendor\Gettext\Generators;

use BookneticVendor\Gettext\Translations;
use BookneticVendor\Gettext\Utils\MultidimensionalArrayTrait;
class Json extends Generator implements GeneratorInterface
{
    use MultidimensionalArrayTrait;
    public static $options = ['json' => 0, 'includeHeaders' => \false];
    /**
     * {@inheritdoc}
     */
    public static function toString(Translations $translations, array $options = [])
    {
        $options += static::$options;
        return \json_encode(static::toArray($translations, $options['includeHeaders'], \true), $options['json']);
    }
}
