<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Exceptions;

    use RuntimeException;

    class AmbiguousInterfaceInitializationException extends DependencyInjectionException
    {
        /** @var string */
        protected $class;

        /** @var string[] */
        protected $implementations = [];

        /**
         * AmbiguousInterfaceInitializationException constructor.
         *
         * @param string          $class
         * @param string[]        $implementations
         * @param \Throwable|null $previous
         */
        public function __construct(string $class, array $implementations, ?\Throwable $previous = NULL)
        {
            $this->class = $class;
            $this->implementations = $implementations;

            parent::__construct($this->calculateMessage(), 0, $previous);
        }

        /**
         * @return string
         */
        public function getClass(): string
        {
            return $this->class;
        }

        /**
         * @return string[]
         */
        public function getImplementations(): array
        {
            return $this->implementations;
        }

        private function calculateMessage(): string
        {
        }

    }
