<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Injector;

    use PsychoB\DependencyInjection\Container\ContainerInterface;

    class Injector implements InjectorInterface
    {
        /** @var ContainerInterface */
        protected $container;

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
                        $ret[] = $this->fetchClassFor($param->getType()->getName(), $klass->getName());
                    }
                } else {
                    throw new InvalidArgumentDefinitionException("Can't inject argument", $param, $refMethod, $klass);
                }
            }

            return $ret;
        }

        private function fetchClassFor(string $children, string $parent)
        {
            if ($this->container->has($children)) {
                return $this->container->get($children);
            }

            $c = $this->inject([$children, '__construct'], []);

            $this->container->add($children, $c, ContainerInterface::ADD_EXCEPTION);

            return $c;
        }
    }
