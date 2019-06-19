<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection;

    use PsychoB\DependencyInjection\Exceptions\AmbiguousInterfaceInitializationException;
    use PsychoB\DependencyInjection\Exceptions\ClassNotFoundException;
    use PsychoB\DependencyInjection\Registration\RegistrationBuilder;
    use PsychoB\DependencyInjection\Registration\RegistrationEntry;
    use PsychoB\ReflectionFile\ReflectionFile;
    use Symfony\Component\Finder\Finder;

    class Container implements ContainerInterface
    {
        /** @var mixed[] */
        protected $initialized = [];

        /** @var RegistrationEntry[] */
        protected $buildDefinitions = [];

        /** @var Injector */
        protected $injector;

        /** @var string[][] */
        protected $interfaces = [];

        /**
         * Container constructor.
         */
        public function __construct()
        {
            $this->injector = new Injector($this);
            $this->add(Injector::class, $this->injector);
            $this->add(Container::class, $this);
            $this->interfaces[ContainerInterface::class] = [Container::class];
        }

        /** @inheritDoc */
        public function has(string $class): bool
        {
            return array_key_exists($class, $this->initialized) ||
                array_key_exists($class, $this->buildDefinitions) ||
                array_key_exists($class, $this->interfaces);
        }

        /** @inheritDoc */
        public function get(string $class)
        {
            if (array_key_exists($class, $this->buildDefinitions)) {
                $obj = $this->injector->make($class);

                if ($this->buildDefinitions[$class]->canBeCached()) {
                    $this->initialized[$class] = $obj;
                }

                return $obj;
            } else if (array_key_exists($class, $this->initialized)) {
                return $this->initialized[$class];
            } else if (array_key_exists($class, $this->interfaces)) {
                if (count($this->interfaces[$class]) === 1) {
                    return $this->get($this->interfaces[$class][0]);
                } else {
                    throw new AmbiguousInterfaceInitializationException($class, $this->interfaces[$class]);
                }
            }

            throw new ClassNotFoundException($class, array_keys($this->initialized),
                                             array_keys($this->buildDefinitions), array_keys($this->interfaces));
        }

        /** @inheritDoc */
        public function add(string $class, $object): void
        {
            $this->initialized[$class] = $object;
        }

        /** @inheritDoc */
        public function build(string $class): RegistrationBuilder
        {
            return new RegistrationBuilder($this, $class);
        }

        /** @inheritDoc */
        public function register(RegistrationBuilder $register)
        {
            $serialize = $register->serialize();

            $this->buildDefinitions[$serialize->getName()] = $serialize;
        }

        /** @inheritDoc */
        public function getEntry(string $class): ?RegistrationEntry
        {
            return $this->buildDefinitions[$class] ?? NULL;
        }

        public function make(string $class, array $arguments = [])
        {
            $new = $this->injector->make($class, $arguments);

            $this->add($class, $new);

            return $new;
        }

        public function inject(string $class, ...$arguments)
        {
            return $this->injectMethod($class, '__construct', ...$arguments);
        }

        public function injectMethod($class, string $method, ...$arguments)
        {
            return $this->injector->inject($class, $method, ...$arguments);
        }

        public function implementsInterface(string $interface): array
        {
            return $this->interfaces[$interface] ?? [];
        }

        public function autowirePath(string ...$paths): void
        {
            $finder = new Finder();
            $finder->ignoreDotFiles(true)->ignoreUnreadableDirs()->files()->in($paths)->name('*.php');

            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    $file = new ReflectionFile($file->getRealPath());

                    foreach ($file->getClasses() as $class) {
                        foreach ($class->getInterfaceNames() as $interface) {
                            $this->interfaces[$interface][] = $class->getName();
                        }

                        $this->buildDefinitions[$class->getName()] = new RegistrationEntry($class->getName(), true, [],
                                                                                           true);
                    }
                }
            }
        }

    }
