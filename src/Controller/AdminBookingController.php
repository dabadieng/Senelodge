<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminBookingController extends AbstractController
{
    /**
     * @Route("/admin/bookings", name="admin_bookings_index")
     */
    public function index(BookingRepository $repos)
    {
        return $this->render('admin/booking/index.html.twig', [
            "bookings" => $repos->findAll()
        ]);
    }

    /**
     * Permet d'éditer une réservation
     * @Route("/admin/bookings/{id}/edit", name="admin_bookings_edit")
     *
     * @param Ad $booking
     * @param Request $request
     * @return Response 
     */
    public function edit(Booking $booking, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(AdminBookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //la valeur 0 est considérée empty donc on appel la méthode prepersiste 
            $booking->setAmount(0); 

            $this->addFlash(
                'success',
                "la réservation N° <strong>{$booking->getId()}</strong> a bien été modifée"
            );

            $manager->persist($booking);
            $manager->flush();

            return $this->redirectToRoute('admin_bookings_index');
        }

        return $this->render("/admin/booking/edit.html.twig", [
            "form" => $form->createView(),
            "booking" => $booking
        ]);
    }



    /**
     * Permet de supprimer une réservation
     * @Route("/admin/bookings/{id}/delete", name="admin_bookings_delete")
     *
     * @param Ad $booking
     * @param EntityManagerInterface $manager
     * @return Response 
     */
    public function delete(Booking $booking, EntityManagerInterface $manager)
    {

        $manager->remove($booking);
        $manager->flush();

        $this->addFlash(
            "success",
            "La réservation concernant l'annonce {$booking->getAd()->getTitle()} a bien été supprimée"
        );

        return $this->redirectToRoute("admin_bookings_index");
    }
}
