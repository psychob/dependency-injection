<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Exceptions;

    class CantInjectParameterException extends DependencyInjectionException
    {
        /** @var string */
        protected $class;

        /** @var string */
        protected $method;

        /** @var string */
        protected $paramName;

        /** @var string[] */
        protected $cycle = [];

        /**
         * CantInjectParameterException constructor.
         *
         * @param string          $class
         * @param string          $method
         * @param string          $paramName
         * @param string[]        $cycle
         * @param \Throwable|null $previous
         */
        public function __construct(string $class, string $method, string $paramName, array $cycle,
                                    ?\Throwable $previous = NULL)
        {
            $this->class = $class;
            $this->method = $method;
            $this->paramName = $paramName;
            $this->cycle = $cycle;

            parent::__construct(sprintf('Can not inject parameter: $%s in %s::%s', $paramName, $class, $method));
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

        /**
         * @return string
         */
        public function getParamName(): string
        {
            return $this->paramName;
        }

        /**
         * @return string[]
         */
        public function getCycle(): array
        {
            return $this->cycle;
        }
    }
