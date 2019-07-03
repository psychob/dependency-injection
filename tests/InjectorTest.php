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
    use PsychoB\DependencyInjection\Exceptions\CantInjectParameterException;
    use PsychoB\DependencyInjection\Exceptions\ClassCantBeInjectedException;
    use PsychoB\DependencyInjection\Exceptions\CyclicDependencyException;
    use Tests\PsychoB\DependencyInjection\Mocks\ClassWithConstructorArgument;
    use Tests\PsychoB\DependencyInjection\Mocks\ComplexConstructor;
    use Tests\PsychoB\DependencyInjection\Mocks\CyclicInjects;
    use Tests\PsychoB\DependencyInjection\Mocks\EmptyConstructor;
    use Tests\PsychoB\DependencyInjection\Mocks\InvalidInjects;
    use Tests\PsychoB\DependencyInjection\Mocks\LargeCyclic;
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
            $containerMock = $this->container->make(NotExistingConstructor::class);

            $this->assertInstanceOf(NotExistingConstructor::class, $containerMock);
        }

        public function testExistingEmptyConstructor()
        {
            $containerMock = $this->container->make(EmptyConstructor::class);

            $this->assertInstanceOf(EmptyConstructor::class, $containerMock);
        }

        public function testExistingSimpleConstructor()
        {
            $this->assertFalse($this->container->has(EmptyConstructor::class));
            $containerMock = $this->container->make(ClassWithConstructorArgument::class);

            $this->assertInstanceOf(ClassWithConstructorArgument::class, $containerMock);
            $this->assertTrue($this->container->has(EmptyConstructor::class));
        }

        public function testExistingSimpleConstructorWithNamedArguments()
        {
            $containerMock = $this->container->make(ClassWithConstructorArgument::class,
                                                      ['mock' => new EmptyConstructor()]);

            $this->assertInstanceOf(ClassWithConstructorArgument::class, $containerMock);
        }

        public function testExistingSimpleConstructorWithPositionalArguments()
        {
            $containerMock = $this->container->make(ClassWithConstructorArgument::class, [new EmptyConstructor()]);

            $this->assertInstanceOf(ClassWithConstructorArgument::class, $containerMock);
        }

        public function testExistingSimpleConstructorWithAnyArgument()
        {
            $containerMock = $this->container->make(ClassWithConstructorArgument::class, [Arguments::anyArgument()]);

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

                $builder = NULL;
            }

            $containerMock = $this->container->get(ComplexConstructor::class);

            $this->assertInstanceOf(ComplexConstructor::class, $containerMock);
        }

        public function testExistingComplexConstructorWithDefinition()
        {
            $this->container->build(ComplexConstructor::class)->autoWire()->argument('foo')->literal(10);

            $containerMock = $this->container->make(ComplexConstructor::class, ['bar' => new EmptyConstructor()]);

            $this->assertInstanceOf(ComplexConstructor::class, $containerMock);
        }

        public function testBuiltinInjectsInt()
        {
            $this->expectException(CantInjectParameterException::class);
            $this->expectExceptionMessage('Can not inject parameter: \'a\' into ' .
                                          '\'Tests\PsychoB\DependencyInjection\Mocks\InvalidInjects::__construct\' ' .
                                          'while trying to build: [Tests\PsychoB\DependencyInjection\Mocks\InvalidInjects]');

            $this->container->build(InvalidInjects::class)->autoWire();

            $this->container->make(InvalidInjects::class);
        }

        public function testBuiltinInjectsFloat()
        {
            $this->expectException(CantInjectParameterException::class);
            $this->expectExceptionMessage('Can not inject parameter: \'b\' into ' .
                                          '\'Tests\PsychoB\DependencyInjection\Mocks\InvalidInjects::__construct\' ' .
                                          'while trying to build: [Tests\PsychoB\DependencyInjection\Mocks\InvalidInjects]');

            $this->container->build(InvalidInjects::class)->autoWire();

            $this->container->make(InvalidInjects::class, ['a' => 1]);
        }

        public function testBuiltinInjectsString()
        {
            $this->expectException(CantInjectParameterException::class);
            $this->expectExceptionMessage('Can not inject parameter: \'c\' into ' .
                                          '\'Tests\PsychoB\DependencyInjection\Mocks\InvalidInjects::__construct\' ' .
                                          'while trying to build: [Tests\PsychoB\DependencyInjection\Mocks\InvalidInjects]');

            $this->container->build(InvalidInjects::class)->autoWire();

            $this->container->make(InvalidInjects::class, ['a' => 1, 'b' => 2]);
        }

        public function testBuiltinInjectsSimpleCyclicInject()
        {
            $this->expectException(CyclicDependencyException::class);
            $this->expectExceptionMessage('Detected cyclic dependency while trying to build: ' .
                                          '[Tests\PsychoB\DependencyInjection\Mocks\CyclicInjects]');

            $this->container->build(CyclicInjects::class)->autoWire();

            $this->container->make(CyclicInjects::class);
        }

        public function testBuiltinInjectsComplexCyclicInject()
        {
            $this->expectException(CyclicDependencyException::class);
            $this->expectExceptionMessage('Detected cyclic dependency while trying to build: ' .
                                          '[Tests\PsychoB\DependencyInjection\Mocks\LargeCyclic <- ' .
                                          'Tests\PsychoB\DependencyInjection\Mocks\SmallCyclic]');

            $this->container->build(LargeCyclic::class)->autoWire();

            $this->container->make(LargeCyclic::class);
        }

        public function testInjectNormalMethod()
        {
            $empty = new EmptyConstructor();

            $this->container->injectMethod($empty, 'functionWithMultipleParameters', [
                'foo' => 1,
                'bar' => 'baz',
                'baz' => true,
            ]);

            $this->assertTrue(true);
        }

        public function testInjectMethodNotExists()
        {
            $this->expectException(ClassCantBeInjectedException::class);

            $empty = new EmptyConstructor();

            $this->container->injectMethod($empty, 'functionNotExists', [
                'foo' => 1,
                'bar' => 'baz',
                'baz' => true,
            ]);
        }

        public function testInjectStaticMethod()
        {
            $this->container->injectMethod(EmptyConstructor::class, 'staticFunctionWithMultipleParameters', [
                'foo' => 1,
                'bar' => 'baz',
                'baz' => true,
            ]);

            $this->assertTrue(true);
        }

        public function testInjectStaticMethodNotExists()
        {
            $this->expectException(ClassCantBeInjectedException::class);

            $this->container->injectMethod(EmptyConstructor::class, 'staticFunctionDosentExists', [
                'foo' => 1,
                'bar' => 'baz',
                'baz' => true,
            ]);

        }

        public function testInjectWithFactory()
        {
            $this->container->build(ClassWithConstructorArgument::class)
                            ->argument('mock')
                            ->factory(function (ContainerInterface $cont) {
                                return $cont->make(EmptyConstructor::class);
                            });

            $this->assertInstanceOf(ClassWithConstructorArgument::class,
                                    $this->container->get(ClassWithConstructorArgument::class));
        }

        public function testMakeWithFactory()
        {
            $this->container->build(ClassWithConstructorArgument::class)
                            ->factory(function (ContainerInterface $cont) {
                                return new ClassWithConstructorArgument($cont->make(EmptyConstructor::class));
                            });

            $this->assertInstanceOf(ClassWithConstructorArgument::class,
                                    $this->container->get(ClassWithConstructorArgument::class));
        }
    }
