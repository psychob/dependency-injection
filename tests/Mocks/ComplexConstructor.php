<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection\Mocks;

    class ComplexConstructor
    {
        public function __construct(int $foo, EmptyConstructor $bar, ClassWithConstructorArgument $baz)
        {
        }
    }
