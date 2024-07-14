<?php

namespace Tests;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use Terminal\ShellStyleParser;

class TestRunner
{
    /**
     * @var ITestCase[]
     */
    private array $testCases;

    public function __construct()
    {
        $this->testCases = [];
        foreach (get_declared_classes() as $class)
            if (is_subclass_of($class, ITestCase::class)) {
                $reflectionClass = new ReflectionClass($class);
                if (!$reflectionClass->isAbstract() && !$reflectionClass->isInterface())
                    $this->testCases[] = new $class();
            }
    }

    public function run(): void
    {
        foreach ($this->testCases as $testObject) {
            $reflectionClass = new ReflectionClass($testObject);
            echo $reflectionClass->getName() . ':' . PHP_EOL;
            $testObject->setUp();
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
            /**
             * @var ReflectionMethod[] $testMethods
             */
            $testMethods = array_filter($methods, function (ReflectionMethod $method) {
                return !empty($method->getAttributes(Test::class));
            });

            foreach ($testMethods as $testMethod) {
                $hasDataset = false;
                foreach ($testMethod->getAttributes(UseDataset::class) as $attribute) {
                    $hasDataset = true;
                    $attributeInstance = $attribute->newInstance();
                    $dataList = call_user_func($attributeInstance->getMethod());
                    echo "==> " . $testMethod->getName() . ':' . PHP_EOL;
                    foreach ($dataList as $key => $data) {
                        if (is_int($key))
                            $key = 'case ' . $key;

                        if ($data instanceof TestCaseData) {
                            if ($data->hasName())
                                $key = $data->getName();
                            $data = $data->getData();
                        }

                        $this->runTest($testMethod, $testObject, $data, "====> " . $key);
                    }
                }

                if (!$hasDataset)
                    $this->runTest($testMethod, $testObject, [], "==> " . $testMethod->getName());
            }

            $testObject->tearDown();
        }
        echo PHP_EOL . ShellStyleParser::style(Assert::getResultMessage());
        if (Assert::isFailed())
            exit(1);
    }

    private function runTest(ReflectionMethod $method, object $testObject, array $args = [], string $showLine = ''): void
    {
        $source = get_class($testObject) . $method->getName() . $showLine;
        Assert::setSource($source);
        echo "{$showLine} - process\r";
        try {
            $method->invokeArgs($testObject, $args);
        } catch (Exception $exception) {
            Assert::addFailure(sprintf("Unexpected: %s(%s, %s)\n%s", get_class($exception),
                $exception->getMessage(),
                $exception->getCode(),
                \Assembly\Exception::prepareTraceAsString($exception->getTrace())
            ), $exception->getLine());
        } finally {
            $this->log($source, $showLine);
        }
    }

    private function log(string $source, string $showLine): void
    {
        $failedResults = Assert::getSourceFailResults($source);

        $resultString = count($failedResults) > 0 ? "<s style='red'>Failure</s>" : "<s style='green'>Success</s>";
        echo ShellStyleParser::style("{$showLine} - {$resultString}\n");
        foreach ($failedResults as $result)
            echo ShellStyleParser::style("<s style='red'>{$result['message']}</s>\non line: {$result['line']}") . PHP_EOL;
    }
}