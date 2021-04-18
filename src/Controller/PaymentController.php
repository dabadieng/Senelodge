<?php

namespace App\Controller;

use App\Entity\Ad;
use Stripe\Stripe;
use App\Entity\Booking;
use App\Repository\AdRepository;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PaymentController extends AbstractController
{

    private $bookingRepository;
    private $bookingTitle;
    private $bookingAmount;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }
    /**
     * Paiement de la réservation 
     *@Route("/create-checkout-session", name="checkout")
     * 
     */
    public function paymentBook(Request $request)
    {
        $session = $request->getSession();
        $booking = $session->get("order");

        \Stripe\Stripe::setApiKey('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');

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Réservation pour l\'annonce : ' . $booking->getAd()->getTitle(),
                    ],
                    'unit_amount' => $booking->getAmount() * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl("success", [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl("echec", [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);


        return new JsonResponse(['id' => $session->id]);
    }

    /**
     *@Route("/ask/payment", name="ask_payment")
     * @return void
     */
    public function askPayment(Request $request, EntityManagerInterface $manager)
    {
        $session = $request->getSession();
        $order = $session->get("order");

        return $this->render("payment/ask.html.twig", [
            "order" => $order
        ]);
    }

    /**
     *@Route("/success", name="success")
     */
    public function success(Request $request, EntityManagerInterface $manager, AdRepository $adRepository)
    {
        $session = $request->getSession();
        $order = $session->get("order");
        $ad = $adRepository->find($order->getAd()->getId());

        $booking = new Booking();
        $booking->setAd($ad)
            ->setAmount($order->getAmount())
            ->setBooker($this->getUser())
            ->setComment($order->getComment())
            ->setStartDate($order->getStartDate())
            ->setEndDate($order->getEndDate());

        $manager->persist($booking);
        $manager->flush();

        return $this->redirectToRoute('booking_show', ['id' => $booking->getId(), 'withAlert' => true]);
    }


    /**
     *@Route("/echec", name="echec")
     */
    public function echec()
    {
        return $this->render("payment/echec.html.twig");
    }
}
