<?php

namespace BookneticVendor\Gettext\Extractors;

use BookneticVendor\Gettext\Translations;
use BookneticVendor\Gettext\Utils\MultidimensionalArrayTrait;
use BookneticVendor\Symfony\Component\Yaml\Yaml as YamlParser;
/**
 * Class to get gettext strings from yaml.
 */
class Yaml extends Extractor implements ExtractorInterface
{
    use MultidimensionalArrayTrait;
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, Translations $translations, array $options = [])
    {
        $messages = YamlParser::parse($string);
        if (\is_array($messages)) {
            static::fromArray($messages, $translations);
        }
    }
}
