<?php

namespace App\DataFixtures;

use App\Entity\SchoolClass;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClassesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $className = 'BTS SIO 2 IRIS SLAM';

        if (!$manager->getRepository(SchoolClass::class)
            ->findOneBy(['name' => $className])) {
            $class = new SchoolClass();
            $class->setName($className);
            $manager->persist($class);
            $manager->flush();
        }
    }
}
