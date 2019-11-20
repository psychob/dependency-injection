<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection\Injector\Injector;

    use PsychoB\DependencyInjection\Container\ContainerInterface;
    use PsychoB\DependencyInjection\Injector\Exceptions\ClassCreationException;
    use PsychoB\DependencyInjection\Injector\Exceptions\CyclicDependencyDetectedException;
    use PsychoB\DependencyInjection\Injector\Injector;
    use Tests\PsychoB\DependencyInjection\Mocks\Container\ContainerMock;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\ConstructorWithDefaultParameters;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\CyclicConstructorMock;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\DefinedConstructorMock;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\NoConstructorMock;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\PrivateConstructorMock;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\ProtectedConstructorMock;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\SimpleConstructorRequiringClassMock;
    use Tests\PsychoB\DependencyInjection\TestCase;

    class MakeTest extends TestCase
    {
        /** @var ContainerInterface */
        protected $container;

        /** @var Injector */
        protected $injector;

        protected function setUp(): void
        {
            parent::setUp();

            $this->container = new ContainerMock();
            $this->injector = new Injector($this->container);
        }

        protected function tearDown(): void
        {
            $this->injector = NULL;
            $this->container = NULL;

            parent::tearDown();
        }

        private function assertContainerHas(string $class): void
        {
            $this->assertTrue($this->container->has($class));
        }

        public function testMakeCanCreateClassWithNoConstructor()
        {
            $this->assertInstanceOf(NoConstructorMock::class, $this->injector->make(NoConstructorMock::class));

            $this->assertContainerHas(NoConstructorMock::class);
        }

        public function testMakeCanCreateClassWithDefinedConstructor()
        {
            $this->assertInstanceOf(DefinedConstructorMock::class,
                $this->injector->make(DefinedConstructorMock::class));

            $this->assertContainerHas(DefinedConstructorMock::class);
        }

        public function testMakeCanReportClassWithDisabledConstructor_Protected()
        {
            $this->expectException(ClassCreationException::class);
            $this->expectExceptionMessage('Class constructor is not public');
            $this->injector->make(ProtectedConstructorMock::class);
        }

        public function testMakeCanReportClassWithDisabledConstructor_Private()
        {
            $this->expectException(ClassCreationException::class);
            $this->expectExceptionMessage('Class constructor is not public');
            $this->injector->make(PrivateConstructorMock::class);
        }

        public function testMakeCanInjectDefaultConstructorParameters()
        {
            $this->assertInstanceOf(ConstructorWithDefaultParameters::class,
                $this->injector->make(ConstructorWithDefaultParameters::class));

            $this->assertContainerHas(ConstructorWithDefaultParameters::class);
        }

        public function testMakeCanBuildDependantTypesForConstructor()
        {
            $this->assertInstanceOf(SimpleConstructorRequiringClassMock::class,
                $this->injector->make(SimpleConstructorRequiringClassMock::class));

            $this->assertContainerHas(SimpleConstructorRequiringClassMock::class);
        }

        public function testMakeCanDetectCyclicDependencies()
        {
            $this->expectException(CyclicDependencyDetectedException::class);
            $this->expectExceptionMessage('Cyclic dependency detected');

            $this->injector->make(CyclicConstructorMock::class);
        }
    }
