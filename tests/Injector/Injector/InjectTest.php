<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection\Injector\Injector;

    use PsychoB\DependencyInjection\Injector\Exceptions\InjectionException;
    use PsychoB\DependencyInjection\Injector\Exceptions\MetadataException;
    use Tests\PsychoB\DependencyInjection\Injector\InjectorTestCase;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\DefinedConstructorMock;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\NoConstructorMock;

    class InjectTest extends InjectorTestCase
    {
        private function assertContainerValue(string $class, $value): void
        {
            $this->assertTrue($this->container->has($class));
            $this->assertSame($value, $this->container->get($class));
        }

        public function testInjectMakesNewClassWhenCalledWithConstruct_String(): void
        {
            $same = $this->injector->inject(NoConstructorMock::class . '::__construct');

            $this->assertContainerHas(NoConstructorMock::class);
            $this->assertContainerValue(NoConstructorMock::class, $same);
        }

        public function testInjectMakesNewClassWhenCalledWithConstruct_Array(): void
        {
            $same = $this->injector->inject([DefinedConstructorMock::class, '__construct']);

            $this->assertContainerHas(DefinedConstructorMock::class);
            $this->assertContainerValue(DefinedConstructorMock::class, $same);
        }

        public function testInjectThrowsWhenEncounteringInvalidClass_String(): void
        {
            $this->expectException(MetadataException::class);
            $this->expectExceptionMessage("Can't retrieve class metadata");

            $this->injector->inject(sprintf('%s::%s', '\UnknownClass\UnknownClass', 'inject'));
        }

        public function testInjectThrowsWhenEncounteringInvalidClass_Array(): void
        {
            $this->expectException(MetadataException::class);
            $this->expectExceptionMessage("Can't retrieve class metadata");

            $this->injector->inject(['\UnknownClass\UnknownClass', 'inject']);
        }

        public function testInjectThrowsWhenEncounteringInvalidMethod_String(): void
        {
            $this->expectException(MetadataException::class);
            $this->expectExceptionMessage("Can't retrieve method metadata");

            $this->injector->inject(sprintf('%s::%s', self::class, '__inject'));
        }

        public function testInjectThrowsWhenEncounteringInvalidMethod_Array(): void
        {
            $this->expectException(MetadataException::class);
            $this->expectExceptionMessage("Can't retrieve method metadata");

            $this->injector->inject([self::class, '__inject']);
        }

        public function testInjectThrowsWhenEncounteringNonStaticMethodFromStaticContext_String(): void
        {
            $this->expectException(InjectionException::class);
            $this->expectExceptionMessage("Can't inject to non static method from static context");

            $this->injector->inject(sprintf('%s::%s', self::class, 'testInjectThrowsWhenEncounteringNonStaticMethodFromStaticContext_String'));
        }

        public function testInjectThrowsWhenEncounteringNonStaticMethodFromStaticContext_Array(): void
        {
            $this->expectException(InjectionException::class);
            $this->expectExceptionMessage("Can't inject to non static method from static context");

            $this->injector->inject([self::class, 'testInjectThrowsWhenEncounteringNonStaticMethodFromStaticContext_Array']);
        }
    }
