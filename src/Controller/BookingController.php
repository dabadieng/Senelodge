<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Entity\OrderBooking;
use App\Form\BookingType;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class BookingController extends AbstractController
{
    /**
     * @Route("/ads/{slug}/book", name="booking_create")
     * IsGranted("ROLE_USER")
     */
    public function book(Ad $ad, Request $request, EntityManagerInterface $manager, EntityManagerInterface $managerWait)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->render("account/renvoiMail.html.twig");
        }

        $booking = new Booking();

        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $booking->setAd($ad)
                ->setBooker($this->getUser());

            // Si les dates ne sont pas disponibles, message d'erreur
            if (!$booking->isBookableDates()) {
                $this->addFlash(
                    'warning',
                    "Les dates que vous avez choisi ne peuvent être réservées : elles sont déjà prises."
                );
            } else {
                //Persist l'entity pour obtenir les methodes de callback 
                $manager->persist($booking);

                // Sinon enregistrement et redirection vers le paiement 
                $orderBooking = new OrderBooking();
                $orderBooking->setAd($booking->getAd())
                    ->setAmount($booking->getAmount())
                    ->setBooker($booking->getBooker())
                    ->setComment($booking->getComment())
                    ->setStartDate($booking->getStartDate())
                    ->setEndDate($booking->getEndDate());

                $manager->persist($orderBooking);
                $manager->remove($booking); 
                $manager->flush();

                $session = $request->getSession();
                $session->set("order", $orderBooking);

                return $this->redirectToRoute('ask_payment');
            }
        }


        return $this->render('booking/book.html.twig', [
            "ad" => $ad,
            "form" => $form->createView()
        ]);
    }


    /**
     * Permet de visualiser une réservation 
     * @Route("/booking/{id}", name="booking_show")
     *
     * @param Booking $booking
     * @return Response
     */
    public function show(Booking $booking, Request $request, EntityManagerInterface $maneger)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->render("account/renvoiMail.html.twig");
        }

        $comment = new Comment;
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setAd($booking->getAd())
                ->setAuthor($this->getUser());

            $maneger->persist($comment);
            $maneger->flush();

            $this->addFlash(
                "success",
                "Votre commentaire à bien été prise en comte"
            );
        }

        return $this->render("booking/show.html.twig", [
            "booking" => $booking,
            "form" => $form->createView()
        ]);
    }
}
