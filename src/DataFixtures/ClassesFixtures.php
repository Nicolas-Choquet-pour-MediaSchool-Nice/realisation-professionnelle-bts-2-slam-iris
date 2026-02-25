<?php

namespace App\DataFixtures;

use App\Entity\SchoolClass;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClassesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $class = new SchoolClass();
        $class->setName('BTS SIO 2 IRIS SLAM');
        $manager->persist($class);

        $manager->flush();
    }
}
