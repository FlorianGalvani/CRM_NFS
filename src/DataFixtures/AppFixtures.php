<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder) {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
//        $user = new User();
//
//        $hash = $this->encoder->hashPassword($user, "password");
//
//        $user->setFirstname('Michel');
//        $user->setLastname('Florian');
//        $user->setEmail('michel.florian@dugland.fr');
//        $user->setPhone('0200000000');
//        $user->setRoles(['ROLE_USER']);
//        $user->setAddress('1 rue JsaisPasQuoi, 76000 Rouen');
//        $user->setPassword($hash);
//
//        $manager->persist($user);
//
//        $manager->flush();
    }
}
