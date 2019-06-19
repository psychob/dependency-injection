<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Exceptions;

    use RuntimeException;
    use Throwable;

    class ClassNotFoundException extends DependencyInjectionException
    {
        /** @var string */
        protected $class;

        /** @var string[] */
        protected $keysInitialized = [];

        /** @var string[] */
        protected $keysDefinitions = [];

        /** @var string[] */
        protected $keysInterfaces = [];

        /**
         * ClassNotFoundException constructor.
         *
         * @param string         $class
         * @param string[]       $keysInitialized
         * @param string[]       $keysDefinitions
         * @param string[]       $keysInterfaces
         * @param Throwable|null $previous
         */
        public function __construct(string $class, array $keysInitialized, array $keysDefinitions,
                                    array $keysInterfaces, ?Throwable $previous = NULL)
        {
            $this->class = $class;
            $this->keysInitialized = $keysInitialized;
            $this->keysDefinitions = $keysDefinitions;
            $this->keysInterfaces = $keysInterfaces;

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
        public function getKeysInitialized(): array
        {
            return $this->keysInitialized;
        }

        /**
         * @return string[]
         */
        public function getKeysDefinitions(): array
        {
            return $this->keysDefinitions;
        }

        /**
         * @return string[]
         */
        public function getKeysInterfaces(): array
        {
            return $this->keysInterfaces;
        }

        /**
         * @return string
         */
        private function calculateMessage(): string
        {
            $allElements = $this->keysInterfaces + $this->keysInitialized + $this->keysDefinitions;
            $onlyClassName = [];
            $ranks = [];
            $simpleClassName = substr($this->class, (strrpos($this->class, '\\') ?? -1) + 1);

            foreach ($allElements as $element) {
                $lastPos = strrpos($element, '\\');

                if ($lastPos === false) {
                    $onlyClassName[] = $element;
                } else {
                    $onlyClassName[] = substr($element, $lastPos + 1);
                }
            }

            foreach ($onlyClassName as $k => $class) {
                $ranks[$k] = 0;
                similar_text($simpleClassName, $class, $ranks[$k]);
            }

            $filtered = array_filter($allElements, function ($element, $key) use ($ranks) {
                return $ranks[$key] >= 75;
            }, ARRAY_FILTER_USE_BOTH);

            uksort($filtered, function ($left, $right) use ($ranks) {
                return -($ranks[$left] <=> $ranks[$right]);
            });

            if (count($filtered) > 0) {
                return sprintf("Class '%s' was not registered, did you mean: %s", $this->class, implode(' or ', array_slice($filtered, 0, 3, false)));
            } else {
                return sprintf("Class '%s' was not registered", $this->class);
            }
        }
    }
