<?php

namespace Luma\DatabaseComponent\Model;

use Luma\DatabaseComponent\Attributes\Column;
use Luma\DatabaseComponent\DatabaseConnection;

class Aurora
{
    protected static ?string $schema = null;
    protected static ?string $table = null;
    protected static ?string $identifier = null;
    protected static ?DatabaseConnection $connection = null;

    /**
     * @param DatabaseConnection $connection
     *
     * @return void
     */
    public static function setDatabaseConnection(DatabaseConnection $connection): void
    {
        static::$connection = $connection;
    }

    /**
     * @return DatabaseConnection
     */
    public static function getDatabaseConnection(): DatabaseConnection
    {
        return static::$connection;
    }

    /**
     * @param int $id
     *
     * @return ?static
     *
     * @throws \Exception
     */
    public static function find(int $id): ?self
    {
        $className = explode('\\', static::class);
        $className = end($className);

        $query = static::getDatabaseConnection()->getConnection()->prepare(
            sprintf(
                "SELECT * FROM %s%s WHERE %s = :id",
                static::$schema ? static::$schema . '.' : '',
                static::$table ?? $className,
                static::$identifier ?? 'id'
            )
        );

        $query->execute(['id' => $id]);
        $query->setFetchMode(\PDO::FETCH_CLASS, static::class);

        $result = $query->fetch();

        if (!$result) {
            return null;
        }

        try {
            $reflector = new \ReflectionClass($result);

            foreach ($reflector->getProperties() as $property) {
                $attribute = $property->getAttributes(Column::class)[0] ?? null;

                if ($attribute) {
                    $columnName = $attribute->newInstance()->name;

                    if ($columnName === $property->getName()) {
                        continue;
                    }

                    $propertyType = $property->getType();

                    if ($propertyType && !$propertyType->isBuiltin()) {
                        /**
                         * @var Aurora $associatedClassName
                         */
                        $associatedClassName = $propertyType->getName();
                        $associatedObject = $associatedClassName::find($result->$columnName);
                        $property->setValue($result, $associatedObject);
                    } else {
                        $property->setValue($result, $result->$columnName);
                    }

                    unset($result->$columnName);
                }
            }
        } catch (\ReflectionException $exception) {
            throw new \Exception($exception->getMessage());
        }

        return $result;
    }
}