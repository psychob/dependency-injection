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
    use PsychoB\DependencyInjection\Container\Exceptions\ClassRetrievalException;

    /**
     * Container Interface
     *
     * @author Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
     * @since  0.8
     */
    interface ContainerInterface
    {
        /**
         * When encountering old value in container, overwrite it
         */
        public const ADD_OVERWRITE = 0;

        /**
         * When encountering old value in container, throw exception
         */
        public const ADD_EXCEPTION = 1;

        /**
         * When encountering old value in container, ignore new value
         */
        public const ADD_IGNORE = 2;

        /**
         * Check if container has defined $class
         *
         * @param string $class Class name
         *
         * @return bool
         */
        public function has(string $class): bool;

        /**
         * Get $class from container
         *
         * @param string $class Class name
         *
         * @return mixed
         *
         * @throws ClassNotFoundException When $class is not in container
         * @throws ClassRetrievalException When there was exception while loading class
         */
        public function get(string $class);

        /**
         * Add new value to container.
         *
         * @param string       $class  Value name
         * @param mixed|object $object Value
         * @param int          $type   Type of add (see ADD_ constant)
         *
         * @throws ClassAlreadyDefinedException When $type == ADD_EXCEPTION and $class already has a value
         */
        public function add(string $class, $object, int $type = self::ADD_OVERWRITE): void;

        /**
         * Make PsrContainerInterface compatible view.
         *
         * @return PsrContainerInterface
         */
        public function psr(): PsrContainerInterface;
    }
