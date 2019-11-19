<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Container\Exceptions;

    use RuntimeException;
    use Throwable;

    /**
     * Exception used when class is already defined and you requested to throw.
     *
     * @author Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
     * @since  0.8
     */
    class ClassAlreadyDefinedException extends RuntimeException
    {
        protected $class;
        protected $oldValue;
        protected $newValue;

        /**
         * ClassAlreadyDefinedException constructor.
         *
         * @param string         $class
         * @param mixed          $oldValue
         * @param mixed          $newValue
         * @param Throwable|null $previous
         */
        public function __construct(string $class, $oldValue, $newValue, Throwable $previous = NULL)
        {
            $this->class = $class;
            $this->oldValue = $oldValue;
            $this->newValue = $newValue;

            parent::__construct('Class already has a value: ' . $this->class, 0, $previous);
        }

        /**
         * @return string
         */
        public function getClass(): string
        {
            return $this->class;
        }

        /**
         * @return mixed
         */
        public function getOldValue()
        {
            return $this->oldValue;
        }

        /**
         * @return mixed
         */
        public function getNewValue()
        {
            return $this->newValue;
        }
    }
