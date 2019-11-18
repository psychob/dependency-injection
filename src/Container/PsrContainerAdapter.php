<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Container;

    use Exception;
    use Psr\Container\ContainerExceptionInterface;
    use Psr\Container\ContainerInterface as PsrContainerInterface;
    use PsychoB\DependencyInjection\Container\Exceptions\PsrErrorRetrievalException;

    /**
     * Adapter used when compatibility with Psr4/ContainerInterface is required.
     *
     * @author Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
     * @since  0.8
     */
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
            try {
                return $this->container->get($id);
            } catch (ContainerExceptionInterface $t) {
                throw $t;
            } catch (Exception $t) {
                // We don't want to intercept Throwable or Error, because we could hide error in implementation, and
                // that would be bad.
                throw new PsrErrorRetrievalException($id, $t);
            }
        }

        /**
         * @inheritDoc
         */
        public function has($id)
        {
            try {
                return $this->container->has($id);
            } catch (Exception $e) {
                // We don't want to intercept Throwable or Error, because we could hide error in implementation, and
                // that would be bad.
                return false;
            }
        }
    }
