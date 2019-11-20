<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Injector;

    use PsychoB\DependencyInjection\Container\ContainerInterface;
    use PsychoB\DependencyInjection\Injector\Exceptions\ClassCreationException;
    use PsychoB\DependencyInjection\Injector\Exceptions\CyclicDependencyDetectedException;

    class Injector implements InjectorInterface
    {
        /** @var ContainerInterface */
        protected $container;
        protected $currentlyConstructed = [];

        /**
         * Injector constructor.
         *
         * @param ContainerInterface $container
         */
        public function __construct(ContainerInterface $container)
        {
            $this->container = $container;
        }

        /** @inheritDoc */
        public function make(string $class, array $arguments = [])
        {
            // we won't make a new instance if we already have one in cache
            if ($this->container->has($class)) {
                return $this->container->get($class);
            }

            try {
                if (in_array($class, $this->currentlyConstructed)) {
                    throw new CyclicDependencyDetectedException('Cyclic dependency detected',
                        $this->currentlyConstructed);
                }

                $this->currentlyConstructed[] = $class;

                try {
                    $refClass = new \ReflectionClass($class);
                } catch (\ReflectionException $e) {
                    throw new ClassCreationException("Can't retrieve class metadata", $class, $e);
                }

                $constructor = $refClass->getConstructor();
                $newInstance = NULL;

                if ($constructor === NULL) {
                    // if class doesn't have a constructor, we assume we have implicit constructor created by PHP. For some
                    // reason it's reported as NULL
                    $newInstance = $this->createWith($refClass, $arguments);
                } else {
                    if ($constructor->isPublic()) {
                        $args = $this->prepareArgs($refClass, $constructor, $arguments);
                        $newInstance = $this->createWith($refClass, $args);
                    } else {
                        throw new ClassCreationException("Class constructor is not public", $class);
                    }
                }

                $this->container->add($class, $newInstance, ContainerInterface::ADD_EXCEPTION);

                return $newInstance;
            } finally {
                array_pop($this->currentlyConstructed);
            }
        }

        private function createWith(\ReflectionClass $klass, array $arguments)
        {
            return $klass->newInstance(...$arguments);
        }

        /** @inheritDoc */
        public function inject($to, array $arguments)
        {
            if (is_array($to)) {
                if (count($to) === 2) {
                    if (is_string($to[1])) {
                        if (is_object($to[0])) {
                            return $this->injectIntoObject($to[0], $to[1], $arguments);
                        }

                        if (is_string($to[0])) {
                            return $this->injectIntoStaticString($to[0], $to[1], $arguments);
                        }
                    }
                }

                throw InvalidCallable::invalidArraySyntax($to, $arguments);
            }

            if (is_string($to)) {
                if (preg_match('/^([^::]+)::([^::]+)$/i', $to, $m)) {
                    return $this->injectIntoStaticString($m[1], $m[2], $arguments);
                }
            }

            if (is_callable($to)) {
                return $this->injectFromCallable($to, $arguments);
            }

            throw new \InvalidArgumentException("\$to is not a callable");
        }

        /** @inheritDoc */
        public function resolveArguments($to, array $arguments): array
        {
        }

        private function injectIntoStaticString(string $class, string $method, array $arguments)
        {
            $rKlass = new \ReflectionClass($class);

            if ($method === '__construct') {
                $rMethod = $rKlass->getConstructor();

                if ($rMethod === NULL) {
                    return $this->createFrom($rKlass, []);
                }

                $preparedArguments = $this->prepareArgs($rKlass, $rMethod, $arguments);

                return $this->createFrom($rKlass, $preparedArguments);
            }
        }

        private function createFrom(\ReflectionClass $rKlass, array $array)
        {
            return $rKlass->newInstance(...$array);
        }

        private function prepareArgs(\ReflectionClass $klass,
            \ReflectionFunctionAbstract $refMethod,
            array $arguments): array
        {
            $ret = [];

            foreach ($refMethod->getParameters() as $it => $param) {
                if (array_key_exists($it, $arguments)) {
                    $ret[] = $arguments[$it];
                } else if (array_key_exists($param->getName(), $arguments)) {
                    $ret[] = $arguments[$param->getName()];
                } else if ($param->isDefaultValueAvailable()) {
                    $ret[] = $param->getDefaultValue();
                } else if ($param->hasType()) {
                    if ($param->getType()->isBuiltin()) {
                        if ($param->getType()->allowsNull()) {
                            $ret[] = NULL;
                        } else {
                            throw new InvalidArgumentDefinitionException("Builtin types are not injectable by itself",
                                $param, $refMethod, $klass);
                        }
                    } else {
                        $ret[] = $this->make($param->getType()->getName());
                    }
                } else {
                    throw new InvalidArgumentDefinitionException("Can't inject argument", $param, $refMethod, $klass);
                }
            }

            return $ret;
        }
    }
