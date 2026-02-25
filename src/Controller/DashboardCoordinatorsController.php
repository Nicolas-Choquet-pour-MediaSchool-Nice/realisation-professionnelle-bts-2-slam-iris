<?php

namespace App\Controller;

use App\Entity\Coordinator;
use App\Entity\User;
use App\Form\CoordinatorType;
use App\Repository\CoordinatorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardCoordinatorsController extends AbstractController
{
    #[Route('/dashboard/coordinators', name: 'app_dashboard_coordinators', methods: ['GET'])]
    public function index(CoordinatorRepository $coordinatorRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }
        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        return $this->render('dashboard_coordinators/index.html.twig', [
            'coordinators' => $coordinatorRepository->findAll(),
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser
        ]);
    }

    #[Route('/dashboard/coordinators/new', name: 'app_dashboard_coordinators_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }
        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        $coordinator = new Coordinator();
        $form = $this->createForm(CoordinatorType::class, $coordinator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();
            $user->setEmail($form->get('email')->getData());
            $user->setFirstname($form->get('firstname')->getData());
            $user->setLastname($form->get('lastname')->getData());
            $user->setRoles(['ROLE_COORDINATOR']);
            $user->setPassword($passwordHasher->hashPassword($user, 'password123'));

            $coordinator->setUser($user);
            $user->setCoordinator($coordinator);

            $entityManager->persist($user);
            $entityManager->persist($coordinator);
            $entityManager->flush();

            $this->addFlash('success', 'Coordinateur créé avec succès.');

            return $this->redirectToRoute('app_dashboard_coordinators', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard_coordinators/new.html.twig', [
            'coordinator' => $coordinator,
            'form' => $form,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser
        ]);
    }

    #[Route('/dashboard/coordinators/{id}', name: 'app_dashboard_coordinators_show', methods: ['GET'])]
    public function show(Coordinator $coordinator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }
        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        return $this->render('dashboard_coordinators/show.html.twig', [
            'coordinator' => $coordinator,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser
        ]);
    }

    #[Route('/dashboard/coordinators/{id}/edit', name: 'app_dashboard_coordinators_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Coordinator $coordinator, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }
        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        $form = $this->createForm(CoordinatorType::class, $coordinator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $coordinator->getUser();
            $user->setEmail($form->get('email')->getData());
            $user->setFirstname($form->get('firstname')->getData());
            $user->setLastname($form->get('lastname')->getData());

            $entityManager->flush();

            $this->addFlash('success', 'Coordinateur mis à jour avec succès.');

            return $this->redirectToRoute('app_dashboard_coordinators', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard_coordinators/edit.html.twig', [
            'coordinator' => $coordinator,
            'form' => $form,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser
        ]);
    }

    #[Route('/dashboard/coordinators/{id}', name: 'app_dashboard_coordinators_delete', methods: ['POST'])]
    public function delete(Request $request, Coordinator $coordinator, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$coordinator->getId(), $request->request->get('_token'))) {
            $user = $coordinator->getUser();
            $entityManager->remove($coordinator);
            if ($user) {
                $entityManager->remove($user);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Coordinateur supprimé avec succès.');
        }

        return $this->redirectToRoute('app_dashboard_coordinators', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/dashboard/coordinators/{id}/reset-password', name: 'app_dashboard_coordinators_reset_password', methods: ['POST'])]
    public function resetPassword(Coordinator $coordinator, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('reset-password' . $coordinator->getId(), $request->request->get('_token'))) {
            $user = $coordinator->getUser();
            $newPassword = bin2hex(random_bytes(4)); // 8 caractères
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $entityManager->flush();

            $this->addFlash('success', sprintf('Le mot de passe de %s %s a été réinitialisé : %s', $user->getFirstname(), $user->getLastname(), $newPassword));
        }

        return $this->redirectToRoute('app_dashboard_coordinators');
    }
}
