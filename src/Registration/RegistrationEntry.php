<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Registration;

    final class RegistrationEntry
    {
        protected $name;
        protected $isSingleton;
        protected $arguments;
        protected $autoWire;
        protected $bindType;
        protected $bind;

        /**
         * RegistrationEntry constructor.
         *
         * @param string                $name
         * @param bool                  $autoWire
         * @param array                 $arguments
         * @param bool                  $isSingleton
         * @param string                $bindType
         * @param object|callable|mixed $bind
         */
        public function __construct(string $name, bool $autoWire, array $arguments, bool $isSingleton, string $bindType,
                                    $bind)
        {
            $this->name = $name;
            $this->arguments = $arguments;
            $this->autoWire = $autoWire;
            $this->isSingleton = $isSingleton;
            $this->bindType = $bindType;
            $this->bind = $bind;
        }

        /**
         * @return string
         */
        public function getName(): string
        {
            return $this->name;
        }

        /**
         * @return array
         */
        public function getArguments(): array
        {
            return $this->arguments;
        }

        /**
         * @return bool
         */
        public function isAutoWire(): bool
        {
            return $this->autoWire;
        }

        /**
         * @return bool
         */
        public function isSingleton(): bool
        {
            return $this->isSingleton;
        }

        /**
         * @return string
         */
        public function getBindType(): string
        {
            return $this->bindType;
        }

        /**
         * @return callable|mixed|object
         */
        public function getBind()
        {
            return $this->bind;
        }

        public function canBeCached(): bool
        {
            if (!$this->isSingleton) {
                return false;
            }

            if (in_array($this->bindType, [BindType::BIND_TYPE_LITERAL, BindType::BIND_TYPE_FUNCTION])) {
                return false;
            }

            foreach ($this->arguments as $argument) {
                if ($argument['bind_type'] !== BindType::BIND_TYPE_FUNCTION) {
                    return false;
                }
            }

            return true;
        }

        public function getArg(string $name): ?array
        {
            foreach ($this->arguments as $arg) {
                if ($arg['type'] === 'named' && $arg['id'] === $name) {
                    return $arg;
                }
            }

            return NULL;
        }

        public function getPos(int $id): ?array
        {
            foreach ($this->arguments as $arg) {
                if ($arg['type'] === 'positional' && $arg['id'] === $id) {
                    return $arg;
                }
            }

            return NULL;
        }
    }
