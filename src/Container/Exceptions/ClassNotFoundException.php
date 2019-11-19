<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Container\Exceptions;

    use Psr\Container\NotFoundExceptionInterface;
    use Throwable;

    /**
     * Exception used when class is not found in container.
     *
     * @author Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
     * @since  0.8
     */
    class ClassNotFoundException extends \RuntimeException implements NotFoundExceptionInterface
    {
        protected $class;
        protected $rest;

        public function __construct($class, array $elements, Throwable $previous = NULL)
        {
            $this->class = $class;
            $this->rest = $elements;

            parent::__construct(sprintf('Element %s not found in: [%s]', $class, implode(', ', $elements)), 0, $previous);
        }

        /**
         * @return mixed
         */
        public function getClass()
        {
            return $this->class;
        }

        /**
         * @return array
         */
        public function getRest(): array
        {
            return $this->rest;
        }
    }
