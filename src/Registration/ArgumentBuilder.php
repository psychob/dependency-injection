<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Registration;

    class ArgumentBuilder
    {
        const ARGUMENT_TYPE_NAMED = 'named';
        const ARGUMENT_TYPE_POSITIONAL = 'positional';

        /** @var RegistrationBuilder */
        protected $builder;

        /** @var string|int */
        protected $name;

        /** @var bool */
        protected $isNamed;

        /** @var string */
        protected $bind;

        /** @var string */
        protected $bindType;

        /**
         * ArgumentBuilder constructor.
         *
         * @param RegistrationBuilder $builder
         * @param int|string          $name
         * @param bool                $isNamed
         */
        public function __construct(RegistrationBuilder $builder, $name, bool $isNamed)
        {
            $this->builder = $builder;
            $this->name = $name;
            $this->isNamed = $isNamed;
        }

        public function __destruct()
        {
            $this->builder->__fetchArgumentBuilder($this);
        }

        public function bind($class): self
        {
            if (is_callable($class)) {
                $this->bindType = BindType::BIND_TYPE_FUNCTION;
            } else {
                $this->bindType = BindType::BIND_TYPE_CLASS;
            }

            $this->bind = $class;
            return $this;
        }

        public function literal($literal): self
        {
            $this->bindType = BindType::BIND_TYPE_LITERAL;
            $this->bind = $literal;

            return $this;
        }

        public function factory($class): self
        {
            $this->bindType = BindType::BIND_TYPE_FACTORY;
            $this->bind = $class;

            return $this;
        }

        public function serialize(): array
        {
            return [
                'type' => $this->isNamed ? self::ARGUMENT_TYPE_NAMED : self::ARGUMENT_TYPE_POSITIONAL,
                'id' => $this->name,
                'bind_type' => $this->bindType,
                'bind' => $this->bind,
            ];
        }
    }
