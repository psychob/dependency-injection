<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection\Injector;

    use PsychoB\DependencyInjection\Container\ContainerInterface;
    use PsychoB\DependencyInjection\Injector\Injector;
    use Tests\PsychoB\DependencyInjection\Mocks\Container\ContainerMock;
    use Tests\PsychoB\DependencyInjection\TestCase;

    class InjectorTestCase extends TestCase
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

        protected function assertContainerHas(string $class): void
        {
            $this->assertTrue($this->container->has($class));
        }
    }
