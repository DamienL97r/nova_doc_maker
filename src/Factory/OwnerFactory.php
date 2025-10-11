<?php

namespace App\Factory;

use App\Entity\Owner;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Owner>
 */
final class OwnerFactory extends PersistentObjectFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    #[\Override]
    public static function class(): string
    {
        return Owner::class;
    }

    #[\Override]
    protected function defaults(): array
    {
        return [
            // Exemple de code APE : 6201Z (programmation informatique)
            'ape'         => self::faker()->regexify('[0-9]{4}[A-Z]'),

            // Société & identité
            'companyName' => self::faker()->company(),
            'firstname'   => self::faker()->firstName(),
            'lastname'    => self::faker()->lastName(),

            // Email professionnel plausible et unique
            'email'       => self::faker()->unique()->companyEmail(),

            // Téléphone international valide
            'phone'       => self::faker()->e164PhoneNumber(),

            // Numéro SIREN (9 chiffres, unique)
            'sirene'      => self::faker()->unique()->numerify('#########'),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Owner $owner): void {})
        ;
    }
}
