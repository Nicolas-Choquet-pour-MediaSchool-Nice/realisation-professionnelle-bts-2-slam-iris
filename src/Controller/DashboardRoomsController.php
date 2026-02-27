<?php

namespace App\Controller;

use App\Entity\Room;
use App\Form\RoomType;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardRoomsController extends AbstractController
{
    #[Route('/dashboard/rooms', name: 'app_dashboard_rooms', methods: ['GET'])]
    public function index(RoomRepository $roomRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isCoordinator = in_array('ROLE_COORDINATOR', $user->getRoles());

        return $this->render('dashboard_rooms/index.html.twig', [
            'rooms' => $roomRepository->findAll(),
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $user
        ]);
    }

    #[Route('/dashboard/rooms/new', name: 'app_dashboard_rooms_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isCoordinator = in_array('ROLE_COORDINATOR', $user->getRoles());

        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingRoom = $entityManager->getRepository(Room::class)->findOneBy(['name' => $room->getName()]);
            if ($existingRoom && $existingRoom->getId() !== $room->getId()) {
                $this->addFlash('error', 'Cette salle existe déjà.');
                return $this->render('dashboard_rooms/new.html.twig', [
                    'room' => $room,
                    'form' => $form,
                    'isAdmin' => $isAdmin,
                    'isCoordinator' => $isCoordinator,
                    'user' => $user
                ]);
            }

            $entityManager->persist($room);
            $entityManager->flush();

            $this->addFlash('success', 'Salle créée avec succès.');

            return $this->redirectToRoute('app_dashboard_rooms', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard_rooms/new.html.twig', [
            'room' => $room,
            'form' => $form,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $user
        ]);
    }

    #[Route('/dashboard/rooms/{id}', name: 'app_dashboard_rooms_show', methods: ['GET'])]
    public function show(Room $room): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isCoordinator = in_array('ROLE_COORDINATOR', $user->getRoles());

        return $this->render('dashboard_rooms/show.html.twig', [
            'room' => $room,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $user
        ]);
    }

    #[Route('/dashboard/rooms/{id}/edit', name: 'app_dashboard_rooms_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Room $room, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isCoordinator = in_array('ROLE_COORDINATOR', $user->getRoles());

        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingRoom = $entityManager->getRepository(Room::class)->findOneBy(['name' => $room->getName()]);
            if ($existingRoom && $existingRoom->getId() !== $room->getId()) {
                $this->addFlash('error', 'Ce nom de salle est déjà utilisé.');
                return $this->render('dashboard_rooms/edit.html.twig', [
                    'room' => $room,
                    'form' => $form,
                    'isAdmin' => $isAdmin,
                    'isCoordinator' => $isCoordinator,
                    'user' => $user
                ]);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Salle mise à jour avec succès.');

            return $this->redirectToRoute('app_dashboard_rooms', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard_rooms/edit.html.twig', [
            'room' => $room,
            'form' => $form,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $user
        ]);
    }

    #[Route('/dashboard/rooms/{id}', name: 'app_dashboard_rooms_delete', methods: ['POST'])]
    public function delete(Request $request, Room $room, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->request->get('_token'))) {
            $entityManager->remove($room);
            $entityManager->flush();
            $this->addFlash('success', 'Salle supprimée avec succès.');
        }

        return $this->redirectToRoute('app_dashboard_rooms', [], Response::HTTP_SEE_OTHER);
    }
}
