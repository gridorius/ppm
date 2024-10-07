<?php

namespace Ppm\Tests;

interface ITestCase
{
    public function setUp(): void;

    public function tearDown(): void;
}