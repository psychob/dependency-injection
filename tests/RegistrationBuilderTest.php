<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection;

    use PsychoB\DependencyInjection\ContainerInterface;
    use PsychoB\DependencyInjection\Registration\ArgumentBuilder;
    use PsychoB\DependencyInjection\Registration\RegistrationBuilder;
    use PsychoB\DependencyInjection\Registration\RegistrationEntry;

    class RegistrationBuilderTest extends TestCase
    {
        public function testCreationAndDestructionOfBuilder()
        {
            $mock = \Mockery::mock(ContainerInterface::class);
            $mock->shouldReceive('build')->andReturnUsing(function ($class) use ($mock) {
                return new RegistrationBuilder($mock, $class);
            });
            $mock->shouldReceive('register');

            /** @var $mock ContainerInterface */
            $mock->build('Foo');

            $mock->shouldHaveReceived('register')->once();
        }

        public function testSettingOptions()
        {
            $mock = \Mockery::mock(ContainerInterface::class);
            $mock->shouldReceive('build')->andReturnUsing(function ($class) use ($mock) {
                return new RegistrationBuilder($mock, $class);
            });
            $mock->shouldReceive('register')->withArgs(function (RegistrationBuilder $builder) {
                $serialized = $builder->serialize();

                $this->assertSame('Foo', $serialized->getName());
                $this->assertTrue($serialized->isAutoWire());
                $this->assertFalse($serialized->isSingleton());

                return true;
            });

            /** @var $mock ContainerInterface */
            $mock->build('Foo')->autoWire()->singleton(false);

            $mock->shouldHaveReceived('register')->once();
        }

        public function testSettingArguments()
        {
            $mock = \Mockery::mock(ContainerInterface::class);
            $mock->shouldReceive('build')->andReturnUsing(function ($class) use ($mock) {
                return new RegistrationBuilder($mock, $class);
            });
            $mock->shouldReceive('register')->withArgs(function (RegistrationBuilder $builder) {
                $serialized = $builder->serialize();

                $this->assertSame('Foo', $serialized->getName());
                $this->assertFalse($serialized->isAutoWire());

                $this->assertCount(5, $serialized->getArguments(), 'Too many arguments defined');

                $this->assertEquals(ArgumentBuilder::ARGUMENT_TYPE_NAMED, $serialized->getArguments()[0]['type']);
                $this->assertEquals('foo', $serialized->getArguments()[0]['id']);
                $this->assertEquals(ArgumentBuilder::BIND_TYPE_CLASS, $serialized->getArguments()[0]['bind_type']);
                $this->assertEquals('Abc', $serialized->getArguments()[0]['bind']);

                $this->assertEquals(ArgumentBuilder::ARGUMENT_TYPE_POSITIONAL, $serialized->getArguments()[1]['type']);
                $this->assertEquals(1, $serialized->getArguments()[1]['id']);
                $this->assertEquals(ArgumentBuilder::BIND_TYPE_CLASS, $serialized->getArguments()[1]['bind_type']);
                $this->assertEquals('Def', $serialized->getArguments()[1]['bind']);

                $this->assertEquals(ArgumentBuilder::ARGUMENT_TYPE_POSITIONAL, $serialized->getArguments()[2]['type']);
                $this->assertEquals(2, $serialized->getArguments()[2]['id']);
                $this->assertEquals(ArgumentBuilder::BIND_TYPE_CLASS, $serialized->getArguments()[2]['bind_type']);
                $this->assertEquals('Gha', $serialized->getArguments()[2]['bind']);

                $this->assertEquals(ArgumentBuilder::ARGUMENT_TYPE_POSITIONAL, $serialized->getArguments()[3]['type']);
                $this->assertEquals(3, $serialized->getArguments()[3]['id']);
                $this->assertEquals(ArgumentBuilder::BIND_TYPE_LITERAL, $serialized->getArguments()[3]['bind_type']);
                $this->assertEquals('foo', $serialized->getArguments()[3]['bind']);

                $this->assertEquals(ArgumentBuilder::ARGUMENT_TYPE_POSITIONAL, $serialized->getArguments()[4]['type']);
                $this->assertEquals(4, $serialized->getArguments()[4]['id']);
                $this->assertEquals(ArgumentBuilder::BIND_TYPE_FUNCTION, $serialized->getArguments()[4]['bind_type']);
                $this->assertIsCallable($serialized->getArguments()[4]['bind']);

                return true;
            });

            /** @var $mock ContainerInterface */
            $builder = $mock->build('Foo');
            $builder->argument('foo')->bind('Abc');
            $builder->positional(1)->bind('Def');
            $builder->positional()->bind('Gha');
            $builder->positional()->literal('foo');
            $builder->positional()->bind(function () { });
            $builder = NULL;

            $mock->shouldHaveReceived('register')->once();
        }
    }
