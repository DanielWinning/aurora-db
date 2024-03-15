<?php

namespace Luma\DatabaseComponent\Model;

use Luma\DatabaseComponent\Attributes\Column;
use Luma\DatabaseComponent\Attributes\Identifier;
use Luma\DatabaseComponent\Attributes\Schema;
use Luma\DatabaseComponent\Attributes\Table;
use Luma\DatabaseComponent\DatabaseConnection;

class Aurora
{
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
        $query = static::getDatabaseConnection()->getConnection()->prepare(static::getFindQueryString($id));

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
     * @param int $id
     * @return string
     *
     * @throws \Exception
     */
    private static function getFindQueryString(int $id): string
    {
        $schema = static::getSchema();
        $table = static::getTable();

        if ($schema !== '') {
            $table = sprintf('%s.%s', $schema, $table);
        }

        return sprintf(
            "SELECT * FROM %s WHERE %s = :id",
            $table,
            static::getPrimaryIdentifierColumnName()
        );
    }

    /**
     * Returns a new instance without saving.
     *
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
     * Returns the name of the primary identifier column as seen in the database. Set using the #[Identifier] attribute
     * on your Aurora's primary key property. All Aurora models require an #[Identifier] attribute.
     *
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

    /**
     * @return string
     */
    public static function getSchema(): string
    {
        return static::getClassAttribute(Schema::class, 'schema');
    }

    /**
     * @return string
     */
    public static function getTable(): string
    {
        $table = static::getClassAttribute(Table::class, 'table');

        if (empty($table)) {
            $className = explode('\\', static::class);
            $table = end($className);
        }

        return $table;
    }

    /**
     * @param string $attributeFQCN
     * @param string $propName
     *
     * @return string
     */
    private static function getClassAttribute(string $attributeFQCN, string $propName): string
    {
        $reflector = new \ReflectionClass(static::class);
        $attributes = $reflector->getAttributes($attributeFQCN);

        return $attributes ? $attributes[0]->newInstance()->$propName : '';
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