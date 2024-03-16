<?php

namespace Luma\AuroraDatabase\Model;

use Luma\AuroraDatabase\Attributes\Column;

class AuroraMapper
{
    /**
     * Used internally to map PDO results to Aurora instances.
     *
     * @param Aurora $aurora
     *
     * @return ?Aurora
     */
    public static function map(Aurora $aurora): ?Aurora
    {
        try {
            $reflector = new \ReflectionClass($aurora);

            foreach ($reflector->getProperties() as $property) {
                $attribute = $property->getAttributes(Column::class)[0] ?? null;

                if ($attribute) {
                    $columnName = $attribute->newInstance()->name;

                    if ($columnName === $property->getName()) {
                        continue;
                    }

                    $propertyType = $property->getType();

                    if ($propertyType && !$propertyType->isBuiltin()) {
                        $propertyClass = new ($propertyType->getName());

                        if ($propertyClass instanceof Aurora) {
                            $associatedClassName = $propertyType->getName();
                            $associatedObject = $associatedClassName::find($aurora->$columnName);
                            $property->setValue($aurora, $associatedObject);
                        } else {
                            // DateTime
                            if ($propertyClass instanceof \DateTimeInterface) {
                                $property->setValue($aurora, new \DateTime($aurora->$columnName));
                            }
                        }
                    } else {
                        $property->setValue($aurora, $aurora->$columnName);
                    }

                    unset($aurora->$columnName);
                }
            }

            return $aurora;
        } catch (\ReflectionException|\Exception $exception) {
            error_log($exception->getMessage());

            return null;
        }
    }
}