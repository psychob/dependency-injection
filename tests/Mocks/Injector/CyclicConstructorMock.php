<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection\Mocks\Injector;

    class CyclicConstructorMock
    {
        /** @var CyclicConstructorMock */
        public $cyclic;

        /**
         * CyclicConstructorMock constructor.
         *
         * @param CyclicConstructorMock $cyclic
         */
        public function __construct(CyclicConstructorMock $cyclic)
        {
            $this->cyclic = $cyclic;
        }

    }
