<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Injector\Exceptions;

    use Throwable;

    class InjectionException extends InjectionBaseException
    {
        /** @var string */
        private $class;

        /** @var string */
        private $method;

        public function __construct($message, string $class, string $method, Throwable $previous = NULL)
        {
            parent::__construct($message, 0, $previous);
            $this->class = $class;
            $this->method = $method;
        }

        /**
         * @return string
         */
        public function getClass(): string
        {
            return $this->class;
        }

        /**
         * @return string
         */
        public function getMethod(): string
        {
            return $this->method;
        }
    }
