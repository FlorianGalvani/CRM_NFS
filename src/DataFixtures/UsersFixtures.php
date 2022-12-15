<?php

namespace App\DataFixtures;

use App\Entity\User;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;

class UsersFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        //ROLE_ADMIN with sub_role customer
        $faker = Faker\Factory::create('fr_FR');

        $user = new User();
        $user->setEmail($faker->email);
        $user->setRoles(['ROLE_ADMIN']);

        $password = $this->hasher->hashPassword($user, 'pass_1234');
        $user->setPassword($password);

        $user->setSubRole('customer');

        $manager->persist($user);
        $manager->flush();

        //ROLE_USER with sub_role customer
        $faker = Faker\Factory::create('fr_FR');

        $user = new User();
        $user->setEmail($faker->email);
        $user->setRoles(['ROLE_USER']);

        $password = $this->hasher->hashPassword($user, 'pass_1234');
        $user->setPassword($password);

        $user->setSubRole('customer');

        $manager->persist($user);
        $manager->flush();

        //ROLE_USER with sub_role commercial
        $faker = Faker\Factory::create('fr_FR');

        $user = new User();
        $user->setEmail($faker->email);
        $user->setRoles(['ROLE_USER']);
        
        $password = $this->hasher->hashPassword($user, 'pass_1234');
        $user->setPassword($password);
        
        $user->setSubRole('commercial');
        
        $manager->persist($user);
        $manager->flush();
    }
}
