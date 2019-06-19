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
        protected $cycle;

        /**
         * ClassCantBeInjectedException constructor.
         *
         * @param string          $class
         * @param array           $cycle
         * @param \Throwable|null $previous
         */
        public function __construct(string $class, array $cycle, ?\Throwable $previous = NULL)
        {
            $this->class = $class;
            $this->cycle = $cycle;

            if (!empty($cycle)) {
                parent::__construct(sprintf('Can not inject class \'%s\' while trying to build: [%s]', $class,
                                            implode(' -> ', $cycle)), 0, $previous);
            } else {
                parent::__construct(sprintf("Can not inject class '%s'", $class), 0, $previous);
            }
        }

        /**
         * @return string
         */
        public function getClass(): string
        {
            return $this->class;
        }
    }
