<?php
declare(strict_types = 1);

namespace DASPRiD\Formidable\Mapping;

use Assert\Assertion;
use DASPRiD\Formidable\Data;
use DASPRiD\Formidable\FormError\FormErrorSequence;
use ReflectionClass;

final class ObjectMapping implements MappingInterface
{
    use MappingTrait;

    /**
     * @var array
     */
    private $mappings;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $key;

    /**
     * @param MappingInterface[] $mappings
     */
    public function __construct(array $mappings, $className, string $key = '')
    {
        foreach ($mappings as $mappingKey => $mapping) {
            Assertion::string($mappingKey);
            Assertion::isInstanceOf($mapping, MappingInterface::class);

            $this->mappings[$mappingKey] = $mapping->withPrefixAndRelativeKey($key, $mappingKey);
        }

        Assertion::classExists($className);

        $this->className = $className;
        $this->key = $key;
    }

    public function bind(Data $data) : BindResult
    {
        $reflectionClass = new ReflectionClass($this->className);
        $reflectionMethod = $reflectionClass->getMethod('__construct');

        $arguments = [];

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $arguments[$reflectionParameter->getName()] = null;
        }

        $formErrors = new FormErrorSequence([]);

        foreach ($this->mappings as $key => $mapping) {
            $bindResult = $mapping->bind($data);

            if (!$bindResult->isSuccess()) {
                $formErrors = $formErrors->merge($bindResult->getFormErrors());
                continue;
            }

            Assertion::keyExists($arguments, $key);
            $arguments[$key] = $mapping->bind($data);
        }

        if (0 < count($formErrors)) {
            return BindResult::fromFormErrors($formErrors);
        }

        return $this->applyConstraints($reflectionClass->newInstance($arguments));
    }

    public function unbind($value) : Data
    {
        Assertion::isInstanceOf($value, $this->className);
        $data = Data::fromFlatArray([]);

        foreach ($this->mappings as $mapping) {
            $data = $data->merge($mapping->unbind($value));
        }

        return $data;
    }

    public function withPrefixAndRelativeKey(string $prefix, string $relativeKey) : MappingInterface
    {
        return new self(
            $this->mappings,
            $this->className,
            $this->createKeyFromPrefixAndRelativeKey($prefix, $relativeKey)
        );
    }
}
