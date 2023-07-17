<?php

namespace ErickComp\BreadcrumbAttributes\Tests;

use ErickComp\BreadcrumbAttributes\Tests\TestClasses\Models\FakeModel;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Route::bind('fake-model', fn(string $name) => new FakeModel($name));
    }
}
