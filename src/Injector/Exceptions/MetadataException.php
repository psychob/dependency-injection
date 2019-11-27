<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Injector\Exceptions;

    use Throwable;

    class MetadataException extends InjectionBaseException
    {
        /** @var string */
        private $className;

        /** @var string */
        private $methodName;

        public function __construct($message, string $class, string $method, Throwable $previous = NULL)
        {
            parent::__construct($message, 0, $previous);

            $this->className = $class;
            $this->methodName = $method;
        }

        /**
         * @return string
         */
        public function getClassName(): string
        {
            return $this->className;
        }

        /**
         * @return string
         */
        public function getMethodName(): string
        {
            return $this->methodName;
        }

        public static function class(string $class, string $method, \Throwable $e): self
        {
            return new self("Can't retrieve class metadata", $class, $method, $e);
        }

        public static function method(string $class, string $method, \Throwable $e): self
        {
            return new self("Can't retrieve method metadata", $class, $method, $e);
        }
    }
