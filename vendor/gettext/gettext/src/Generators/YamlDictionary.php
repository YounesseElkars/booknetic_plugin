<?php

namespace BookneticVendor\Gettext\Generators;

use BookneticVendor\Gettext\Translations;
use BookneticVendor\Gettext\Utils\DictionaryTrait;
use BookneticVendor\Symfony\Component\Yaml\Yaml as YamlDumper;
class YamlDictionary extends Generator implements GeneratorInterface
{
    use DictionaryTrait;
    public static $options = ['includeHeaders' => \false, 'indent' => 2, 'inline' => 3];
    /**
     * {@inheritdoc}
     */
    public static function toString(Translations $translations, array $options = [])
    {
        $options += static::$options;
        return YamlDumper::dump(static::toArray($translations, $options['includeHeaders']), $options['inline'], $options['indent']);
    }
}
