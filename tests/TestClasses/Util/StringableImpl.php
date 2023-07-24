<?php
namespace ErickComp\BreadcrumbAttributes\Tests\TestClasses\Util;

class StringableImpl
{
    public function __construct(
        private string $str
    ) {
    }

    public function __toString()
    {
        return $this->str;
    }

}
