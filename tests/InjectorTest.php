<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection;

    use PsychoB\DependencyInjection\Arguments;
    use PsychoB\DependencyInjection\Container;
    use PsychoB\DependencyInjection\ContainerInterface;
    use Tests\PsychoB\DependencyInjection\Mocks\ClassWithConstructorArgument;
    use Tests\PsychoB\DependencyInjection\Mocks\ComplexConstructor;
    use Tests\PsychoB\DependencyInjection\Mocks\EmptyConstructor;
    use Tests\PsychoB\DependencyInjection\Mocks\NotExistingConstructor;
    use Tests\PsychoB\DependencyInjection\TestCase;

    class InjectorTest extends TestCase
    {
        /**
         * @var Container
         */
        private $container;

        protected function setUp(): void
        {
            parent::setUp();

            $this->container = new Container();
        }

        public function testNotExistingConstructor()
        {
            $containerMock = $this->container->inject(NotExistingConstructor::class);

            $this->assertInstanceOf(NotExistingConstructor::class, $containerMock);
        }

        public function testExistingEmptyConstructor()
        {
            $containerMock = $this->container->inject(EmptyConstructor::class);

            $this->assertInstanceOf(EmptyConstructor::class, $containerMock);
        }

        public function testExistingSimpleConstructor()
        {
            $this->assertFalse($this->container->has(EmptyConstructor::class));
            $containerMock = $this->container->inject(ClassWithConstructorArgument::class);

            $this->assertInstanceOf(ClassWithConstructorArgument::class, $containerMock);
            $this->assertTrue($this->container->has(EmptyConstructor::class));
        }

        public function testExistingSimpleConstructorWithNamedArguments()
        {
            $containerMock = $this->container->inject(ClassWithConstructorArgument::class, ['mock' => new EmptyConstructor()]);

            $this->assertInstanceOf(ClassWithConstructorArgument::class, $containerMock);
        }

        public function testExistingSimpleConstructorWithPositionalArguments()
        {
            $containerMock = $this->container->inject(ClassWithConstructorArgument::class, [new EmptyConstructor()]);

            $this->assertInstanceOf(ClassWithConstructorArgument::class, $containerMock);
        }

        public function testExistingSimpleConstructorWithAnyArgument()
        {
            $containerMock = $this->container->inject(ClassWithConstructorArgument::class, [Arguments::anyArgument()]);

            $this->assertInstanceOf(ClassWithConstructorArgument::class, $containerMock);
        }

        public function testExistingSimpleConstructorWithDefinition()
        {
            {
                $builder = $this->container->build(ComplexConstructor::class);

                $builder->positional()->literal(10);
                $builder->positional()->bind(EmptyConstructor::class);
                $builder->positional()->bind(function (ContainerInterface $container) {
                    return $container->make(ClassWithConstructorArgument::class);
                });

                $builder = null;
            }

            $containerMock = $this->container->get(ComplexConstructor::class);

            $this->assertInstanceOf(ComplexConstructor::class, $containerMock);
        }

        public function testExistingComplexConstructorWithDefinition()
        {
            $this->container->build(ComplexConstructor::class)->autoWire()->argument('foo')->literal(10);

            $containerMock = $this->container->inject(ComplexConstructor::class, ['bar' => new EmptyConstructor()]);

            $this->assertInstanceOf(ComplexConstructor::class, $containerMock);
        }
    }
