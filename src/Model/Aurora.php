<?php

namespace Luma\DatabaseComponent\Model;

use Luma\DatabaseComponent\Attributes\Column;
use Luma\DatabaseComponent\Attributes\Identifier;
use Luma\DatabaseComponent\DatabaseConnection;

class Aurora
{
    protected static ?string $schema = null;
    protected static ?string $table = null;
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
                static::getPrimaryIdentifierColumnName()
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

    /**
     * @param array $data
     *
     * @return static
     */
    public static function make(array $data): self
    {
        $instance = new static();
        $reflector = new \ReflectionClass($instance);

        foreach ($reflector->getProperties() as $property) {
            $propertyName = $property->getName();

            if (array_key_exists($propertyName, $data)) {
                $property->setValue($instance, $data[$propertyName]);
            }
        }

        return $instance;
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public static function getPrimaryIdentifierPropertyName(): string
    {
        return static::getPrimaryIdentifier()[0];
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public static function getPrimaryIdentifierColumnName(): string
    {
        return static::getPrimaryIdentifier()[1];
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    private static function getPrimaryIdentifier(): array
    {
        $instance = new static();
        $reflector = new \ReflectionClass($instance);

        foreach ($reflector->getProperties() as $property) {
            $identifier = $property->getAttributes(Identifier::class)[0] ?? null;

            if (!$identifier) continue;

            $propertyName = $property->getName();
            $columnName = $property->getAttributes(Column::class)[0] ?? $propertyName;

            if ($columnName instanceof \ReflectionAttribute) {
                $columnName = $columnName->getArguments()[0];
            }

            return [
                $propertyName,
                $columnName,
            ];
        }

        throw new \Exception('Invalid entity. Classes extending Aurora must contain an #[Identifier] attribute to set the primary key.');
    }

//    public function save()
//    {
//        $reflectionClass = new \ReflectionClass($this);
//
//        foreach ($reflectionClass->getProperties() as $property) {
//            var_dump($property->getName());
//
//            if ($property->getName() !== self::$identifier) {
//                var_dump($property->getValue($this));
//            }
//        }
//    }
}