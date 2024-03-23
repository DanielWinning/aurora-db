<?php

namespace Luma\AuroraDatabase\Model;

use Luma\AuroraDatabase\Attributes\Column;
use Luma\AuroraDatabase\Attributes\Identifier;
use Luma\AuroraDatabase\Attributes\Schema;
use Luma\AuroraDatabase\Attributes\Table;
use Luma\AuroraDatabase\DatabaseConnection;

class Aurora
{
    protected static ?DatabaseConnection $connection = null;
    protected static string $queryString = '';
    protected static array $queryBindings = [];

    /**
     * @return int
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
     * @return static[]|null
     */
    public static function all(): array|null
    {
        $sql = sprintf('SELECT * FROM %s', static::getSchemaAndTableCombined());

        return static::executeQuery($sql);
    }

    /**
     * @param int $page
     * @param int $perPage
     * @param string|null $orderBy
     * @param string|null $orderDirection
     *
     * @return static[]|null
     */
    public static function paginate(int $page = 1, int $perPage = 10, string $orderBy = null, string $orderDirection = null): null|array
    {
        $offset = ($page - 1) * $perPage;

        $sql = sprintf(
            'SELECT * FROM %s',
            static::getSchemaAndTableCombined()
        );

        if ($orderBy) {
            try {
                $reflector = new \ReflectionClass(static::class);
                $property = $reflector->getProperty($orderBy);
                $columnAttribute = $property->getAttributes(Column::class)[0] ?? null;
                $columnName = $columnAttribute ? $columnAttribute->newInstance()->getName() : $orderBy;
                $sql .= sprintf(' ORDER BY %s %s', $columnName, $orderDirection ?? 'ASC');
            } catch (\ReflectionException $exception) {
                error_log($exception->getMessage());
            }
        }

        $sql = sprintf('%s LIMIT %d OFFSET %d', $sql, $perPage, $offset);

        return static::executeQuery($sql);
    }

    /**
     * @return int
     */
    public static function count(): int
    {
        $sql = sprintf(
            'SELECT COUNT(*) FROM %s',
            self::getSchemaAndTableCombined()
        );
        $query = static::getDatabaseConnection()->getConnection()->prepare($sql);
        $query->execute();
        $query->setFetchMode(\PDO::FETCH_NUM);

        $result = $query->fetch();

        return $result ? (int) $result[0] : 0;
    }

    /**
     * @param int $id
     *
     * @return static|null
     */
    public static function find(int $id): static|null
    {
        return static::executeQuery(static::getFindQueryString(), ['id' => $id]);
    }

    /**
     * @param string $property
     * @param string|int $value
     *
     * @return static|null
     *
     * @throws \ReflectionException
     */
    public static function findBy(string $property, string|int $value): static|null
    {
        $reflector = new \ReflectionClass(static::class);
        $reflectionProperty = $reflector->getProperty($property);
        $columnAttribute = $reflectionProperty->getAttributes(Column::class)[0] ?? null;

        if (!$columnAttribute) {
            throw new \Exception("Property {$property} does not exist or does not have a Column attribute.");
        }

        $columnName = $columnAttribute->newInstance()->getName();

        $sql = sprintf(
            'SELECT * FROM %s WHERE %s = :value LIMIT 1',
            static::getSchemaAndTableCombined(),
            $columnName
        );
        $result = static::executeQuery($sql, ['value' => $value]);

        return $result ?? null;
    }

    /**
     * @return static|null
     */
    public static function getLatest(): static|null
    {
        $sql = sprintf(
            'SELECT * FROM %s ORDER BY %s DESC LIMIT 1',
            static::getSchemaAndTableCombined(),
            static::getPrimaryIdentifierColumnName()
        );
        $latest = static::executeQuery($sql);

        return $latest ?? null;
    }

    /**
     * @param string[] $columns
     *
     * @return static
     */
    public static function select(array $columns = ['*']): static
    {
        $columns = array_map(function (string $columnName) {
            return static::getColumnNameByReflection($columnName);
        }, $columns);
        $primaryColumn = static::getPrimaryIdentifierColumnName();

        if (!in_array('*', $columns) && !in_array($primaryColumn, $columns)) {
            $columns[] = $primaryColumn;
        }

        $columns = implode(',', $columns);

        self::$queryString = sprintf('SELECT %s FROM %s', $columns, static::getSchemaAndTableCombined());

        return new static;
    }

    /**
     * @param string $column
     * @param string|int $value
     *
     * @return static
     */
    public function whereIs(string $column, string|int $value): static
    {
        return static::where($column, '=', $value);
    }

    /**
     * @param string $column
     * @param string|int $value
     *
     * @return static
     */
    public function whereNot(string $column, string|int $value): static
    {
        return static::where($column, '!=', $value);
    }

    /**
     * @param string $column
     * @param array $values
     *
     * @return static
     */
    public function whereIn(string $column, array $values): static
    {
        return static::where($column, 'IN', $values);
    }

    /**
     * @param string $column
     * @param array $values
     *
     * @return static
     */
    public function whereNotIn(string $column, array $values): static
    {
        return static::where($column, 'NOT IN', $values);
    }

