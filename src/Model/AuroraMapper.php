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
                [$columnAttribute] = self::getAttributes($property);

                if (!$columnAttribute) continue;

                $aurora = self::handleColumnAttribute($columnAttribute, $fetchData, $property, $aurora);
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
                        if (isset($fetchData[$columnName]) && $fetchData[$columnName]) {
                            $associatedObject = $propertyClass::find($fetchData[$columnName]);
                            $property->setValue($aurora, $associatedObject);
                        }
                    }
                } elseif ($implementsDateTimeInterface || $propertyClass === \DateTimeInterface::class) {
                    $dateTime = is_null($fetchData[$columnName])
                        ? null
                        : \DateTime::createFromFormat($fetchData[$columnName]);
                    $property->setValue($aurora, $dateTime);
                }
            } else {
                $property->setValue($aurora, $fetchData[$columnName]);
            }
        }

        unset($fetchData[$columnName]);

        return $aurora;
    }

    /**
     * @param Aurora $aurora
     * @param string $associatedClassName
     *
     * @return Aurora
     */
    public static function fetchAssociated(
        Aurora &$aurora,
        string $associatedClassName
    ): Aurora {
        $reflector = new \ReflectionClass($aurora);

        foreach ($reflector->getProperties() as $property) {
            $attribute = $property->getAttributes(AuroraCollection::class)[0] ?? null;

            if (!$attribute) continue;

            $propertyType = $property->getType();
            $propertyClass = $propertyType->getName();
            $attributeInstance = $attribute->newInstance();

            if ($propertyClass !== Collection::class || $attributeInstance->getReferenceClass() !== $associatedClassName) {
                continue;
            }

            $associatedClass = $attributeInstance->getReferenceClass();
            $associatedClassProperty = $attributeInstance->getReferenceProperty();

            if (is_subclass_of($associatedClass, Aurora::class) && $associatedClassProperty) {
                if (in_array($associatedClass, self::$processedClasses)) {
                    return $aurora;
                }

                self::$processedClasses[] = $associatedClass;
                self::$parent = $aurora;

                $associatedObjects = $associatedClass::select()->whereIs($associatedClassProperty, $aurora->getId())->get();
                $property->setValue($aurora, $associatedObjects ? new Collection($associatedObjects) : new Collection([]));

                self::$parent = null;
            } else {
                $pivotSchema = $attributeInstance->getPivotSchema();
                $pivotTable = $attributeInstance->getPivotTable();
                $pivotColumn = $attributeInstance->getPivotColumn();

                if (!$pivotTable || !$pivotColumn || !$pivotSchema) {
                    continue;
                }

                try {
                    $associatedReflector = new \ReflectionClass($associatedClass);
                    $associatedReflectorInstance = $associatedReflector->newInstance();
                    $getAssociatedPrimaryIdentifierColumnName = $associatedReflector->getMethod('getPrimaryIdentifierColumnName');
                    $getPrimaryIdentifierColumnName = $reflector->getMethod('getPrimaryIdentifierColumnName');

                    $sql = sprintf(
                        'SELECT %s FROM %s WHERE %s = %d',
                        $getAssociatedPrimaryIdentifierColumnName->invoke($associatedReflectorInstance),
                        $associatedReflectorInstance::getSchema() ? $associatedReflectorInstance::getSchema() . '.' . $pivotTable : $pivotTable,
                        $getPrimaryIdentifierColumnName->invoke($reflector),
                        $aurora->getId()
                    );

                    $associatedIdsQuery = Aurora::getDatabaseConnection()->getConnection()->prepare($sql);
                    $associatedIdsQuery->execute();
                    $associatedIdsQuery->setFetchMode(\PDO::FETCH_NUM);

                    $associatedIds = $associatedIdsQuery->fetchAll();
                    if (count($associatedIds)) {
                        $associatedIds = array_map(function (array $id) {
                            return $id[0];
                        }, $associatedIds);

                        $associatedObjects = $associatedClass::select()->whereIn('id', $associatedIds)->get();

                        $property->setValue(
                            $aurora,
                            is_array($associatedObjects)
                                ? new Collection($associatedObjects)
                                : (
                            $associatedObjects instanceof Aurora
                                ? new Collection([$associatedObjects])
                                : new Collection([])
                            )
                        );
                    } else {
                        $property->setValue($aurora, new Collection());
                    }
                } catch (\ReflectionException $exception) {
                    error_log($exception);

                    return $aurora;
                }
            }
        }

        return $aurora;
    }
}