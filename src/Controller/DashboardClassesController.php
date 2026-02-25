<?php

namespace App\Controller;

use App\Entity\SchoolClass;
use App\Entity\User;
use App\Form\AddStudentToSchoolClassType;
use App\Form\SchoolClassType;
use App\Repository\SchoolClassRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardClassesController extends AbstractController
{
    #[Route('/dashboard/classes', name: 'app_dashboard_classes')]
    public function index(SchoolClassRepository $schoolClassRepository): Response
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

        $params = [
            'controller_name' => 'DashboardClassesController',
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser,
            'school_classes' => $schoolClassRepository->findAll()
        ];

        if ($isCoordinator) {
            $params['school_classes'] = $schoolClassRepository
                ->findByCoordinator($connectedUser->getId());
        }

        return $this->render('dashboard_classes/index.html.twig', $params);
    }

    #[Route('/dashboard/class/new', name: 'app_dashboard_class_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
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

        $schoolClass = new SchoolClass();
        $form = $this->createForm(SchoolClassType::class, $schoolClass);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($schoolClass);
            $entityManager->flush();

            return $this->redirectToRoute('app_dashboard_classes', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard_classes/new.html.twig', [
            'school_class' => $schoolClass,
            'form' => $form,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser,
        ]);
    }

    #[Route('/dashboard/class/{id}', name: 'app_dashboard_class_show', methods: ['GET'])]
    public function show(SchoolClass $schoolClass): Response
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

        return $this->render('dashboard_classes/show.html.twig', [
            'school_class' => $schoolClass,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser,
        ]);
    }

    #[Route('/dashboard/class/{id}/edit', name: 'app_dashboard_class_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SchoolClass $schoolClass, EntityManagerInterface $entityManager): Response
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

        $form = $this->createForm(SchoolClassType::class, $schoolClass);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Classe modifiée avec succès.');

            return $this->redirectToRoute('app_dashboard_classes', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard_classes/edit.html.twig', [
            'school_class' => $schoolClass,
            'form' => $form,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser,
        ]);
    }

    #[Route('/dashboard/class/{id}', name: 'app_dashboard_class_delete', methods: ['POST'])]
    public function delete(Request $request, SchoolClass $schoolClass, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$schoolClass->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($schoolClass);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_dashboard_classes', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/dashboard/class/{id}/add_student', name: 'app_dashboard_class_add_student', methods: ['GET', 'POST'])]
    public function add_students(SchoolClass $schoolClass, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AddStudentToSchoolClassType::class, $schoolClass);
        $form->handleRequest($request);

        /* @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }
        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        if ($form->isSubmitted() && $form->isValid()) {
            $students = $form->get('students')->getData();
            foreach ($students as $student) {
                $student->setClass($schoolClass);
                $entityManager->persist($student);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Étudiants ajoutés avec succès.');

            return $this->redirectToRoute('app_dashboard_class_show', ['id' => $schoolClass->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard_classes/add_student.html.twig', [
            'school_class' => $schoolClass,
            'form' => $form,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser,
        ]);
    }

    #[Route('/dashboard/class/{id}/remove_student/{student_id}', name: 'app_dashboard_class_remove_student', methods: ['POST'])]
    public function removeStudent(Request $request, SchoolClass $schoolClass, int $student_id, EntityManagerInterface $entityManager): Response
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

        if ($this->isCsrfTokenValid('remove_student'.$student_id, $request->getPayload()->getString('_token'))) {
            $student = $entityManager->getRepository(\App\Entity\Student::class)->find($student_id);
            if ($student && $student->getClass() === $schoolClass) {
                $student->setClass(null);
                $entityManager->flush();
                $this->addFlash('success', 'Étudiant retiré de la classe avec succès.');
            }
        }

        return $this->redirectToRoute('app_dashboard_class_show', ['id' => $schoolClass->getId()], Response::HTTP_SEE_OTHER);
    }
}
