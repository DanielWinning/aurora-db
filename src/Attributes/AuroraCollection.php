<?php

namespace Luma\AuroraDatabase\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AuroraCollection
{
    private string $referenceClass;
    private string $referenceProperty;

    public function __construct(string $class, string $property)
    {
        $this->referenceClass = $class;
        $this->referenceProperty = $property;
    }

    /**
     * @return string
     */
    public function getReferenceClass(): string
    {
        return $this->referenceClass;
    }

    /**
     * @return string
     */
    public function getReferenceProperty(): string
    {
        return $this->referenceProperty;
    }
}