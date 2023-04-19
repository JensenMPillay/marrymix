<?php

namespace App\Controller;

use App\Entity\Reservation;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/reservation', name: 'app_admin_reservation')]
class AdminReservationController extends AbstractController
{
    #[Route(path: '/', name: '')]
    public function reservation(ReservationRepository $reservationRepository)
    {
        $reservations = $reservationRepository->findAll();
        // View
        return $this->render("admin/admin_reservation/reservation.html.twig", ["reservations" => $reservations]);
    }

    #[Route(path: '/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    public function reservationDelete(ManagerRegistry $managerRegistry, Reservation $reservation = null)
    {
        if ($reservation) {
            // Suppression
            $manager = $managerRegistry->getManager();
            $manager->remove($reservation);
            $manager->flush();
            $this->addFlash('delete_reservation_success', "Reservation Deleted!");
        };
        // Redirection
        return $this->redirectToRoute('app_admin_reservation');
    }
}
