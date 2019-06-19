<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection;

    final class Arguments
    {
        public static function anyArgument()
        {
            static $any = null;

            if ($any === null) {
                $any = function ( ) { };
            }

            return $any;
        }

        public static function isAny($arg): bool
        {
            return $arg === static::anyArgument();
        }
    }
