<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Injector;

    interface InjectorInterface
    {
        /**
         * This method returns instance of $class. It might create new one, or reuse instance that is already created
         * and cached.
         *
         * @param string $class     Class name
         * @param array  $arguments Arguments passed to constructor
         *
         * @return object
         */
        public function make(string $class, array $arguments = []);

        public function inject($to, array $arguments = []);
    }
