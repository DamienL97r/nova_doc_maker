<?php

namespace App\Factory;

use App\Entity\Quote;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Quote>
 */
final class QuoteFactory extends PersistentObjectFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function class(): string
    {
        return Quote::class;
    }

    protected function defaults(): array
    {
        // Dates plausibles
        $issue = \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-3 months', 'now'));
        $valid = $issue->modify('+30 days');

        // Montants cohérents
        $subTotal = self::faker()->randomFloat(2, 100, 5000); // HT
        $vatRate  = 0.20;                                     // 20% TVA
        $taxTotal = round($subTotal * $vatRate, 2);
        $total    = round($subTotal + $taxTotal, 2);

        // Statuts plausibles (≤ 20 chars)
        $status = self::faker()->randomElement(['draft', 'sent', 'accepted', 'rejected', 'expired']);

        return [
            // Associations: par défaut crée de nouveaux, mais seront écrasées dans la Story
            'customer'   => CustomerFactory::new(),
            'owner'      => OwnerFactory::new(),

            'title'      => self::faker()->sentence(6),
            'status'     => $status,

            'issueDate'  => $issue,
            'validUntil' => $valid,

            // Numéro unique; on finalise le format en initialize()
            'number'     => 'Q-' . self::faker()->unique()->numerify('########'),

            'subTotal'   => $subTotal,
            'taxTotal'   => $taxTotal,
            'total'      => $total,
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Quote $quote): void {
                // Harmonise le numéro: Q-YYYY-NNNN (année = issueDate)
                $issue = $quote->getIssueDate() ?? new \DateTimeImmutable();
                $year  = $issue->format('Y');

                // Garde une séquence unique mais lisible
                $sequence = self::faker()->unique()->numerify('####');
                $quote->setNumber(sprintf('Q-%s-%s', $year, $sequence));

                // Recalcule total pour éviter toute dérive d'arrondi
                $sub = (float) $quote->getSubTotal();
                $tax = (float) round($sub * 0.20, 2);
                $quote->setTaxTotal($tax);
                $quote->setTotal(round($sub + $tax, 2));

                // S'assure que validUntil > issueDate
                if ($quote->getValidUntil() <= $issue) {
                    $quote->setValidUntil($issue->modify('+30 days'));
                }
            });
    }
}
