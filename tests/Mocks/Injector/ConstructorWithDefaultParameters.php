<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection\Mocks\Injector;

    class ConstructorWithDefaultParameters
    {
        public $foo;
        public $bar;

        public function __construct(int $foo = 12, $bar = "no type")
        {
            $this->foo = $foo;
            $this->bar = $bar;
        }
    }
