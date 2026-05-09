<?php

namespace App\DataFixtures;

use App\Entity\Tache;
use App\Entity\User;
use App\Entity\Projet;
use App\Entity\Etiquette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TacheFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $labels = ['Bug','Feature','Urgent','Documentation','Amélioration','Design'];

        for ($i = 0; $i < 40; $i++) {

            $t = new Tache();
            $t->setTitre($faker->sentence(4));
            $t->setStatut($faker->randomElement(['a_faire','en_cours','terminee']));
            $t->setPriorite($faker->randomElement(['basse','moyenne','haute','urgente']));
            // dateCreation est déjà définie dans le constructeur de l'entité, donc pas besoin de setter

            // ✅ Utilisation des références AVEC le nom de la classe
            $t->setProjet($this->getReference('projet'.rand(0,7), Projet::class));
            $t->setAssigneA($this->getReference('chef', User::class));

            for ($j = 0; $j < rand(1,3); $j++) {
                $t->addEtiquette(
                    $this->getReference($labels[array_rand($labels)], Etiquette::class)
                );
            }

            $manager->persist($t);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProjetFixtures::class,
            EtiquetteFixtures::class
        ];
    }
}