<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection\Mocks;

    class EmptyConstructor
    {
        public function __construct()
        {
        }

        public function functionWithMultipleParameters(ClassWithConstructorArgument $arg, int $foo, string $bar, $baz,
                                                       NotExistingConstructor $const)
        {
        }

        public static function staticFunctionWithMultipleParameters(ClassWithConstructorArgument $arg, int $foo,
                                                                    string $bar, $baz, NotExistingConstructor $const)
        {
        }
    }
