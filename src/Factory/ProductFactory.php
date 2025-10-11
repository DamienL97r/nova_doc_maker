<?php

namespace App\Factory;

use App\Entity\Product;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Product>
 */
final class ProductFactory extends PersistentObjectFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function class(): string
    {
        return Product::class;
    }

    protected function defaults(): array
    {
        // Choix de taux FR plausibles : 0%, 5.5%, 10%, 20%
        $taxRate = self::faker()->randomElement([0.00, 0.055, 0.10, 0.20]);

        // Valeurs de base
        $quantity  = self::faker()->randomFloat(2, 1, 20);      // 1 à 20 unités
        $unitPrice = self::faker()->randomFloat(2, 10, 1500);   // 10€ à 1500€ HT

        // Calculs HT/TVA/TTC
        $totalHT   = round($quantity * $unitPrice, 2);
        $tax       = round($totalHT * $taxRate, 2);
        $totalTTC  = round($totalHT + $tax, 2);

        return [
            'quote'     => QuoteFactory::new(),                 // écrasé au besoin dans ta Story
            'title'     => self::faker()->sentence(4),
            'quantity'  => $quantity,
            'unitPrice' => $unitPrice,                          // HT/unité
            'taxRate'   => $taxRate,                            // ex: 0.20 pour 20%
            'total'     => $totalTTC,                           // total TTC (cohérent avec les champs ci-dessus)
        ];
    }

    protected function initialize(): static
    {
        return $this->afterInstantiate(function (Product $product): void {
            // Sécurise les bornes
            $q   = max(0.01, (float) $product->getQuantity());
            $pu  = max(0.00, (float) $product->getUnitPrice());
            $tva = max(0.0, min(1.0, (float) $product->getTaxRate())); // 0% → 100%

            $totalHT  = round($q * $pu, 2);
            $tax      = round($totalHT * $tva, 2);
            $totalTTC = round($totalHT + $tax, 2);

            $product->setQuantity($q);
            $product->setUnitPrice($pu);
            $product->setTaxRate($tva);
            $product->setTotal($totalTTC);

            // Optionnel : tronque le titre à 255 si nécessaire
            $title = (string) $product->getTitle();
            if (mb_strlen($title) > 255) {
                $product->setTitle(mb_substr($title, 0, 252) . '...');
            }
        });
    }
}
