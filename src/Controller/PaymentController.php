<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use Stripe\Stripe;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractController
{
    /**
     * Paiement de la réservation 
     *@Route("/create-checkout-session", name="checkout")
     * 
     */
    public function paymentBook()
    {

        \Stripe\Stripe::setApiKey('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');

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'T-shirt',
                    ],
                    'unit_amount' => 2000,
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
    public function paid()
    {
        return $this->render("payment/ask.html.twig");
    }

    /**
     *@Route("/success", name="success")
     */
    public function success()
    {
        return $this->render("payment/success.html.twig");
    }


    /**
     *@Route("/echec", name="echec")
     */
    public function echec()
    {
        return $this->render("payment/echec.html.twig");
    }
}
