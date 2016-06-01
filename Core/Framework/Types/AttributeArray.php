<?php
namespace AppBundle\Services\Core\Framework\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class AttributeArray extends Type
{
    const ATTRIBUTE_ARRAY = 'attribute_array';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        foreach ($value as $itemKey => $itemValue) {
            $value[$itemKey] = '[' . $itemValue . ']';
        }
        $attribute = implode(',', $value);
        return $attribute;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $attributes = explode($value);
        foreach ($attributes as $attrKey => $attrValue) {
            $attributes[$attrKey] = substr($attrValue, 1, -1);
        }
        return $attributes;
    }

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array $fieldDeclaration The field declaration.
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform The currently used database platform.
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'text';
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     *
     * @todo Needed?
     */
    public function getName()
    {
        return self::ATTRIBUTE_ARRAY;
    }
}