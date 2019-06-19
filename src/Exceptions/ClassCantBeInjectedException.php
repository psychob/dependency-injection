<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Exceptions;

    class ClassCantBeInjectedException extends DependencyInjectionException
    {
        /** @var string */
        protected $class;

        /**
         * ClassCantBeInjectedException constructor.
         *
         * @param string          $class
         * @param \Throwable|null $previous
         */
        public function __construct(string $class, ?\Throwable $previous = NULL)
        {
            $this->class = $class;

            parent::__construct(sprintf("Can not inject class %s", $class), 0, $previous);
        }

        /**
         * @return string
         */
        public function getClass(): string
        {
            return $this->class;
        }
    }
