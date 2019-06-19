<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Exceptions;

    class MethodCantBeInjectedException extends DependencyInjectionException
    {
        /** @var string */
        protected $class;

        /** @var string */
        protected $method;

        /**
         * MethodCantBeInjectedException constructor.
         *
         * @param string          $class
         * @param string          $method
         * @param \Throwable|null $previous
         */
        public function __construct(string $class, string $method, ?\Throwable $previous = NULL)
        {
            $this->class = $class;
            $this->method = $method;

            parent::__construct(sprintf('Can not inject %s::%s', $class, $method), 0, $previous);
        }

    }
