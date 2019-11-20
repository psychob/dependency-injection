<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace Tests\PsychoB\DependencyInjection\Mocks\Injector;

    class SimpleConstructorRequiringClassMock
    {
        /** @var NoConstructorMock */
        public $empty;

        /**
         * SimpleConstructorRequiringClassMock constructor.
         *
         * @param NoConstructorMock $empty
         */
        public function __construct(NoConstructorMock $empty)
        {
            $this->empty = $empty;
        }
    }
