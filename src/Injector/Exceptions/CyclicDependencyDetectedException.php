<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Injector\Exceptions;

    use Throwable;

    class CyclicDependencyDetectedException extends \RuntimeException
    {
        /** @var string[] */
        protected $cycle = [];

        /**
         * CyclicDependencyDetectedException constructor.
         *
         * @param string         $message
         * @param string[]       $className
         * @param Throwable|null $previous
         */
        public function __construct(string $message, array $className, ?Throwable $previous = NULL)
        {
            $this->cycle = $className;

            parent::__construct($message, 0, $previous);
        }

        /**
         * @return string[]
         */
        public function getCycle(): array
        {
            return $this->cycle;
        }
    }
