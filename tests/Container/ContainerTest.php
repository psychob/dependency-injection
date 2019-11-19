<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection\Container;

    use Mockery;
    use Psr\Container\ContainerInterface as PsrContainerInterface;
    use PsychoB\DependencyInjection\Container\ContainerInterface;
    use PsychoB\DependencyInjection\Container\Exceptions\ClassAlreadyDefinedException;
    use PsychoB\DependencyInjection\Container\Exceptions\ClassNotFoundException;
    use Tests\PsychoB\DependencyInjection\Mocks\Container\ContainerMock;
    use Tests\PsychoB\DependencyInjection\TestCase;

    class ContainerTest extends TestCase
    {
        public function testHasFunctionWorks()
        {
            $mock = Mockery::mock(ContainerMock::class, [
                ['foo' => 'bar'],
            ]);
            $mock->makePartial();

            $this->assertTrue($mock->has('foo'));
            $this->assertFalse($mock->has('bar'));
        }

        public function testGetReturnCorrectElement()
        {
            $mock = Mockery::mock(ContainerMock::class, [
                ['foo' => 'bar'],
            ]);
            $mock->makePartial();

            $this->assertSame('bar', $mock->get('foo'));
        }

        public function testGetThrowsOnUnknownElement()
        {
            $mock = Mockery::mock(ContainerMock::class, [
                ['foo' => 'bar'],
            ]);
            $mock->makePartial();

            $this->expectException(ClassNotFoundException::class);
            $this->expectExceptionMessageRegExp('/element .*? not found/i');
            $mock->get('Foo');
        }

        public function testGetThrowsOnUnknownElementWithCorrectException()
        {
            $mock = Mockery::mock(ContainerMock::class, [
                ['foo' => 'bar'],
            ]);
            $mock->makePartial();

            $wasThrown = false;

            try {
                $mock->get('Foo');
            } catch (ClassNotFoundException $e) {
                $wasThrown = true;
                $this->assertSame('Foo', $e->getClass());
                $this->assertEquals(['foo'], $e->getRest());
            }

            $this->assertTrue($wasThrown);
        }

        public function provideAddWithEmptyContainer(): array
        {
            return [
                [ContainerInterface::ADD_IGNORE],
                [ContainerInterface::ADD_EXCEPTION],
                [ContainerInterface::ADD_OVERWRITE],
            ];
        }

        /** @dataProvider provideAddWithEmptyContainer */
        public function testAddWithEmptyContainer(int $type)
        {
            $mock = new ContainerMock();

            $mock->add('foo', 'bar', $type);

            $this->assertSame('bar', $mock->get('foo'));
        }

        public function testAddWithDuplicateAndOverwrite()
        {
            $mock = new ContainerMock();

            $mock->add('foo', 'bar', ContainerInterface::ADD_OVERWRITE);
            $this->assertSame('bar', $mock->get('foo'));

            $mock->add('foo', 'new-bar', ContainerInterface::ADD_OVERWRITE);
            $this->assertNotSame('bar', $mock->get('foo'));
            $this->assertSame('new-bar', $mock->get('foo'));
        }

        public function testAddWithDuplicateAndIgnore()
        {
            $mock = new ContainerMock();

            $mock->add('foo', 'bar', ContainerInterface::ADD_IGNORE);
            $this->assertSame('bar', $mock->get('foo'));

            $mock->add('foo', 'new-bar', ContainerInterface::ADD_IGNORE);
            $this->assertSame('bar', $mock->get('foo'));
            $this->assertNotSame('new-bar', $mock->get('foo'));
        }

        public function testAddWithDuplicateAndException()
        {
            $mock = new ContainerMock();

            $mock->add('foo', 'bar', ContainerInterface::ADD_EXCEPTION);
            $this->assertSame('bar', $mock->get('foo'));

            $this->expectException(ClassAlreadyDefinedException::class);
            $this->expectExceptionMessageRegExp('/class already has a value/i');
            $mock->add('foo', 'new-bar', ContainerInterface::ADD_EXCEPTION);
        }

        public function testAddWithDuplicateAndCheckException()
        {
            $mock = new ContainerMock();

            $mock->add('foo', 'bar', ContainerInterface::ADD_EXCEPTION);
            $this->assertSame('bar', $mock->get('foo'));

            $wasThrown = false;

            try {
                $mock->add('foo', 'new-bar', ContainerInterface::ADD_EXCEPTION);
            } catch (ClassAlreadyDefinedException $e) {
                $this->assertSame('foo', $e->getClass());
                $this->assertSame('bar', $e->getOldValue());
                $this->assertSame('new-bar', $e->getNewValue());
                $wasThrown = true;
            }

            $this->assertTrue($wasThrown);
        }

        public function testPsrReturnCorrectInterface()
        {
            $mock = new ContainerMock();

            $this->assertInstanceOf(PsrContainerInterface::class, $mock->psr());
        }

    }
