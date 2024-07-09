<?php

namespace Tests;

use Assembly\Resources;
use Closure;
use ReflectionClass;
use ReflectionMethod;

/**
 * @template Abstract
 */
class MockBuilder
{
    const METHOD_BODY = "\$this->tryExecuteSetupMethod('%s', func_get_args());";
    private string $abstract;
    private bool $isInterface;

    private array $setupMethods;

    /**
     * @param class-string<Abstract> $abstract
     */
    public function __construct(string $abstract)
    {
        $this->abstract = $abstract;
        $this->isInterface = interface_exists($abstract);
        $this->setupMethods = [];
    }

    /**
     * @param class-string<Abstract> $abstract
     */
    public static function from(string $abstract): MockBuilder
    {
        return new static($abstract);
    }

    public function setup(string $method, Closure $handle): MockBuilder
    {
        $this->setupMethods[$method] = $handle;
        return $this;
    }

    /**
     * @return Abstract
     * @throws \ReflectionException
     */
    public function build(): object
    {
        $className = $this->makeClassName();
        if (!class_exists($className)) {
            eval($this->generateEntityString());
            $object = new $className($this->setupMethods);
            assert($object instanceof $className);
            assert($object instanceof $this->abstract);
            return $object;
        }
        return new $className($this->setupMethods);
    }

    public function generateEntityString(): string
    {
        $reflectionClass = new ReflectionClass($this->abstract);
        $publicMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $bodyString = '';
        foreach ($publicMethods as $method)
            $bodyString .= $this->buildMethod($method);

        return sprintf(
            Resources::get('templates/mock_class')->getContent(),
            $this->makeClassName(),
            ($this->isInterface ? ' implements ' : ' extends '),
            $this->abstract,
            $bodyString
        );
    }

    private function buildMethod(ReflectionMethod $method): string
    {
        $arguments = [];
        foreach ($method->getParameters() as $parameter) {
            $arguments[] = preg_replace("/^.+?\[.+?>\s(.+?)\s\]/", '$1', (string)$parameter);
        }
        $methodName = $method->getName();
        $returnType = $method->getReturnType();
        $returnTypeName = $method->hasReturnType() ? $returnType->getName() : '';
        $return = is_null($returnType) ? '' : ': ' . $returnTypeName;
        return sprintf(
            "\tpublic function %s(%s)%s{\n\t\t%s\n\t}\n\n",
            $methodName,
            implode(', ', $arguments),
            $return,
            sprintf($returnTypeName == 'void' ? static::METHOD_BODY : ('return ' . static::METHOD_BODY), $methodName)
        );
    }

    private function makeClassName(): string
    {
        return 'Mock_' . preg_replace("/\\\\/", '_', $this->abstract);
    }
}