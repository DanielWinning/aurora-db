<?php

namespace Luma\AuroraDatabase\Model;

use Luma\AuroraDatabase\Attributes\AuroraCollection;
use Luma\AuroraDatabase\Attributes\Column;
use Luma\AuroraDatabase\Utils\Collection;

class AuroraMapper
{
    private static array $processedClasses = [];
    private static ?Aurora $parent = null;

    /**
     * Used internally to map PDO results to Aurora instances.
     *
     * @param array $fetchData
     * @param string $mapToClass
     *
     * @return ?array
     */
    public static function map(array $fetchData, string $mapToClass): ?Aurora
    {
        try {
            $aurora = new $mapToClass;
            $reflector = new \ReflectionClass($aurora);

            foreach ($reflector->getProperties() as $property) {
                [$columnAttribute, $auroraCollectionAttribute] = self::getAttributes($property);

                if (!$columnAttribute && !$auroraCollectionAttribute) continue;

                $aurora = self::handleColumnAttribute($columnAttribute, $fetchData, $property, $aurora);
                $aurora = self::handleAuroraCollectionAttribute($auroraCollectionAttribute, $property, $aurora);
            }

            return $aurora;
        } catch (\ReflectionException|\Exception $exception) {
            error_log($exception->getMessage());

            return null;
        }
    }

    /**
     * @param \ReflectionProperty $property
     *
     * @return array
     */
    private static function getAttributes(\ReflectionProperty $property): array
    {
        return [
            $property->getAttributes(Column::class)[0] ?? null,
            $property->getAttributes(AuroraCollection::class)[0] ?? null,
        ];
    }

    /**
     * @param \ReflectionAttribute|null $columnAttribute
     * @param array $fetchData
     * @param \ReflectionProperty $property
     * @param Aurora $aurora
     *
     * @return Aurora
     *
     * @throws \Exception
     */
    private static function handleColumnAttribute(
        ?\ReflectionAttribute $columnAttribute,
        array $fetchData,
        \ReflectionProperty $property,
        Aurora &$aurora
    ): Aurora {
        if (!$columnAttribute) return $aurora;

        $columnName = $columnAttribute->newInstance()->getName();

        if (!array_key_exists($columnName, $fetchData)) return $aurora;

        $propertyType = $property->getType();

        if ($propertyType) {
            if (!$propertyType->isBuiltin()) {
                $propertyClass = $propertyType->getName();
                $implementsDateTimeInterface = in_array(\DateTimeInterface::class, class_implements($propertyClass));

                if (is_subclass_of($propertyClass, Aurora::class)) {
                    if (self::$parent) {
                        $property->setValue($aurora, self::$parent);
                    } else {
                        $associatedObject = $propertyClass::find($fetchData[$columnName]);
                        $property->setValue($aurora, $associatedObject);
                    }
                } elseif ($implementsDateTimeInterface || $propertyClass === \DateTimeInterface::class) {
                    $property->setValue($aurora, new \DateTime($fetchData[$columnName]));
                }
            } else {
                $property->setValue($aurora, $fetchData[$columnName]);
            }
        }

        unset($fetchData[$columnName]);

        return $aurora;
    }

    /**
     * @param \ReflectionAttribute|null $attribute
     * @param \ReflectionProperty $property
     * @param Aurora $aurora
     *
     * @return Aurora
     */
    private static function handleAuroraCollectionAttribute(
        ?\ReflectionAttribute $attribute,
        \ReflectionProperty $property,
        Aurora &$aurora
    ): Aurora {
        if (!$attribute) return $aurora;

        $propertyType = $property->getType();
        $propertyClass = $propertyType->getName();

        if ($propertyClass !== Collection::class) return $aurora;

        $attributeInstance = $attribute->newInstance();

        $associatedClass = $attributeInstance->getReferenceClass();
        $associatedClassProperty = $attributeInstance->getReferenceProperty();

        if (is_subclass_of($associatedClass, Aurora::class)) {
            if (in_array($associatedClass, self::$processedClasses)) {
                return $aurora;
            }
            self::$processedClasses[] = $associatedClass;
            self::$parent = $aurora;
            $associatedObjects = $associatedClass::select()->whereIs($associatedClassProperty, $aurora->getId())->get();
            $property->setValue($aurora, $associatedObjects ? new Collection($associatedObjects) : new Collection([]));
            self::$parent = null;
        }

        return $aurora;
    }
}