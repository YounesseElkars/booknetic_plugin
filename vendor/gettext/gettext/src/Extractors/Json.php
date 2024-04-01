<?php

namespace BookneticVendor\Gettext\Extractors;

use BookneticVendor\Gettext\Translations;
use BookneticVendor\Gettext\Utils\MultidimensionalArrayTrait;
/**
 * Class to get gettext strings from json.
 */
class Json extends Extractor implements ExtractorInterface
{
    use MultidimensionalArrayTrait;
    /**
     * {@inheritdoc}
     */
    public static function fromString($string, Translations $translations, array $options = [])
    {
        $messages = \json_decode($string, \true);
        if (\is_array($messages)) {
            static::fromArray($messages, $translations);
        }
    }
}
