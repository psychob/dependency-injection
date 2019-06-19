<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection;

    use Psr\Container\ContainerInterface as PsrContainerInterface;

    class PsrContainerAdapter implements PsrContainerInterface
    {
        /** @var ContainerInterface */
        protected $container;

        /**
         * PsrContainerAdapter constructor.
         *
         * @param ContainerInterface $container
         */
        public function __construct(ContainerInterface $container)
        {
            $this->container = $container;
        }

        /**
         * @inheritDoc
         */
        public function get($id)
        {
            return $this->container->get($id);
        }

        /**
         * @inheritDoc
         */
        public function has($id)
        {
            return $this->container->has($id);
        }
    }
