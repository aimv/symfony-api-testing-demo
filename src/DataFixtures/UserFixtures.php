<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const string USER_ADMIN_REFERENCE = 'user-admin';

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('admin@example.com');

        $manager->persist($user);
        $manager->flush();

        // Сохраняем ссылку на этот объект, чтобы использовать в других фикстурах
        $this->addReference(self::USER_ADMIN_REFERENCE, $user);
    }
}
