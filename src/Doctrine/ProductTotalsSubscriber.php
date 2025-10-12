<?php

namespace App\Doctrine;

use App\Entity\Product;
use App\Entity\Quote;
use App\Service\QuoteTotalsCalculator;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final class ProductTotalsSubscriber
{
    public function __construct(
        private readonly QuoteTotalsCalculator $quoteCalc, // ← injection
    ) {}

    public function prePersist(LifecycleEventArgs $args): void
    {
        $e = $args->getObject();
        if (!$e instanceof Product) return;

        $this->compute($e);

        // Optionnel: recalcule le devis parent aussi lors de la création de la ligne
        if ($quote = $e->getQuote()) {
            $this->quoteCalc->recalculate($quote);
            // pas besoin de recompute ici (prePersist)
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $e = $args->getObject();
        if (!$e instanceof Product) return;

        $this->compute($e);

        $om = $args->getObjectManager();

        // Recompute changeset du Product
        if ($om instanceof EntityManagerInterface) {
            $metaP = $om->getClassMetadata(Product::class);
            $om->getUnitOfWork()->recomputeSingleEntityChangeSet($metaP, $e);
        }

        // 👉 Recalcule le devis parent puisque la ligne a changé
        if (($quote = $e->getQuote()) && $om instanceof EntityManagerInterface) {
            $this->quoteCalc->recalculate($quote);

            // Très important : recomputer le changeset du devis
            $metaQ = $om->getClassMetadata(Quote::class);
            $om->getUnitOfWork()->recomputeSingleEntityChangeSet($metaQ, $quote);
        }
    }

    private function compute(Product $p): void
    {
        $qRaw   = $p->getQuantity();
        $puRaw  = $p->getUnitPrice();
        $tvaRaw = $p->getTaxRate();

        $q   = is_numeric($qRaw)   ? (float)$qRaw   : 0.0;
        $pu  = is_numeric($puRaw)  ? (float)$puRaw  : 0.0;
        $tva = is_numeric($tvaRaw) ? (float)$tvaRaw : 0.0;

        if (!is_finite($q))  $q  = 0.0;
        if (!is_finite($pu)) $pu = 0.0;
        if (!is_finite($tva)) $tva = 0.0;
        $tva = max(0.0, min(1.0, $tva));

        $ht  = round($q * $pu, 2);
        $tax = round($ht * $tva, 2);
        $ttc = round($ht + $tax, 2);

        $p->setTotal(number_format($ttc, 2, '.', ''));
    }
}
