<?php

namespace App\DataFixtures;

use App\Entity\Projet;
use App\Entity\User;
use App\Entity\Etiquette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProjetFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // ✅ SAFE reference (IMPORTANT FIX)
        $chef = $this->getReference('chef', User::class);

        for ($i = 0; $i < 8; $i++) {

            $p = new Projet();
            $p->setNom($faker->sentence(3));
            $p->setDescription($faker->paragraph());
            $p->setDateCreation(new \DateTimeImmutable());
            $p->setDateLimite($faker->dateTimeBetween('+1 week', '+2 months'));
            $p->setStatut($faker->randomElement([
                'planifie',
                'en_cours',
                'termine',
                'annule'
            ]));

            $p->setCreateur($chef);

            $manager->persist($p);

            $this->addReference('projet'.$i, $p);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}