<?php

namespace App\Factory;

use App\Entity\Customer;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @internal
 * @extends PersistentObjectFactory<Customer>
 */
final class CustomerFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Customer::class;
    }

    protected function defaults(): array
    {
        return [
            // APE: 4 chiffres + 1 lettre (ex: 6201Z)
            'ape'         => self::faker()->regexify('[0-9]{4}[A-Z]'),

            'companyName' => self::faker()->company(),
            'email'       => self::faker()->unique()->companyEmail(),
            'phone'       => self::faker()->e164PhoneNumber(),

            // SIREN: 9 chiffres, unique
            'sirene'      => self::faker()->unique()->numerify('#########'),

            // TVA (plausible)
            'vatNumber'   => 'FR'
                . self::faker()->numerify('##')
                . ' '
                . self::faker()->numerify('#########'),
        ];
    }
}
