<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection;

    use PsychoB\DependencyInjection\Container;
    use PsychoB\DependencyInjection\Exceptions\ClassNotFoundException;
    use Tests\PsychoB\DependencyInjection\Mocks\Container2Mock;
    use Tests\PsychoB\DependencyInjection\Mocks\Container3Mock;
    use Tests\PsychoB\DependencyInjection\Mocks\ContainerMock;

    class ContainerTest extends TestCase
    {
        public function testContainerHaveItselfDefined()
        {
            $container = new Container();

            $this->assertTrue($container->has(Container::class));
            $this->assertEquals($container, $container->get(Container::class));
        }

        public function testContainerDosentHaveAnyDefinition()
        {
            $container = new Container();

            $this->assertFalse($container->has(ContainerMock::class));

            $this->expectException(ClassNotFoundException::class);
            $this->expectExceptionMessage("Class 'Tests\PsychoB\DependencyInjection\Mocks\ContainerMock' was not registered, " .
                                          "did you mean: PsychoB\DependencyInjection\Container");
            $container->get(ContainerMock::class);
        }

        public function testContainerDosentHaveCorrectDefinition()
        {
            $container = new Container();

            $this->assertFalse($container->has(ContainerMock::class));
            $this->assertFalse($container->has(Container2Mock::class));
            $this->assertFalse($container->has(Container3Mock::class));

            $container->add(Container2Mock::class, new Container2Mock());
            $container->add(Container3Mock::class, new Container3Mock());

            $this->expectException(ClassNotFoundException::class);
            $this->expectExceptionMessage("Class 'Tests\PsychoB\DependencyInjection\Mocks\ContainerMock' was not registered, " .
                                          "did you mean: Tests\PsychoB\DependencyInjection\Mocks\Container2Mock or " .
                                          "Tests\PsychoB\DependencyInjection\Mocks\Container3Mock or PsychoB\DependencyInjection\Container");
            $container->get(ContainerMock::class);
        }
    }
