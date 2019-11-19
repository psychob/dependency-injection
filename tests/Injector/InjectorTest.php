<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection\Injector;

    use PsychoB\DependencyInjection\Injector\Injector;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\ConstructorWithDefaultParameters;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\DefinedConstructorMock;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\NoConstructorMock;
    use Tests\PsychoB\DependencyInjection\TestCase;

    class InjectorTest extends TestCase
    {
        public function provideSimpleConstructorMocks(): array
        {
            return [
                [NoConstructorMock::class],
                [DefinedConstructorMock::class],
                [ConstructorWithDefaultParameters::class],
            ];
        }

        /** @dataProvider provideSimpleConstructorMocks */
        public function testSimpleConstructorInjectionStringSyntax(string $class)
        {
            $injector = new Injector();

            $this->assertInstanceOf($class, $injector->inject(sprintf('%s::__construct', $class), []));
        }

        /** @dataProvider provideSimpleConstructorMocks */
        public function testSimpleConstructorInjectionArraySyntax(string $class)
        {
            $injector = new Injector();

            $this->assertInstanceOf($class, $injector->inject([$class, '__construct'], []));
        }
    }
