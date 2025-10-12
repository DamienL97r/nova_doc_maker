<?php

namespace App\Doctrine;

use App\Entity\Product;
use App\Entity\Quote;
use App\Service\QuoteTotalsCalculator;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\EntityManagerInterface;

#[AsDoctrineListener(event: Events::onFlush)]
final class QuoteTotalsOnFlushListener
{
    public function __construct(private readonly QuoteTotalsCalculator $calculator) {}

    public function onFlush(OnFlushEventArgs $args): void
    {
        /** @var EntityManagerInterface $em */
        $em  = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        // Collecte tous les devis impactés par ce flush
        /** @var array<Quote> $impacted */
        $impacted = [];

        // 1) Si un Quote lui-même est inséré/MAJ, on le (re)calcule
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Quote) {
                $impacted[spl_object_id($entity)] = $entity;
            }
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Quote) {
                $impacted[spl_object_id($entity)] = $entity;
            }
        }

        // 2) Si des Products liés sont insérés/MAJ/supprimés, on (re)calcule le Quote parent
        $iter = function (array $entities) use (&$impacted) {
            foreach ($entities as $e) {
                if ($e instanceof Product && null !== $e->getQuote()) {
                    $impacted[spl_object_id($e->getQuote())] = $e->getQuote();
                }
            }
        };
        $iter($uow->getScheduledEntityInsertions());
        $iter($uow->getScheduledEntityUpdates());
        $iter($uow->getScheduledEntityDeletions());

        if (!$impacted) {
            return;
        }

        // 3) Recalcule chaque devis et recompute son changeset
        foreach ($impacted as $quote) {
            $this->calculator->recalculate($quote);

            $meta = $em->getClassMetadata(Quote::class);
            $uow->recomputeSingleEntityChangeSet($meta, $quote);
        }
    }
}
