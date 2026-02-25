<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookingController extends AbstractController
{
    #[Route('/booking', name: 'app_booking')]
    public function index(Request $request, ReservationRepository $reservationRepository, RoomRepository $roomRepository): Response
    {
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }

        $rooms = $roomRepository->findAll();
        $roomId = $request->query->get('room');
        $selectedRoom = null;
        if ($roomId) {
            $selectedRoom = $roomRepository->find($roomId);
        }
        if (!$selectedRoom && count($rooms) > 0) {
            $selectedRoom = $rooms[0];
        }

        $dateStr = $request->query->get('date');
        try {
            $selectedDate = $dateStr ? new \DateTime($dateStr) : new \DateTime();
        } catch (\Exception $e) {
            $selectedDate = new \DateTime();
        }

        $reservations = $reservationRepository->findByDate($selectedDate);
        if ($selectedRoom) {
            $reservations = array_filter($reservations, function($res) use ($selectedRoom) {
                return $res->getRoom()->getId() === $selectedRoom->getId();
            });
        }

        // Générer les créneaux horaires de 8h à 20h toutes les 30 min
        $slots = [];
        $startTime = (clone $selectedDate)->setTime(8, 0, 0);
        $endTime = (clone $selectedDate)->setTime(20, 0, 0);

        while ($startTime <= $endTime) {
            $slots[] = clone $startTime;
            $startTime->modify('+30 minutes');
        }

        // Données pour le calendrier
        $year = (int)$selectedDate->format('Y');
        $month = (int)$selectedDate->format('m');

        $firstDayOfMonth = new \DateTime("$year-$month-01");
        $lastDayOfMonth = (clone $firstDayOfMonth)->modify('last day of this month');

        $daysInMonth = [];
        $currentDate = clone $firstDayOfMonth;

        // Ajouter les jours vides au début si le mois ne commence pas un lundi (1)
        $firstDayDayOfWeek = (int)$firstDayOfMonth->format('N');
        for ($i = 1; $i < $firstDayDayOfWeek; $i++) {
            $daysInMonth[] = null;
        }

        while ($currentDate <= $lastDayOfMonth) {
            $daysInMonth[] = clone $currentDate;
            $currentDate->modify('+1 day');
        }

        return $this->render('booking/index.html.twig', [
            'controller_name' => 'BookingController',
            'isAdmin' => in_array('ROLE_ADMIN', $connectedUser->getRoles()),
            'isCoordinator' => in_array('ROLE_COORDINATOR', $connectedUser->getRoles()),
            'user' => $connectedUser,
            'selectedDate' => $selectedDate,
            'reservations' => $reservations,
            'timeSlots' => $slots,
            'daysInMonth' => $daysInMonth,
            'currentMonth' => $firstDayOfMonth,
            'prevMonth' => (clone $firstDayOfMonth)->modify('-1 month'),
            'nextMonth' => (clone $firstDayOfMonth)->modify('+1 month'),
            'rooms' => $rooms,
            'selectedRoom' => $selectedRoom,
        ]);
    }

    #[Route('/booking/reserve', name: 'app_booking_reserve', methods: ['POST'])]
    public function reserve(Request $request, RoomRepository $roomRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $date = $data['date'] ?? null;
        $slots = $data['slots'] ?? [];
        $roomId = $data['roomId'] ?? null;

        if (!$date || empty($slots) || !$roomId) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }

        $room = $roomRepository->find($roomId);
        if (!$room) {
            return new JsonResponse(['error' => 'Salle introuvable'], 404);
        }

        // Trier les créneaux pour s'assurer qu'ils sont dans l'ordre chronologique
        sort($slots);

        try {
            if (empty($slots)) {
                return new JsonResponse(['error' => 'Aucun créneau sélectionné'], 400);
            }

            // Pour l'instant, on suppose que l'utilisateur sélectionne des créneaux contigus
            // On prend le premier et on ajoute 30 min au dernier
            $start = new \DateTime($date . ' ' . $slots[0]);
            $lastSlot = new \DateTime($date . ' ' . end($slots));
            $end = (clone $lastSlot)->modify('+30 minutes');

            $reservation = new Reservation();
            $reservation->setUser($user);
            $reservation->setRoom($room);
            $reservation->setReservationStart($start);
            $reservation->setReservationEnd($end);

            $entityManager->persist($reservation);
            $entityManager->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/booking/cancel/{id}', name: 'app_booking_cancel', methods: ['POST'])]
    public function cancel(Reservation $reservation, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        // Vérifier que c'est bien la réservation de l'utilisateur ou qu'il a les droits (Admin/Coord)
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isCoordinator = in_array('ROLE_COORDINATOR', $user->getRoles());

        if ($reservation->getUser()->getId() !== $user->getId() && !$isAdmin && !$isCoordinator) {
            return new JsonResponse(['error' => 'Non autorisé à annuler cette réservation'], 403);
        }

        $reservation->setStatus('canceled');
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
