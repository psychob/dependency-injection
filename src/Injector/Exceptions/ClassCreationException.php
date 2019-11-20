<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Injector\Exceptions;

    class ClassCreationException extends \RuntimeException
    {
        /** @var string */
        protected $className;

        /**
         * ClassCreationException constructor.
         *
         * @param string          $message
         * @param string          $className
         * @param \Throwable|null $previous
         */
        public function __construct(string $message, string $className, ?\Throwable $previous = null)
        {
            $this->className = $className;

            parent::__construct($message, 0, $previous);
        }

        /**
         * @return string
         */
        public function getClassName(): string
        {
            return $this->className;
        }
    }
