<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardBookingController extends AbstractController
{
    #[Route('/dashboard/booking', name: 'app_dashboard_booking')]
    public function index(ReservationRepository $reservationRepository, Request $request): Response
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

        $statusFilter = $request->query->get('status', 'all');

        $criteria = [];
        if ($statusFilter === 'active') {
            $criteria['status'] = null; // Assuming null or empty means active
            // OR we might need to use a custom query if 'active' is more complex
        } elseif ($statusFilter === 'canceled') {
            $criteria['status'] = 'canceled';
        }

        if ($statusFilter === 'active') {
             // Let's use a more robust way to get active ones (not canceled)
             $reservations = $reservationRepository->createQueryBuilder('r')
                ->where('r.status IS NULL OR r.status != :canceled')
                ->setParameter('canceled', 'canceled')
                ->orderBy('r.reservation_start', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $reservations = $reservationRepository->findBy($criteria, ['reservation_start' => 'DESC']);
        }

        return $this->render('dashboard_booking/index.html.twig', [
            'controller_name' => 'DashboardBookingController',
            'reservations' => $reservations,
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser,
            'currentStatus' => $statusFilter,
        ]);
    }
}
