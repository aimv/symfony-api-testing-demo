<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

// Наследуем DependentFixtureInterface для управления порядком загрузки
class CompanyFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $company = new Company();
        $company->setName('Middle Tech LLC');
        $company->setOwner(
            $this->getReference(UserFixtures::USER_ADMIN_REFERENCE, User::class)
        );
        $manager->persist($company);

        $company2 = new Company();
        $company2->setName('Test New Company');
        $company2->setOwner(
            $this->getReference(UserFixtures::USER_ADMIN_REFERENCE, User::class)
        );
        $manager->persist($company2);

        $manager->flush();
    }

    // Этот метод указывает Doctrine, какие фикстуры ДОЛЖНЫ быть загружены ДО текущей
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
