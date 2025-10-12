<?php

namespace App\Service;

use App\Entity\Quote;
use App\Entity\Product;

final class QuoteTotalsCalculator
{
    /**
     * Calcule/alimente subTotal (HT), taxTotal (TVA), total (TTC)
     * + met à jour le total TTC de chaque ligne Product si présent.
     */
    public function recalculate(Quote $quote): void
    {
        $subTotal = 0.0; // HT
        $taxTotal = 0.0; // TVA
        $total    = 0.0; // TTC

        foreach ($quote->getItems() as $item) {
            if (!$item instanceof Product) {
                continue;
            }

            $q   = max(0.0, (float) $item->getQuantity());
            $pu  = max(0.0, (float) $item->getUnitPrice()); // HT/unité
            $tva = max(0.0, min(1.0, (float) $item->getTaxRate())); // 0–1

            $lineHT  = round($q * $pu, 2);
            $lineTVA = round($lineHT * $tva, 2);
            $lineTTC = round($lineHT + $lineTVA, 2);

            $subTotal += $lineHT;
            $taxTotal += $lineTVA;
            $total    += $lineTTC;
        }

        // Quote stocke des DECIMAL en string → on fige le format à 2 décimales
        $quote->setSubTotal(number_format($subTotal, 2, '.', ''));
        $quote->setTaxTotal(number_format($taxTotal, 2, '.', ''));
        $quote->setTotal(number_format($total, 2, '.', ''));
    }
}