    /**
     * @param string $column
     * @param string $operator
     * @param string|int|array $value
     *
     * @return static
     */
    protected function where(string $column, string $operator, string|int|array $value): static
    {
        if (str_contains(self::$queryString, 'WHERE')) {
            self::$queryString .= ' AND';
        } else {
            self::$queryString .= ' WHERE';
        }

        $column = static::getColumnNameByReflection($column);

        self::$queryString .= " {$column} {$operator} ";

        if (is_string($value)) {
            self::$queryString .= "'{$value}'";
        } elseif (is_array($value)) {
            self::$queryString .= '(';

            foreach ($value as $key => $singleValue) {
                if (!is_numeric($key)) continue;

                if (is_string($singleValue)) {
                    self::$queryString .= "'{$singleValue}'";
                } else {
                    self::$queryString .= $singleValue;
                }

                if ($key !== count($value) - 1) {
                    self::$queryString .= ',';
                }
            }

            self::$queryString .= ')';
        } else {
            self::$queryString .= $value;
        }

        return new static;
    }

    /**
     * @param string $column
     * @param string $direction
     *
     * @return static
     */
    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $column = static::getColumnNameByReflection($column);

        self::$queryString .= " ORDER BY {$column} {$direction}";

        return new static;
    }

    /**
     * @param int $limit
     *
     * @return static
     */
    public function limit(int $limit): static
    {
        self::$queryString .= " LIMIT {$limit}";

        return new static;
    }

    /**
     * Executes the built-up query string and returns the result.
     *
     * @return static|static[]|null
     */
    public function get(): static|array|null
    {
        return static::executeQuery(self::$queryString, self::$queryBindings);
    }

    /**
     * @param string $sql
     * @param ?array $params
     *
     * @return static|array|null
     */
    private static function executeQuery(string $sql, array $params = null): static|array|null
    {
        $query = static::getDatabaseConnection()->getConnection()->prepare($sql);

        if ($params && count($params)) {
            foreach ($params as $key => $value) {
                if (is_int($value)) {
                    $query->bindParam(sprintf(':%s', $key), $value, \PDO::PARAM_INT);
                } else {
                    $query->bindParam(sprintf(':%s', $key), $value);
                }
            }
        }

        $query->execute();
        $query->setFetchMode(\PDO::FETCH_ASSOC);

        $result = $query->fetchAll();

        static::$queryString = '';

        if (!$result) return null;

        if (count($result) === 1) {
            return AuroraMapper::map($result[0], static::class);
        }

        return array_map(function(array $aurora) {
            return AuroraMapper::map($aurora, static::class);
        }, $result);
    }

    /**
     * @return string
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
    public static function create(array $data): static
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
     * @return string|null
     */
    public static function getPrimaryIdentifierPropertyName(): string|null
    {
        $primaryIdentifier = static::getPrimaryIdentifier();

        return $primaryIdentifier ? $primaryIdentifier[0] : null;
    }

    /**
     * Returns the name of the primary identifier column as seen in the database. Set using the #[Identifier] attribute
     * on your Aurora's primary key property. All Aurora models require an #[Identifier] attribute.
     *
     * @return string|null
     */
    public static function getPrimaryIdentifierColumnName(): string|null
    {
        $primaryIdentifier = static::getPrimaryIdentifier();

        return $primaryIdentifier ? $primaryIdentifier[1] : null;
    }

    /**
     * @return array|null
     */
    private static function getPrimaryIdentifier(): array|null
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

        return null;
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

    /**
     * @return static
     */
    public function save(): static
    {
        if (isset($this->{static::getPrimaryIdentifierPropertyName()})) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * @return static|null
     */
    private function insert(): static|null
    {
        $reflector = new \ReflectionClass($this);

        $columns = [];
        $values = [];
        $params = [];

        foreach ($reflector->getProperties() as $property) {
            $columnAttribute = $property->getAttributes(Column::class)[0] ?? null;

            if ($columnAttribute && ($property->getName() !== static::getPrimaryIdentifierPropertyName())) {
                $columnAttribute = $columnAttribute->newInstance();
                $columnName = $columnAttribute->getName();

                if (!$property->isInitialized($this)) {
                    continue;
                } else {
                    $columns[] = $columnName;
                    $values[] = ':' . $columnName;
                    $value = $property->getValue($this);

                    if ($value instanceof Aurora) {
                        $value = $value->getId();
                    }

                    $params[$columnName] = $value;
                }
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
     * @return static
     */
    private function update(): static
    {
        $reflector = new \ReflectionClass($this);

        $columns = [];
        $params = [];

        foreach ($reflector->getProperties() as $property) {
            $column = $property->getAttributes(Column::class)[0] ?? null;

            if ($column && ($property->getName() !== static::getPrimaryIdentifierPropertyName())) {
                $columnName = $column->newInstance()->getName();
                $columns[] = $columnName . ' = :' . $columnName;

                $value = $property->getValue($this);

                if ($value instanceof Aurora) {
                    $value = $value->getId();
                }

                if ($value instanceof \DateTimeInterface) {
                    $value = $value->format(DATE_W3C);
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

    /**
     * @param string $propertyName
     *
     * @return string
     */
    private static function getColumnNameByReflection(string $propertyName): string
    {
        try {
            $reflector = new \ReflectionProperty(static::class, $propertyName);
            $columnAttribute = $reflector->getAttributes(Column::class)[0] ?? null;

            return $columnAttribute ? $columnAttribute->newInstance()->getName() : $propertyName;
        } catch (\ReflectionException $exception) {
            return $propertyName;
        }
    }
}