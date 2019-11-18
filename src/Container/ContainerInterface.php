<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Container;

    use Psr\Container\ContainerInterface as PsrContainerInterface;
    use PsychoB\DependencyInjection\Container\Exceptions\ClassNotFoundException;
    use PsychoB\DependencyInjection\Container\Exceptions\ClassRetrievalException;

    interface ContainerInterface
    {
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

        public function add(string $class, $object, int $type): void;

        public function psr(): PsrContainerInterface;
    }
