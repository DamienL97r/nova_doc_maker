<?php

namespace App\Doctrine;

use App\Entity\Quote;
use App\Service\QuoteTotalsCalculator;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface; // ← important

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final class QuoteTotalsSubscriber
{
    public function __construct(private readonly QuoteTotalsCalculator $calculator) {}

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Quote) {
            return;
        }

        $this->calculator->recalculate($entity);

        // En prePersist pas besoin de recompute; Doctrine calculera le changeset initial.
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Quote) {
            return;
        }

        $this->calculator->recalculate($entity);

        // Recompute le changeset via l'EntityManager ORM
        $om = $args->getObjectManager();
        if ($om instanceof EntityManagerInterface) {
            $meta = $om->getClassMetadata(Quote::class);
            $om->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $entity);
        }
        // Sinon (autre persistance), on ne fait rien.
    }
}
