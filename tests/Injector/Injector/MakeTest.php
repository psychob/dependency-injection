<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection\Injector\Injector;

    use PsychoB\DependencyInjection\Injector\Exceptions\ClassCreationException;
    use PsychoB\DependencyInjection\Injector\Exceptions\CyclicDependencyDetectedException;
    use PsychoB\DependencyInjection\Injector\Exceptions\MetadataException;
    use Tests\PsychoB\DependencyInjection\Injector\InjectorTestCase;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\ConstructorWithDefaultParameters;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\CyclicConstructorMock;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\DefinedConstructorMock;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\NoConstructorMock;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\PrivateConstructorMock;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\ProtectedConstructorMock;
    use Tests\PsychoB\DependencyInjection\Mocks\Injector\SimpleConstructorRequiringClassMock;

    class MakeTest extends InjectorTestCase
    {
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

        public function testMakeCanReportClassWithDisabledConstructor_Protected_CheckException()
        {
            /** @noinspection PhpUndefinedClassInspection PhpFullyQualifiedNameUsageInspection PhpUndefinedNamespaceInspection */
            $wasThrown = false;

            try {
                $this->injector->make(ProtectedConstructorMock::class);
            } catch (ClassCreationException $e) {
                $wasThrown = true;

                $this->assertSame("Class constructor is not public", $e->getMessage());
                $this->assertSame(ProtectedConstructorMock::class, $e->getClassName());
            }

            $this->assertTrue($wasThrown);
        }

        public function testMakeCanReportClassWithDisabledConstructor_Private()
        {
            $this->expectException(ClassCreationException::class);
            $this->expectExceptionMessage('Class constructor is not public');

            $this->injector->make(PrivateConstructorMock::class);
        }

        public function testMakeCanReportClassWithDisabledConstructor_Private_CheckException()
        {
            /** @noinspection PhpUndefinedClassInspection PhpFullyQualifiedNameUsageInspection PhpUndefinedNamespaceInspection */
            $wasThrown = false;

            try {
                $this->injector->make(PrivateConstructorMock::class);
            } catch (ClassCreationException $e) {
                $wasThrown = true;

                $this->assertSame("Class constructor is not public", $e->getMessage());
                $this->assertSame(PrivateConstructorMock::class, $e->getClassName());
            }

            $this->assertTrue($wasThrown);
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

        public function testMakeCanDetectCyclicDependencies_CheckException()
        {
            /** @noinspection PhpUndefinedClassInspection PhpFullyQualifiedNameUsageInspection PhpUndefinedNamespaceInspection */
            $wasThrown = false;

            try {
                $this->injector->make(CyclicConstructorMock::class);
            } catch (CyclicDependencyDetectedException $e) {
                $wasThrown = true;

                $this->assertSame("Cyclic dependency detected", $e->getMessage());
                $this->assertSame([CyclicConstructorMock::class], $e->getCycle());
            }

            $this->assertTrue($wasThrown);
        }

        public function testMakeWillReuseAlreadyCreatedClass()
        {
            $first = $this->injector->make(NoConstructorMock::class);

            $this->assertSame($first, $this->injector->make(NoConstructorMock::class));
        }

        public function testMakeWillThrowWhenClassIsUnknown()
        {
            $this->expectException(MetadataException::class);
            $this->expectExceptionMessage('Can\'t retrieve class metadata');

            /** @noinspection PhpUndefinedClassInspection PhpFullyQualifiedNameUsageInspection PhpUndefinedNamespaceInspection */
            $this->injector->make(\UnknownNamespace\UnknownClass::class);
        }

        public function testMakeWillThrowWhenClassIsUnknown_CheckException()
        {
            /** @noinspection PhpUndefinedClassInspection PhpFullyQualifiedNameUsageInspection PhpUndefinedNamespaceInspection */
            $loadClassName = \UnknownNamespace\UnknownClass::class;
            $wasThrown = false;

            try {
                $this->injector->make($loadClassName);
            } catch (MetadataException $e) {
                $wasThrown = true;

                $this->assertSame("Can't retrieve class metadata", $e->getMessage());
                $this->assertSame($loadClassName, $e->getClassName());
                $this->assertSame('__construct', $e->getMethodName());
            }

            $this->assertTrue($wasThrown);
        }
    }
