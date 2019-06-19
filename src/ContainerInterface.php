<?php
    //
    // dependency-injection
    // (c) 2019 RGB Lighthouse <https://rgblighthouse.pl>
    // (c) 2019 Andrzej Budzanowski <kontakt@andrzej.budzanowski.pl>
    //

    namespace PsychoB\DependencyInjection;

    use PsychoB\DependencyInjection\Registration\RegistrationBuilder;
    use PsychoB\DependencyInjection\Registration\RegistrationEntry;

    interface ContainerInterface
    {
        public function has(string $class): bool;

        public function get(string $class);

        public function add(string $class, $object);

        public function build(string $class): RegistrationBuilder;

        public function register(RegistrationBuilder $register);

        public function getEntry(string $class): ?RegistrationEntry;

        public function make(string $class, array $arguments = []);
    }
