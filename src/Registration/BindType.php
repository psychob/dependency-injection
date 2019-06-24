<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection\Registration;

    final class BindType
    {
        public const BIND_TYPE_NONE = 'none';
        public const BIND_TYPE_FUNCTION = 'function';
        public const BIND_TYPE_CLASS = 'class';
        public const BIND_TYPE_LITERAL = 'literal';
        public const BIND_TYPE_FACTORY = 'factory';
    }
