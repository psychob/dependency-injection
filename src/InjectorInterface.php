<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection;

    interface InjectorInterface
    {
        /**
         * Inject $func arguments using $arguments and container
         *
         * @param string|callable|string[] $func
         * @param mixed[]                  $arguments
         *
         * @return mixed
         */
        public function inject($func, array $arguments);

        /**
         * Make new instance of $class
         *
         * @param string  $class
         * @param mixed[] $arguments
         *
         * @return mixed $class
         */
        public function make(string $class, array $arguments = []);
    }
