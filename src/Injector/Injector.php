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
        }

        /** @inheritDoc */
        public function resolveArguments($to, array $arguments): array
        {
        }
    }
