<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Exceptions;

    class CyclicDependencyException extends DependencyInjectionException
    {
        protected $class;
        protected $cycle;

        /**
         * CyclicDependencyException constructor.
         *
         * @param string          $class
         * @param string[]        $cycle
         * @param \Throwable|null $previous
         */
        public function __construct(string $class, array $cycle, ?\Throwable $previous = NULL)
        {
            $this->class = $class;
            $this->cycle = $cycle;

            parent::__construct(sprintf('Detected cyclic dependency while trying to build: [%s]',
                                        implode(' <- ', $cycle)), 0, $previous);
        }

    }
