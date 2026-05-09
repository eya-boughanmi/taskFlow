<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Projet;
use App\Entity\Etiquette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // ADMIN
        $admin = new User();
        $admin->setEmail('admin@taskflow.com');
        $admin->setPseudo('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));

        $manager->persist($admin);
        $this->addReference('admin', $admin);

        // CHEF
        $chef = new User();
        $chef->setEmail('chef@taskflow.com');
        $chef->setPseudo('chef');
        $chef->setRoles(['ROLE_CHEF_PROJET']);
        $chef->setPassword($this->hasher->hashPassword($chef, 'chef123'));

        $manager->persist($chef);
        $this->addReference('chef', $chef);

        // USERS
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($faker->email());
            $user->setPseudo($faker->userName());
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->hasher->hashPassword($user, 'user123'));

            $manager->persist($user);
        }

        $manager->flush();
    }
}