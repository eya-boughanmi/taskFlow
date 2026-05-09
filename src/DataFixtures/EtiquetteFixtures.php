<?php

namespace App\DataFixtures;

use App\Entity\Etiquette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtiquetteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = [
            ['Bug', '#e74c3c'],
            ['Feature', '#3498db'],
            ['Urgent', '#e67e22'],
            ['Documentation', '#2ecc71'],
            ['Amélioration', '#9b59b6'],
            ['Design', '#f1c40f'],
        ];

        foreach ($data as [$name, $color]) {
            $e = new Etiquette();
            $e->setNom($name);
            $e->setCouleur($color);

            $manager->persist($e);

            $this->addReference($name, $e);
        }

        $manager->flush();
    }
}