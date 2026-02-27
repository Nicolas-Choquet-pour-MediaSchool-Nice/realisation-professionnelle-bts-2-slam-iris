<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\User;
use App\Form\AdminType;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardAdminsController extends AbstractController
{
    #[Route('/dashboard/admins', name: 'app_dashboard_admins', methods: ['GET'])]
    public function index(AdminRepository $adminRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }
        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        return $this->render('dashboard_admins/index.html.twig', [
            'admins' => $adminRepository->findAll(),
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser
        ]);
    }

    #[Route('/dashboard/admins/new', name: 'app_dashboard_admins_new', methods: ['GET', 'POST'])]
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

        $admin = new Admin();
        $form = $this->createForm(AdminType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->render('dashboard_admins/new.html.twig', [
                    'admin' => $admin,
                    'form' => $form,
                    'isAdmin' => $isAdmin,
                    'isCoordinator' => $isCoordinator,
                    'user' => $connectedUser
                ]);
            }

            $user = new User();
            $user->setEmail($form->get('email')->getData());
            $user->setFirstname($form->get('firstname')->getData());
            $user->setLastname($form->get('lastname')->getData());
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword($passwordHasher->hashPassword($user, 'password123'));

            $admin->setUser($user);
            $user->setAdmin($admin);

            $entityManager->persist($user);
            $entityManager->persist($admin);
            $entityManager->flush();

            $this->addFlash('success', 'Administrateur créé avec succès.');

            return $this->redirectToRoute('app_dashboard_admins', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard_admins/new.html.twig', [
            'admin' => $admin,
            'form' => $form,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser
        ]);
    }

    #[Route('/dashboard/admins/{id}', name: 'app_dashboard_admins_show', methods: ['GET'])]
    public function show(Admin $admin): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }
        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        return $this->render('dashboard_admins/show.html.twig', [
            'admin' => $admin,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser
        ]);
    }

    #[Route('/dashboard/admins/{id}/edit', name: 'app_dashboard_admins_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Admin $admin, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }

        if ($admin->getUser()->getId() === $connectedUser->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier votre propre profil administrateur ici.');
            return $this->redirectToRoute('app_dashboard_admins');
        }

        $isCoordinator = in_array('ROLE_COORDINATOR', $connectedUser->getRoles());
        $isAdmin = in_array('ROLE_ADMIN', $connectedUser->getRoles());

        $form = $this->createForm(AdminType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $admin->getUser();
            $email = $form->get('email')->getData();

            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->render('dashboard_admins/edit.html.twig', [
                    'admin' => $admin,
                    'form' => $form,
                    'isAdmin' => $isAdmin,
                    'isCoordinator' => $isCoordinator,
                    'user' => $connectedUser
                ]);
            }

            $user->setEmail($form->get('email')->getData());
            $user->setFirstname($form->get('firstname')->getData());
            $user->setLastname($form->get('lastname')->getData());

            $entityManager->flush();

            $this->addFlash('success', 'Administrateur mis à jour avec succès.');

            return $this->redirectToRoute('app_dashboard_admins', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard_admins/edit.html.twig', [
            'admin' => $admin,
            'form' => $form,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser
        ]);
    }

    #[Route('/dashboard/admins/{id}', name: 'app_dashboard_admins_delete', methods: ['POST'])]
    public function delete(Request $request, Admin $admin, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var User $connectedUser */
        $connectedUser = $this->getUser();

        if ($admin->getUser()->getId() === $connectedUser->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas vous supprimer vous-même.');
            return $this->redirectToRoute('app_dashboard_admins');
        }

        if ($this->isCsrfTokenValid('delete'.$admin->getId(), $request->request->get('_token'))) {
            $user = $admin->getUser();
            $entityManager->remove($admin);
            if ($user) {
                $entityManager->remove($user);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Administrateur supprimé avec succès.');
        }

        return $this->redirectToRoute('app_dashboard_admins', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/dashboard/admins/{id}/reset-password', name: 'app_dashboard_admins_reset_password', methods: ['POST'])]
    public function resetPassword(Admin $admin, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var User $connectedUser */
        $connectedUser = $this->getUser();

        if ($admin->getUser()->getId() === $connectedUser->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas réinitialiser votre propre mot de passe ici.');
            return $this->redirectToRoute('app_dashboard_admins');
        }

        if ($this->isCsrfTokenValid('reset-password' . $admin->getId(), $request->request->get('_token'))) {
            $user = $admin->getUser();
            $newPassword = bin2hex(random_bytes(4)); // 8 caractères
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $entityManager->flush();

            $this->addFlash('success', sprintf('Le mot de passe de %s %s a été réinitialisé : %s', $user->getFirstname(), $user->getLastname(), $newPassword));
        }

        return $this->redirectToRoute('app_dashboard_admins');
    }
}
