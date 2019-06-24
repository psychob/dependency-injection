<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Registration;

    use PsychoB\DependencyInjection\ContainerInterface;

    class RegistrationBuilder
    {
        /** @var string */
        protected $name;

        /** @var bool */
        protected $autoWire = false;

        /** @var mixed[] */
        protected $arguments = [];

        /** @var int */
        protected $positionalNumber = 0;

        /** @var ContainerInterface */
        protected $container;

        /** @var bool */
        protected $isSingleton = true;

        /** @var string */
        protected $bindType = BindType::BIND_TYPE_NONE;

        /** @var callable|object|string */
        protected $bind;

        /**
         * RegistrationBuilder constructor.
         *
         * @param ContainerInterface $container
         * @param string             $name
         */
        public function __construct(ContainerInterface $container, string $name)
        {
            $this->name = $name;
            $this->container = $container;
        }

        public function __destruct()
        {
            $this->container->register($this);
        }

        public function autoWire(bool $wire = true): self
        {
            $this->autoWire = $wire;

            return $this;
        }

        public function singleton(bool $is = true): self
        {
            $this->isSingleton = $is;

            return $this;
        }

        public function argument(string $name): ArgumentBuilder
        {
            return new ArgumentBuilder($this, $name, true);
        }

        public function positional(int $number = -1): ArgumentBuilder
        {
            if ($number === -1) {
                $number = $this->positionalNumber++;
            } else {
                $this->positionalNumber = $number + 1;
            }

            return new ArgumentBuilder($this, $number, false);
        }

        public function serialize()
        {
            return new RegistrationEntry($this->name, $this->autoWire, $this->arguments, $this->isSingleton, $this->bindType, $this->bind);
        }

        public function literal($object): self
        {
            $this->bindType = BindType::BIND_TYPE_LITERAL;
            $this->bind = $object;

            return $this;
        }

        public function bind($object): self
        {
            if (is_callable($object)) {
                $this->bindType = BindType::BIND_TYPE_FUNCTION;
            } else {
                $this->bindType = BindType::BIND_TYPE_CLASS;
            }

            $this->bind = $object;

            return $this;
        }

        public function factory($class): self
        {
            $this->bindType = BindType::BIND_TYPE_FACTORY;
            $this->bind = $class;

            return $this;
        }

        public function __fetchArgumentBuilder(ArgumentBuilder $builder): void
        {
            $this->arguments[] = $builder->serialize();
        }
    }
