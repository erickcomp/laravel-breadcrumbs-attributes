<?php
namespace ErickComp\BreadcrumbAttributes\Tests\TestClasses\Models;

use Illuminate\Support\Str;

class FakeModel
{
    public string $name;
    public function __construct(?string $name = null)
    {
        if (!$name) {
            $name = Str::random(10);
        }

        $this->name = $name;
    }
}
