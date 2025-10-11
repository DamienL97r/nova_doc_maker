<?php

namespace App\Story;

use App\Factory\CustomerFactory;
use App\Factory\OwnerFactory;
use App\Factory\QuoteFactory;
use App\Factory\ProductFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'demo')]
final class DemoStory extends Story
{
    public function build(): void
    {
        // Récupère le Faker "Foundry-aware"
        $faker = \Zenstruck\Foundry\faker();
        // Reset du registre unique (utile si tu relances souvent)
        $faker->unique(true);

        // 5 clients et 5 owners
        $customers = CustomerFactory::createMany(5); // -> array<App\Entity\Customer>
        $owners    = OwnerFactory::createMany(5);    // -> array<App\Entity\Owner>

        // 5 devis, appariés 1–1
        foreach (range(0, 4) as $i) {
            $quote = QuoteFactory::createOne([
                'customer' => $customers[$i],
                'owner'    => $owners[$i],
            ]); // -> App\Entity\Quote

            // 1 à 10 produits rattachés à ce devis
            $count = $faker->numberBetween(1, 10);

            $products = ProductFactory::createMany($count, [
                'quote' => $quote,
            ]); // -> array<App\Entity\Product>

            // Recalcule des totaux à partir des produits
            $subTotal = 0.0; // HT
            $taxTotal = 0.0; // TVA
            $total    = 0.0; // TTC

            foreach ($products as $p) {
                $q   = (float) $p->getQuantity();
                $pu  = (float) $p->getUnitPrice();   // HT / unité
                $tva = (float) $p->getTaxRate();     // ex: 0.20

                $lineHT  = round($q * $pu, 2);
                $lineTVA = round($lineHT * $tva, 2);
                $lineTTC = round($lineHT + $lineTVA, 2);

                $subTotal += $lineHT;
                $taxTotal += $lineTVA;
                $total    += $lineTTC;
            }

            // Applique les totaux au devis
            $quote->setSubTotal(round($subTotal, 2));
            $quote->setTaxTotal(round($taxTotal, 2));
            $quote->setTotal(round($total, 2));
            // Pas besoin de persist/flush explicite: Foundry/Doctrine gèrent déjà
        }
    }
}
