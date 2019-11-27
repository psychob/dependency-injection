<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Container;

    use Psr\Container\ContainerInterface as PsrContainerInterface;
    use PsychoB\DependencyInjection\Container\Exceptions\ClassAlreadyDefinedException;
    use PsychoB\DependencyInjection\Container\Exceptions\ClassNotFoundException;

    /**
     * Container Implementation
     *
     * @author Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
     * @since  0.8
     */
    class Container implements ContainerInterface
    {
        protected $elements = [];

        /** @inheritDoc */
        public function has(string $class): bool
        {
            return array_key_exists($class, $this->elements);
        }

        /** @inheritDoc */
        public function get(string $class)
        {
            if (!$this->has($class)) {
                throw new ClassNotFoundException($class, array_keys($this->elements));
            }

            return $this->elements[$class];
        }

        /** @inheritDoc */
        public function add(string $class, $object, int $type = ContainerInterface::ADD_OVERWRITE): void
        {
            if ($this->has($class)) {
                switch ($type) {
                    case ContainerInterface::ADD_OVERWRITE:
                        $this->elements[$class] = $object;
                        break;

                    case ContainerInterface::ADD_EXCEPTION:
                        throw new ClassAlreadyDefinedException($class, $this->get($class), $object);

                    case ContainerInterface::ADD_IGNORE:
                        break;
                }
            } else {
                $this->elements[$class] = $object;
            }
        }

        /** @inheritDoc */
        public function psr(): PsrContainerInterface
        {
            return new PsrContainerAdapter($this);
        }
    }
