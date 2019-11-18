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
     * This exception is thrown when PsrContainerAdapter inner container fail to retrieve class.
     *
     * @author Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
     * @since  0.8
     */
    class PsrErrorRetrievalException extends \RuntimeException implements NotFoundExceptionInterface
    {
        protected $id;

        /**
         * PsrClassNotFoundException constructor.
         *
         * @param mixed          $id
         * @param Throwable|null $t
         */
        public function __construct($id, Throwable $t = NULL)
        {
            $this->id = $id;

            parent::__construct('Failed to retrieve Element: ' . strval($id), 0, $t);
        }

        /**
         * @return mixed
         */
        public function getId()
        {
            return $this->id;
        }
    }
