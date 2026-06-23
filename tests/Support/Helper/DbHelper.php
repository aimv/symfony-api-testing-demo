<?php

namespace App\Tests\Support\Helper;

use Codeception\Exception\ModuleException;
use Codeception\Module;
use Codeception\Module\Symfony;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

class DbHelper extends Module
{
    /**
     * Быстрый доступ к EntityManager через модуль Symfony
     * @throws ModuleException
     */
    private function getEntityManager(): EntityManagerInterface
    {
        /** @var Symfony $symfony */
        $symfony = $this->getModule('Symfony');

        /** @var EntityManagerInterface $em */
        $em = $symfony->_getContainer()->get('doctrine.orm.entity_manager');

        return $em;
    }

    /**
     * Красивый метод для загрузки фикстур одной строкой
     * @throws ModuleException
     */
    public function loadFixtures(string $fixtureClass): void
    {
        $em = $this->getEntityManager();
        $loader = new Loader();
        $loader->addFixture(new $fixtureClass());

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures(), false); // false чтобы не дропать миграции таблиц
    }

    /**
     * Красивый метод для извлечения сущности из репозитория
     * @throws ModuleException
     */
    public function grabEntityFromRepository(string $entityClass, array $criteria): ?object
    {
        $em = $this->getEntityManager();
        return $em->getRepository($entityClass)->findOneBy($criteria);
    }
}
