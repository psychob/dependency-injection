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
    use PsychoB\DependencyInjection\Exceptions\MethodCantBeInjectedException;
    use PsychoB\DependencyInjection\Registration\ArgumentBuilder;
    use PsychoB\DependencyInjection\Registration\BindType;
    use PsychoB\DependencyInjection\Registration\RegistrationEntry;

    class Injector
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

        /**
         * @param string $class
         * @param array  $arguments
         *
         * @return mixed
         */
        public function make(string $class, array $arguments = [])
        {
            return $this->inject($class, '__construct', $arguments);
        }

        /**
         * @param string $class
         * @param string $method
         * @param array  $arguments
         *
         * @return mixed
         */
        public function inject($class, string $method, array $arguments = [])
        {
            if (is_object($class)) {
                return $this->injectMethod($class, $method, $arguments);
            } else {
                return $this->injectImpl($class, $method, $arguments);
            }
        }

        public function injectFunction(callable $callable, array $arguments = [])
        {
            $ref_func = new \ReflectionFunction($callable);

            $args = $this->fetchArgumentsFrom_TP($ref_func, $arguments);

            return $ref_func->invoke(...$args);
        }

        public function injectMethod($object, string $method, array $arguments)
        {
        }

        private function injectImpl(string $class, string $method, array $arguments)
        {
            $def = $this->container->getEntry($class);

            [$ref_class, $ref_method] = $this->fetchReflections($class, $method);

            try {
                if ($method === '__construct') {
                    if (in_array($class, $this->injectingCycle)) {
                        throw new CyclicDependencyException($class, $this->injectingCycle);
                    }

                    $this->injectingCycle[] = $class;
                }

                if ($ref_method === NULL) {
                    // php will only return null for constructor that wasn't defined (so it will supply default one)

                    return $this->createNewObject($class, $arguments, $def);
                }

                if ($method === '__construct') {
                    return $this->createNewObjectWith($ref_class, $ref_method, $arguments, $def);
                } else {
                    return $this->injectMethodWith($ref_class, $ref_method, $arguments, $def);
                }
            } finally {
                if ($method === '__construct') {
                    array_pop($this->injectingCycle);
                }
            }
        }

        private function fetchReflections(string $class, string $method): array
        {
            try {
                $ref_class = new \ReflectionClass($class);
            } catch (\ReflectionException $e) {
                throw new ClassCantBeInjectedException($class, $this->injectingCycle, $e);
            }

            if ($method === '__construct') {
                return [$ref_class, $ref_class->getConstructor()];
            }

            try {
                $ref_method = $ref_class->getMethod($method);
            } catch (\ReflectionException $e) {
                throw new MethodCantBeInjectedException($class, $method, $this->injectingCycle, $e);
            }

            return [$ref_class, $ref_method];
        }

        private function createNewObject(string $class, array $arguments, ?RegistrationEntry $def)
        {
            return new $class(...$arguments);
        }

        private function createNewObjectWith(\ReflectionClass $ref_class, \ReflectionMethod $ref_method,
                                             array $arguments, ?RegistrationEntry $def)
        {
            if (empty($ref_method->getParameters())) {
                return $this->createNewObject($ref_class->getName(), $arguments, $def);
            }

            $args = [];

            if (empty($arguments)) {
                $args = $this->fetchArgumentsFromVoid($ref_class, $ref_method, $def);
            } else {
                $args = $this->fetchArgumentsFromList($ref_class, $ref_method, $arguments, $def);
            }

            return $ref_class->newInstance(...$args);
        }

        private function fetchArgumentsFrom_T(\ReflectionFunctionAbstract $ref, string $className = 'anonymous'): array
        {
            $ret = [];

            foreach ($ref->getParameters() as $param) {
                $ret[] = $this->resolveParameterWithAutowire($param, $className, $ref->getName());
            }

            return $ret;
        }

        private function fetchArgumentsFrom_TP(\ReflectionFunctionAbstract $ref, array $parameters,
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

        private function fetchArgumentsFrom_TD(\ReflectionFunctionAbstract $ref, RegistrationEntry $def,
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

        private function fetchArgumentsFrom_TPD(\ReflectionFunctionAbstract $ref, array $arguments,
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

        private function fetchArgumentsFromVoid(\ReflectionClass $ref_class, \ReflectionMethod $ref_method,
                                                ?RegistrationEntry $def)
        {
            if ($def === NULL) {
                return $this->fetchArgumentsFrom_T($ref_method, $ref_class->getName());
            } else {
                return $this->fetchArgumentsFrom_TD($ref_method, $def, $ref_class->getName());
            }
        }

        private function fetchArgumentsFromList(\ReflectionClass $ref_class, \ReflectionMethod $ref_method,
                                                array $arguments, ?RegistrationEntry $def)
        {
            if ($def === NULL) {
                return $this->fetchArgumentsFrom_TP($ref_method, $arguments, $ref_class->getName());
            } else {
                return $this->fetchArgumentsFrom_TPD($ref_method, $arguments, $def, $ref_class->getName());
            }
        }

        private function resolveParameterWithAutowire(\ReflectionParameter $param, string $className = 'anonymous',
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

        private function resolveParameterWithDefinition(\ReflectionParameter $param, RegistrationEntry $def,
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

        private function resolveBind(string $type, $bind)
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
                    return $this->injectFunction($bind);

                case BindType::BIND_TYPE_FACTORY:
                    return $this->injectFunction($bind);

                default:
                    throw new InternalException("Unknown bind type: {$type}");
            }
        }
    }
