<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection;

    use PsychoB\DependencyInjection\Arguments;
    use PsychoB\DependencyInjection\Container;
    use Tests\PsychoB\DependencyInjection\Mocks\ClassWithConstructorArgument;
    use Tests\PsychoB\DependencyInjection\Mocks\Container2Mock;
    use Tests\PsychoB\DependencyInjection\Mocks\Container3Mock;
    use Tests\PsychoB\DependencyInjection\Mocks\ContainerMock;
    use Tests\PsychoB\DependencyInjection\Mocks\EmptyConstructor;

    class AutowireTest extends TestCase
    {
        public function testAutoDetect()
        {
            $container = new Container();

            $container->autowirePath(__DIR__ . DIRECTORY_SEPARATOR . 'Mocks');

            $this->assertTrue($container->has(ContainerMock::class));
            $this->assertTrue($container->has(Container2Mock::class));
            $this->assertTrue($container->has(Container3Mock::class));
        }

        public function testAutowiredInject()
        {
            $container = new Container();

            $container->autowirePath(__DIR__ . DIRECTORY_SEPARATOR . 'Mocks');
            $mock = $container->get(ClassWithConstructorArgument::class);

            $this->assertInstanceOf(ClassWithConstructorArgument::class, $mock);
        }

        public function testAutowiredInjectNamed()
        {
            $container = new Container();

            $container->autowirePath(__DIR__ . DIRECTORY_SEPARATOR . 'Mocks');
            $mock = $container->make(ClassWithConstructorArgument::class, [Arguments::anyArgument()]);

            $this->assertInstanceOf(ClassWithConstructorArgument::class, $mock);
        }

        public function testAutoWireSrc()
        {
            $container = new Container();

            // this will throw exception on error
            $container->autowirePath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src');

            $this->assertTrue(true);
        }

    }
