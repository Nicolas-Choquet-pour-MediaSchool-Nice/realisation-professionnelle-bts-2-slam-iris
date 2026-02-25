<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardBookingController extends AbstractController
{
    #[Route('/dashboard/booking', name: 'app_dashboard_booking')]
    public function index(ReservationRepository $reservationRepository): Response
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

        $reservations = $reservationRepository->findBy([], ['reservation_start' => 'DESC']);

        return $this->render('dashboard_booking/index.html.twig', [
            'controller_name' => 'DashboardBookingController',
            'reservations' => $reservations,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser,
        ]);
    }
}
