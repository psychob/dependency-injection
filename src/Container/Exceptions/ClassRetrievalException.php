<?php
    //
    // psychob/dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Container\Exceptions;

    use Psr\Container\ContainerExceptionInterface;

    /**
     * Container Implementation
     *
     * @author Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
     * @since  0.8
     */
    class ClassRetrievalException extends \RuntimeException implements ContainerExceptionInterface
    {

    }
