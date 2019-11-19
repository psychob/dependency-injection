<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Injector;

    interface InjectorInterface
    {
        public function inject($to, array $arguments);
        public function resolveArguments($to, array $arguments): array;
    }
