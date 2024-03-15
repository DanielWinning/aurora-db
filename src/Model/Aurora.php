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
     * @return int
     *
     * @throws \Exception
     */
    public function getId(): int
    {
        return $this->{static::getPrimaryIdentifierPropertyName()};
    }

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
    public static function find(int $id): ?static
    {
        $aurora = static::executeQuery(static::getFindQueryString(), ['id' => $id]);

        return $aurora ? AuroraMapper::map($aurora) : null;
    }

    /**
     * @return ?static
     *
     * @throws \Exception
     */
    public static function getLatest(): ?static
    {
        $sql = sprintf(
            'SELECT * FROM %s ORDER BY %s DESC LIMIT 1',
            static::getSchemaAndTableCombined(),
            static::getPrimaryIdentifierColumnName()
        );
        $latest = static::executeQuery($sql);

        return $latest ? AuroraMapper::map($latest) : null;
    }

    /**
     * @param string $sql
     * @param ?array $params
     *
     * @return ?static
     */
    private static function executeQuery(string $sql, array $params = null): ?static
    {
        $query = static::getDatabaseConnection()->getConnection()->prepare($sql);
        $query->execute($params);
        $query->setFetchMode(\PDO::FETCH_CLASS, static::class);

        $result = $query->fetch();

        if (!$result) return null;

        return $result;
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    private static function getFindQueryString(): string
    {
        return sprintf(
            "SELECT * FROM %s WHERE %s = :id",
            static::getSchemaAndTableCombined(),
            static::getPrimaryIdentifierColumnName()
        );
    }

    /**
     * @return string
     */
    private static function getSchemaAndTableCombined(): string
    {
        $schema = static::getSchema();
        $table = static::getTable();

        if ($schema !== '') {
            $table = sprintf('%s.%s', $schema, $table);
        }

        return $table;
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

    public function save()
    {
        if (isset($this->{static::getPrimaryIdentifierPropertyName()})) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * @return static
     *
     * @throws \Exception
     */
    private function insert(): static
    {
        $reflector = new \ReflectionClass($this);

        $columns = [];
        $values = [];
        $params = [];

        foreach ($reflector->getProperties() as $property) {
            $column = $property->getAttributes(Column::class)[0] ?? null;

            if ($column && ($property->getName() !== static::getPrimaryIdentifierPropertyName())) {
                $columnName = $column->newInstance()->name;
                $columns[] = $columnName;
                $values[] = ':' . $columnName;

                $value = $property->getValue($this);

                if ($value instanceof Aurora) {
                    $value = $value->getId();
                }

                $params[$columnName] = $value;
            }
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::getSchemaAndTableCombined(),
            implode(',', $columns),
            implode(',', $values)
        );

        $query = static::$connection->getConnection()->prepare($sql);
        $query->execute($params);

        $this->{static::getPrimaryIdentifierPropertyName()}
            = static::getDatabaseConnection()->getConnection()->lastInsertId();

        return $this;
    }

    /**
     * @return $this
     *
     * @throws \Exception
     */
    private function update(): static
    {
        $reflector = new \ReflectionClass($this);

        $columns = [];
        $params = [];

        foreach ($reflector->getProperties() as $property) {
            $column = $property->getAttributes(Column::class)[0] ?? null;

            if ($column && ($property->getName() !== static::getPrimaryIdentifierPropertyName())) {
                $columnName = $column->newInstance()->name;
                $columns[] = $columnName . ' = :' . $columnName;

                $value = $property->getValue($this);

                if ($value instanceof Aurora) {
                    $value = $value->getId();
                }

                $params[$columnName] = $value;
            }
        }

        $params['id'] = $this->getId();

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :id',
            static::getSchemaAndTableCombined(),
            implode(', ', $columns),
            static::getPrimaryIdentifierColumnName()
        );

        $query = static::getDatabaseConnection()->getConnection()->prepare($sql);
        $query->execute($params);

        return $this;
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function delete(): bool
    {
        $sql = sprintf(
            'DELETE FROM %s WHERE %s = :id',
            static::getSchemaAndTableCombined(),
            static::getPrimaryIdentifierColumnName()
        );

        $query = static::getDatabaseConnection()->getConnection()->prepare($sql);
        return $query->execute(['id' => $this->getId()]);
    }
}