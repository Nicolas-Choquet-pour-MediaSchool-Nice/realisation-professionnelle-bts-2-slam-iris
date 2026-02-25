<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Coordinator;
use App\Entity\Student;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private UserRepository $userRepository
    )
    {}

    public function load(ObjectManager $manager): void
    {
        if (!$this->userRepository->findOneBy([
            'email' => 'student@example.com'
        ])) {
            // Création d'un étudiant
            $user = new User();
            $user->setEmail('student@example.com')
                ->setFirstname('Student')
                ->setLastname('1')
                ->setRoles(['ROLE_USER']);
            $user->setPassword($this->hasher->hashPassword($user, 'password123'));
            $manager->persist($user);

            $student = new Student()->setUser($user);
            $manager->persist($student);

            $user->setStudent($student);
            $manager->persist($user);
        }

        if (!$this->userRepository->findOneBy([
            'email' => 'admin@example.com'
        ])) {
            // Création d'un administrateur
            $user = new User();
            $user->setEmail('admin@example.com')
                ->setRoles(['ROLE_ADMIN'])
                ->setFirstname('Admin')
                ->setLastname('1');
            $user->setPassword($this->hasher->hashPassword($user, 'password123'));
            $manager->persist($user);

            $admin = new Admin()->setUser($user);
            $manager->persist($admin);

            $user->setAdmin($admin);
            $manager->persist($user);
        }

        if (!$this->userRepository->findOneBy([
            'email' => 'coordinator@example.com'
        ])) {
            // Création d'un corrdinateur
            $user = new User();
            $user->setEmail('coordinator@example.com')
                ->setRoles(['ROLE_COORDINATOR'])
                ->setFirstname('Coordinator')
                ->setLastname('1');
            $user->setPassword($this->hasher->hashPassword($user, 'password123'));
            $manager->persist($user);

            $coordinator = new Coordinator()->setUser($user);
            $manager->persist($coordinator);

            $user->setCoordinator($coordinator);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
