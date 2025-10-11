<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Story\DemoStory;

final class StoryFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Exécute la Story Foundry
        DemoStory::load();
    }
}
