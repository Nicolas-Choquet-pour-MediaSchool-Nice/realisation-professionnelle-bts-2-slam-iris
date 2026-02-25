<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\User;
use App\Repository\AdminRepository;
use App\Repository\CoordinatorRepository;
use App\Repository\EquipmentRepository;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\SchoolClassRepository;
use App\Repository\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        SchoolClassRepository $schoolClassRepository,
        StudentRepository $studentRepository,
        CoordinatorRepository $coordinatorRepository,
        AdminRepository $adminRepository,
        RoomRepository $roomRepository,
        EquipmentRepository $equipmentRepository,
        ReservationRepository $reservationRepository,
    ): Response
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
            'controller_name' => 'DashboardController',
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'user' => $connectedUser,
            'nb_classes' => $schoolClassRepository->count(),
            'nb_students' => $studentRepository->count(),
            'nb_coordinators' => $coordinatorRepository->count(),
            'nb_admins' => $adminRepository->count(),
            'nb_rooms' => $roomRepository->count(),
            'nb_equipments' => $equipmentRepository->count(),
            'nb_booking' => $reservationRepository->count()
        ];

        if ($isCoordinator) {
            $params['nb_classes'] = count($schoolClassRepository
                ->findByCoordinator($connectedUser->getId()));
            unset($params['nb_admins']);
            unset($params['nb_coordinators']);
        }

        return $this->render((in_array('ROLE_ADMIN', $connectedUser->getRoles())
            ? 'dashboard/admin.html.twig'
            : 'dashboard/coordinator.html.twig'), $params);

    }
}
