<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection;

    use PsychoB\DependencyInjection\Exceptions\CantInjectParameterException;
    use PsychoB\DependencyInjection\Exceptions\ClassCantBeInjectedException;
    use PsychoB\DependencyInjection\Exceptions\CyclicDependencyException;
    use PsychoB\DependencyInjection\Registration\BindType;
    use PsychoB\DependencyInjection\Registration\RegistrationEntry;
    use ReflectionClass;
    use ReflectionException;
    use ReflectionFunction;
    use ReflectionFunctionAbstract;

    class Injector implements InjectorInterface
    {
        /** @var ContainerInterface */
        protected $container;

        /** @var string[] */
        protected $injectingCycle = [];

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
            return $this->inject([$class, '__construct'], $arguments);
        }

        /** @inheritDoc */
        public function inject($func, array $arguments = [])
        {
            if (is_array($func)) {
                if (count($func) === 2) {
                    if ($func[1] === '__construct') {
                        return $this->makeNewInstance($func[0], $arguments);
                    } else {
                        if (is_object($func[0])) {
                            return $this->injectIntoObject($func[0], $func[1], $arguments);
                        } else {
                            return $this->injectIntoStatic($func[0], $func[1], $arguments);
                        }
                    }
                } else {
                    throw InvalidArgumentException::invalidCallable('$func',
                                                                    'Callable array must have only two arguments');
                }
            } else if (is_callable($func)) {
                return $this->injectIntoFunction($func, $arguments);
            } else {
                throw InvalidArgumentException::invalidCallable('$func', 'Callable array must have only two arguments');
            }
        }

        protected function makeNewInstance(string $class, array $arguments)
        {
            try {
                if (in_array($class, $this->injectingCycle)) {
                    throw new CyclicDependencyException($class, $this->injectingCycle);
                }

                $this->injectingCycle[] = $class;

                /** @noinspection PhpUnhandledExceptionInspection */
                $ref_class = new ReflectionClass($class);
                $ref_constructor = $ref_class->getConstructor();

                if ($ref_constructor === NULL) {
                    // default - not defined - constructor
                    return $ref_class->newInstance();
                }

                $args = $this->fetchArgumentsFrom($ref_constructor, $arguments, $this->container->getEntry($class),
                                                  $class);
                return $ref_class->newInstance(...$args);
            } finally {
                array_pop($this->injectingCycle);
            }
        }

        protected function fetchArgumentsFrom(ReflectionFunctionAbstract $method, array $arguments,
                                              ?RegistrationEntry $def, string $className = 'anonymous'): array
        {
            if ($def === NULL) {
                if (empty($arguments)) {
                    return $this->fetchArgumentsFrom_T($method, $className);
                } else {
                    return $this->fetchArgumentsFrom_TP($method, $arguments, $className);
                }
            } else {
                if (empty($arguments)) {
                    return $this->fetchArgumentsFrom_TD($method, $def, $className);
                } else {
                    return $this->fetchArgumentsFrom_TPD($method, $arguments, $def, $className);
                }
            }
        }

        protected function fetchArgumentsFrom_T(ReflectionFunctionAbstract $ref, string $className = 'anonymous'): array
        {
            $ret = [];

            foreach ($ref->getParameters() as $param) {
                $ret[] = $this->resolveParameterWithAutowire($param, $className, $ref->getName());
            }

            return $ret;
        }

        protected function fetchArgumentsFrom_TP(ReflectionFunctionAbstract $ref, array $parameters,
                                                 string $className = 'anonymous'): array
        {
            if (empty($parameters)) {
                return $this->fetchArgumentsFrom_T($ref, $className);
            } else {
                $ret = [];

                foreach ($ref->getParameters() as $no => $param) {
                    if (array_key_exists($no, $parameters) && !Arguments::isAny($parameters[$no])) {
                        // positional
                        $ret[] = $parameters[$no];
                    } else if (array_key_exists($param->getName(),
                                                $parameters) && !Arguments::isAny($parameters[$param->getName()])) {
                        // named
                        $ret[] = $parameters[$param->getName()];
                    } else {
                        $ret[] = $this->resolveParameterWithAutowire($param, $className, $ref->getName());
                    }
                }

                return $ret;
            }
        }

        protected function fetchArgumentsFrom_TD(ReflectionFunctionAbstract $ref, RegistrationEntry $def,
                                                 string $className = 'anonymous'): array
        {
            if ($def->isAutoWire() && empty($def->getArguments())) {
                return $this->fetchArgumentsFrom_T($ref, $className);
            } else {
                $ret = [];

                foreach ($ref->getParameters() as $no => $param) {
                    [$value, $returned] = $this->resolveParameterWithDefinition($param, $def, $className,
                                                                                $ref->getName());

                    if ($returned) {
                        $ret[] = $value;
                    } else if ($def->isAutoWire()) {
                        $ret[] = $this->resolveParameterWithAutowire($param, $className, $ref->getName());
                    } else {
                        throw new MissingArgumentDefinitionException($className, $ref->getName(), $param->getName());
                    }
                }

                return $ret;
            }
        }

        protected function fetchArgumentsFrom_TPD(ReflectionFunctionAbstract $ref, array $arguments,
                                                  RegistrationEntry $def, string $className = 'anonymous'): array
        {
            if ($def->isAutoWire() && empty($def->getArguments())) {
                return $this->fetchArgumentsFrom_TP($ref, $arguments, $className);
            } else {
                $ret = [];

                foreach ($ref->getParameters() as $no => $param) {
                    // arguments have priority
                    if (array_key_exists($no, $arguments) && !Arguments::isAny($arguments[$no])) {
                        // positional
                        $ret[] = $arguments[$no];
                    } else if (array_key_exists($param->getName(),
                                                $arguments) && !Arguments::isAny($arguments[$param->getName()])) {
                        // named
                        $ret[] = $arguments[$param->getName()];
                    } else {
                        [$value, $returned] = $this->resolveParameterWithDefinition($param, $def, $className,
                                                                                    $ref->getName());

                        if ($returned) {
                            $ret[] = $value;
                        } else if ($def->isAutoWire()) {
                            $ret[] = $this->resolveParameterWithAutowire($param, $className, $ref->getName());
                        } else {
                            throw new MissingArgumentDefinitionException($className, $ref->getName(),
                                                                         $param->getName());
                        }
                    }
                }

                return $ret;
            }
        }

        protected function resolveParameterWithAutowire(\ReflectionParameter $param, string $className = 'anonymous',
                                                        string $methodName = 'anonymous')
        {
            $type = $param->getType();
            $ret = NULL;

            if ($type === NULL || $type->isBuiltin()) {
                throw new CantInjectParameterException($className, $methodName, $param->getName(),
                                                       $this->injectingCycle);
            }

            if ($this->container->has($type->getName())) {
                $ret = $this->container->get($type->getName());
            } else {
                $ret = $this->container->make($type->getName());
            }

            return $ret;
        }

        protected function resolveParameterWithDefinition(\ReflectionParameter $param, RegistrationEntry $def,
                                                          string $className = 'anonymous',
                                                          string $methodName = 'anonymous')
        {
            $no = $param->getPosition();

            $nDef = $def->getArg($param->getName());
            $pDef = $def->getPos($no);

            if ($nDef !== NULL && $pDef !== NULL) {
                throw new InconsistentDefinitionForArgumentException($className, $methodName, $param->getName());
            }

            $actual = $nDef ?? $pDef;

            if ($actual) {
                return [$this->resolveBind($actual['bind_type'], $actual['bind']), true];
            }

            return [NULL, false];
        }

        protected function resolveBind(string $type, $bind)
        {
            switch ($type) {
                case BindType::BIND_TYPE_LITERAL:
                    return $bind;

                case BindType::BIND_TYPE_CLASS:
                    if ($this->container->has($bind)) {
                        return $this->container->get($bind);
                    } else {
                        return $this->container->make($bind);
                    }

                case BindType::BIND_TYPE_FUNCTION:
                    return $this->inject($bind);

                case BindType::BIND_TYPE_FACTORY:
                    return $this->inject($bind);

                default:
                    throw new InternalException("Unknown bind type: {$type}");
            }
        }

        protected function injectIntoObject($object, string $method, array $arguments)
        {
            /** @noinspection PhpUnhandledExceptionInspection */
            $ref_class = new ReflectionClass($object);
            try {
                $ref_method = $ref_class->getMethod($method);
            } catch (ReflectionException $e) {
                throw new ClassCantBeInjectedException($ref_class->getName(), $this->injectingCycle, $e);
            }

            $args = $this->fetchArgumentsFrom($ref_method, $arguments,
                                              $this->container->getEntry($ref_class->getName()), $ref_class->getName());
            return $ref_method->invoke($object, ...$args);
        }

        protected function injectIntoStatic(string $className, string $method, array $arguments)
        {
            /** @noinspection PhpUnhandledExceptionInspection */
            $ref_class = new ReflectionClass($className);
            try {
                $ref_method = $ref_class->getMethod($method);
            } catch (ReflectionException $e) {
                throw new ClassCantBeInjectedException($ref_class->getName(), $this->injectingCycle, $e);
            }

            $args = $this->fetchArgumentsFrom($ref_method, $arguments,
                                              $this->container->getEntry($ref_class->getName()), $ref_class->getName());
            return call_user_func([$className, $method], ...$args);
        }

        protected function injectIntoFunction(callable $func, array $arguments)
        {
            /** @noinspection PhpUnhandledExceptionInspection */
            $ref_method = new ReflectionFunction($func);

            $args = $this->fetchArgumentsFrom($ref_method, $arguments, NULL);
            return $ref_method->invoke(...$args);
        }
    }
