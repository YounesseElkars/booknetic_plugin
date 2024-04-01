<?php

namespace BookneticVendor\Gettext\Generators;

use BookneticVendor\Gettext\Translations;
use BookneticVendor\Gettext\Utils\DictionaryTrait;
class JsonDictionary extends Generator implements GeneratorInterface
{
    use DictionaryTrait;
    public static $options = ['json' => 0, 'includeHeaders' => \false];
    /**
     * {@parentDoc}.
     */
    public static function toString(Translations $translations, array $options = [])
    {
        $options += static::$options;
        return \json_encode(static::toArray($translations, $options['includeHeaders']), $options['json']);
    }
}
