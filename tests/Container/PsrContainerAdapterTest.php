<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection\Container;

    use Psr\Container\ContainerExceptionInterface;
    use Psr\Container\NotFoundExceptionInterface;
    use PsychoB\DependencyInjection\Container\ContainerInterface;
    use PsychoB\DependencyInjection\Container\Exceptions\PsrErrorRetrievalException;
    use PsychoB\DependencyInjection\Container\PsrContainerAdapter;
    use Tests\PsychoB\DependencyInjection\TestCase;

    class PsrContainerAdapterTest extends TestCase
    {
        public function testThatPsrContainerTakesAnyContainerInterface()
        {
            $mock = \Mockery::mock(ContainerInterface::class);

            $adapter = new PsrContainerAdapter($mock);

            // We want to make sure that PsrContainerAdapter won't just accept Container and not the interface
            $this->assertInstanceOf(PsrContainerAdapter::class, $adapter);
        }

        public function testThatExceptionIsThrownForPsrCompatibleException()
        {
            /** @var \RuntimeException|ContainerExceptionInterface $psrCompatException */
            $psrCompatException = \Mockery::mock(ContainerExceptionInterface::class . ',' . \RuntimeException::class);
            $mock = \Mockery::mock(ContainerInterface::class);
            $mock->shouldReceive('get')->andThrow($psrCompatException)->withArgs(['foo']);

            $adapter = new PsrContainerAdapter($mock);

            $this->expectExceptionObject($psrCompatException);
            $adapter->get('foo');
        }

        public function testThatExceptionIsThrownForPsrNotFoundException()
        {
            /** @var \RuntimeException|NotFoundExceptionInterface $psrCompatException */
            $psrCompatException = \Mockery::mock(NotFoundExceptionInterface::class . ',' . \RuntimeException::class);
            $mock = \Mockery::mock(ContainerInterface::class);
            $mock->shouldReceive('get')->andThrow($psrCompatException)->withArgs(['foo']);

            $adapter = new PsrContainerAdapter($mock);

            $this->expectExceptionObject($psrCompatException);
            $adapter->get('foo');
        }

        public function testThatExceptionIsThrownWhenExceptionIsNotCompatible()
        {
            /** @var \RuntimeException $psrCompatException */
            $psrCompatException = \Mockery::mock(\RuntimeException::class);
            $mock = \Mockery::mock(ContainerInterface::class);
            $mock->shouldReceive('get')->andThrow($psrCompatException)->withArgs(['foo']);

            $adapter = new PsrContainerAdapter($mock);

            $this->expectException(PsrErrorRetrievalException::class);
            $this->expectExceptionMessageRegExp('/Failed to retrieve Element: foo/i');
            $adapter->get('foo');
        }

        public function testThatValueIsReturnedWhenGetSucceeds()
        {
            $mock = \Mockery::mock(ContainerInterface::class);
            $mock->shouldReceive('get')->withArgs(['foo'])->andReturn('abc');

            $adapter = new PsrContainerAdapter($mock);

            $this->assertEquals('abc', $adapter->get('foo'));
        }

        public function testThatThrowableIsUntouched()
        {
            /** @var \RuntimeException|NotFoundExceptionInterface $psrCompatException */
            $psrCompatException = \Mockery::mock(\Error::class);
            $mock = \Mockery::mock(ContainerInterface::class);
            $mock->shouldReceive('get')->andThrow($psrCompatException)->withArgs(['foo']);

            $adapter = new PsrContainerAdapter($mock);

            $this->expectException(\Error::class);
            $adapter->get('foo');
        }

        public function testThatHasInterceptExceptionsThrown()
        {
            /** @var \RuntimeException|NotFoundExceptionInterface $psrCompatException */
            $psrCompatException = \Mockery::mock(\Exception::class);
            $mock = \Mockery::mock(ContainerInterface::class);
            $mock->shouldReceive('has')->andThrow($psrCompatException)->withArgs(['foo']);

            $adapter = new PsrContainerAdapter($mock);

            $this->assertFalse($adapter->has('foo'));
        }

        public function testThatHasDoesntInterceptThrowableOrError()
        {
            $psrCompatException = \Mockery::mock(\Error::class);
            $mock = \Mockery::mock(ContainerInterface::class);
            $mock->shouldReceive('has')->andThrow($psrCompatException)->withArgs(['foo']);

            $adapter = new PsrContainerAdapter($mock);

            $this->expectException(\Error::class);
            $adapter->has('foo');
        }

        public function testThatHasPassValueWhenInnerClassReturnWithoutException()
        {
            $psrCompatException = \Mockery::mock(\Error::class);
            $mock = \Mockery::mock(ContainerInterface::class);
            $mock->shouldReceive('has')->withArgs(['false'])->andReturn(false);
            $mock->shouldReceive('has')->withArgs(['true'])->andReturn(true);

            $adapter = new PsrContainerAdapter($mock);

            $this->assertFalse($adapter->has('false'));
            $this->assertTrue($adapter->has('true'));
        }
    }
