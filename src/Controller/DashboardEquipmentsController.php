<?php

namespace App\Controller;

use App\Entity\Equipment;
use App\Form\EquipmentType;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardEquipmentsController extends AbstractController
{
    #[Route('/dashboard/equipments', name: 'app_dashboard_equipments', methods: ['GET'])]
    public function index(EquipmentRepository $equipmentRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isCoordinator = in_array('ROLE_COORDINATOR', $user->getRoles());

        return $this->render('dashboard_equipments/index.html.twig', [
            'equipments' => $equipmentRepository->findAll(),
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $user
        ]);
    }

    #[Route('/dashboard/equipments/new', name: 'app_dashboard_equipments_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isCoordinator = in_array('ROLE_COORDINATOR', $user->getRoles());

        $equipment = new Equipment();
        $form = $this->createForm(EquipmentType::class, $equipment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingEquipment = $entityManager->getRepository(Equipment::class)->findOneBy(['name' => $equipment->getName()]);
            if ($existingEquipment && $existingEquipment->getId() !== $equipment->getId()) {
                $this->addFlash('error', 'Cet équipement existe déjà.');
                return $this->render('dashboard_equipments/new.html.twig', [
                    'equipment' => $equipment,
                    'form' => $form,
                    'isAdmin' => $isAdmin,
                    'isCoordinator' => $isCoordinator,
                    'user' => $user
                ]);
            }

            $entityManager->persist($equipment);
            $entityManager->flush();

            $this->addFlash('success', 'Équipement créé avec succès.');

            return $this->redirectToRoute('app_dashboard_equipments', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard_equipments/new.html.twig', [
            'equipment' => $equipment,
            'form' => $form,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $user
        ]);
    }

    #[Route('/dashboard/equipments/{id}', name: 'app_dashboard_equipments_show', methods: ['GET'])]
    public function show(Equipment $equipment): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isCoordinator = in_array('ROLE_COORDINATOR', $user->getRoles());

        return $this->render('dashboard_equipments/show.html.twig', [
            'equipment' => $equipment,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $user
        ]);
    }

    #[Route('/dashboard/equipments/{id}/edit', name: 'app_dashboard_equipments_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipment $equipment, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isCoordinator = in_array('ROLE_COORDINATOR', $user->getRoles());

        $form = $this->createForm(EquipmentType::class, $equipment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingEquipment = $entityManager->getRepository(Equipment::class)->findOneBy(['name' => $equipment->getName()]);
            if ($existingEquipment && $existingEquipment->getId() !== $equipment->getId()) {
                $this->addFlash('error', 'Ce nom d\'équipement est déjà utilisé.');
                return $this->render('dashboard_equipments/edit.html.twig', [
                    'equipment' => $equipment,
                    'form' => $form,
                    'isAdmin' => $isAdmin,
                    'isCoordinator' => $isCoordinator,
                    'user' => $user
                ]);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Équipement mis à jour avec succès.');

            return $this->redirectToRoute('app_dashboard_equipments', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard_equipments/edit.html.twig', [
            'equipment' => $equipment,
            'form' => $form,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $user
        ]);
    }

    #[Route('/dashboard/equipments/{id}', name: 'app_dashboard_equipments_delete', methods: ['POST'])]
    public function delete(Request $request, Equipment $equipment, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$equipment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($equipment);
            $entityManager->flush();
            $this->addFlash('success', 'Équipement supprimé avec succès.');
        }

        return $this->redirectToRoute('app_dashboard_equipments', [], Response::HTTP_SEE_OTHER);
    }
}
