<?php

namespace BookneticVendor\Gettext\Generators;

use BookneticVendor\Gettext\Translations;
use BookneticVendor\Gettext\Utils\MultidimensionalArrayTrait;
use BookneticVendor\Symfony\Component\Yaml\Yaml as YamlDumper;
class Yaml extends Generator implements GeneratorInterface
{
    use MultidimensionalArrayTrait;
    public static $options = ['includeHeaders' => \false, 'indent' => 2, 'inline' => 4];
    /**
     * {@inheritdoc}
     */
    public static function toString(Translations $translations, array $options = [])
    {
        $options += static::$options;
        return YamlDumper::dump(static::toArray($translations, $options['includeHeaders']), $options['inline'], $options['indent']);
    }
}
