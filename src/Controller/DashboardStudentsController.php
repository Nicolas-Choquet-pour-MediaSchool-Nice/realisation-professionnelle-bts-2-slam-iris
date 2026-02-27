<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\User;
use App\Form\StudentType;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardStudentsController extends AbstractController
{
    #[Route('/dashboard/students', name: 'app_dashboard_students', methods: ['GET'])]
    public function index(StudentRepository $studentRepository): Response
    {
        /* @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }
        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        if (!$isCoordinator && !$isAdmin) {
            $this->addFlash('error', "Vous n'avez pas le bon rôle");
            return $this->redirectToRoute('app_index');
        }

        /* @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }
        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        $students = [];
        if ($isAdmin) {
            $students = $studentRepository->findAll();
        } elseif ($isCoordinator) {
            $students = $studentRepository->findByCoordinator($connectedUser->getId());
        }

        return $this->render('dashboard_students/index.html.twig', [
            'students' => $students,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser,
        ]);
    }

    #[Route('/dashboard/students/new', name: 'app_dashboard_students_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /* @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }

        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        if (!$isAdmin && !$isCoordinator) {
            $this->addFlash('error', "Vous n'avez pas le bon rôle");
            return $this->redirectToRoute('app_index');
        }

        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->render('dashboard_students/new.html.twig', [
                    'student' => $student,
                    'form' => $form,
                    'isAdmin' => $isAdmin,
                    'isCoordinator' => $isCoordinator,
                    'user' => $connectedUser,
                ]);
            }

            $user = new User();
            $user->setFirstname($form->get('firstname')->getData());
            $user->setLastname($form->get('lastname')->getData());
            $user->setEmail($form->get('email')->getData());
            $user->setRoles(['ROLE_USER']);
            // Mot de passe par défaut pour les nouveaux étudiants
            $user->setPassword($passwordHasher->hashPassword($user, 'password123'));

            $student->setUser($user);

            $entityManager->persist($user);
            $entityManager->persist($student);
            $entityManager->flush();

            $this->addFlash('success', 'Étudiant créé avec succès.');

            return $this->redirectToRoute('app_dashboard_students', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard_students/new.html.twig', [
            'student' => $student,
            'form' => $form,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser,
        ]);
    }

    #[Route('/dashboard/students/{id}', name: 'app_dashboard_students_show', methods: ['GET'])]
    public function show(Student $student): Response
    {
        /* @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }

        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        if (!$isAdmin && !$isCoordinator) {
            $this->addFlash('error', "Vous n'avez pas le bon rôle");
            return $this->redirectToRoute('app_index');
        }

        return $this->render('dashboard_students/show.html.twig', [
            'student' => $student,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser,
        ]);
    }

    #[Route('/dashboard/students/{id}/edit', name: 'app_dashboard_students_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Student $student, EntityManagerInterface $entityManager): Response
    {
        /* @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }

        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        if (!$isAdmin && !$isCoordinator) {
            $this->addFlash('error', "Vous n'avez pas le bon rôle");
            return $this->redirectToRoute('app_index');
        }

        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $student->getUser();
            $email = $form->get('email')->getData();

            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->render('dashboard_students/edit.html.twig', [
                    'student' => $student,
                    'form' => $form,
                    'isAdmin' => $isAdmin,
                    'isCoordinator' => $isCoordinator,
                    'user' => $connectedUser,
                ]);
            }

            $user->setFirstname($form->get('firstname')->getData());
            $user->setLastname($form->get('lastname')->getData());
            $user->setEmail($form->get('email')->getData());

            $entityManager->flush();

            $this->addFlash('success', 'Étudiant modifié avec succès.');

            return $this->redirectToRoute('app_dashboard_students', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard_students/edit.html.twig', [
            'student' => $student,
            'form' => $form,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser,
        ]);
    }

    #[Route('/dashboard/students/{id}', name: 'app_dashboard_students_delete', methods: ['POST'])]
    public function delete(Request $request, Student $student, EntityManagerInterface $entityManager): Response
    {
        /* @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }

        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        if (!$isAdmin && !$isCoordinator) {
            $this->addFlash('error', "Vous n'avez pas le bon rôle");
            return $this->redirectToRoute('app_index');
        }

        if ($this->isCsrfTokenValid('delete'.$student->getId(), $request->getPayload()->getString('_token'))) {
            $user = $student->getUser();
            $entityManager->remove($student);
            if ($user) {
                $entityManager->remove($user);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Étudiant supprimé avec succès.');
        }

        return $this->redirectToRoute('app_dashboard_students', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/dashboard/students/{id}/reset-password', name: 'app_dashboard_students_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request, Student $student, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /* @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }

        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        if (!$isAdmin && !$isCoordinator) {
            $this->addFlash('error', "Vous n'avez pas le bon rôle");
            return $this->redirectToRoute('app_index');
        }

        if ($this->isCsrfTokenValid('reset-password'.$student->getId(), $request->getPayload()->getString('_token'))) {
            $user = $student->getUser();
            if ($user) {
                $newPassword = bin2hex(random_bytes(4)); // 8 chars random password
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();

                $this->addFlash('success', sprintf('Le mot de passe de %s %s a été réinitialisé : %s', $user->getFirstname(), $user->getLastname(), $newPassword));
            }
        }

        return $this->redirectToRoute('app_dashboard_students', [], Response::HTTP_SEE_OTHER);
    }
}
