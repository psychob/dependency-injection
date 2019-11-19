<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Injector;

    class Injector implements InjectorInterface
    {
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

                if ($rMethod === null) {
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

        private function prepareArgs(\ReflectionClass $klass, \ReflectionFunctionAbstract $refMethod, array $arguments): array
        {
            return [];
        }
    }
