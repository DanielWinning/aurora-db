<?php

namespace Luma\AuroraDatabase\Model;

use Luma\AuroraDatabase\Attributes\Column;

class AuroraMapper
{
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
                $attribute = $property->getAttributes(Column::class)[0] ?? null;

                if ($attribute) {
                    $columnName = $attribute->newInstance()->name;

                    if (!array_key_exists($columnName, $fetchData)) continue;

                    $propertyType = $property->getType();

                    if ($propertyType && !$propertyType->isBuiltin()) {
                        $propertyClass = $propertyType->getName();

                        if (is_subclass_of($propertyClass, Aurora::class)) {
                            $associatedObject = $propertyClass::find($fetchData[$columnName]);
                            $property->setValue($aurora, $associatedObject);
                        } else {
                            $implementsDateTimeInterface = in_array(\DateTimeInterface::class, class_implements($propertyClass));

                            if ($propertyClass === \DateTimeInterface::class || $implementsDateTimeInterface) {
                                $property->setValue($aurora, new \DateTime($fetchData[$columnName]));
                            }
                        }
                    } else {
                        $property->setValue($aurora, $fetchData[$columnName]);
                    }

                    unset($fetchData[$columnName]);
                }
            }

            return $aurora;
        } catch (\ReflectionException|\Exception $exception) {
            error_log($exception->getMessage());

            return null;
        }
    }
}