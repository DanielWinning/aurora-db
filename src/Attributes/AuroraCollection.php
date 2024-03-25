<?php

namespace Luma\AuroraDatabase\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AuroraCollection
{
    private string $referenceClass;
    private ?string $referenceProperty;
    private ?string $pivotTable;
    private ?string $pivotColumn;

    public function __construct(
        string $class,
        ?string $property = null,
        ?string $pivotTable = null,
        ?string $pivotColumn = null
    ) {
        $this->referenceClass = $class;
        $this->referenceProperty = $property;
        $this->pivotTable = $pivotTable;
        $this->pivotColumn = $pivotColumn;
    }

    /**
     * @return string
     */
    public function getReferenceClass(): string
    {
        return $this->referenceClass;
    }

    /**
     * @return string|null
     */
    public function getReferenceProperty(): ?string
    {
        return $this->referenceProperty;
    }

    /**
     * @return string|null
     */
    public function getPivotTable(): ?string
    {
        return $this->pivotTable;
    }

    /**
     * @return string|null
     */
    public function getPivotColumn(): ?string
    {
        return $this->pivotColumn;
    }
}