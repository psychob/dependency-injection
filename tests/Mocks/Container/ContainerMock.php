<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection\Mocks\Container;

    use PsychoB\DependencyInjection\Container\Container;

    class ContainerMock extends Container
    {
        /**
         * ContainerMock constructor.
         *
         * @param array $elements
         */
        public function __construct(array $elements = [])
        {
            $this->elements = $elements;
        }
    }
