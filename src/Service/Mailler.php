<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mailler
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail($email, $token)
    {
        $email = (new TemplatedEmail())
            ->from('dieng.daba@gmail.com')
            ->to(new Address($email))
            ->cc('dieng.daba@outlook.com')
            //->addCc('cc2@example.com')
            ->subject('Veuillez confirmer votre compte')

            // path of the Twig template to render
            ->htmlTemplate('emails/validation.html.twig')
            //->html('<p>See Twig integration for better HTML integration!</p>')
            //->text('you have successfully registered!')
            //->html('<p>See Twig integration for better HTML integration!</p>')

            // pass variables (name => value) to the template
            ->context([
                'token' => $token,
                'expiration_date' => new \DateTime('+7 days'),
            ]);

        $this->mailer->send($email);
        //return $this->redirectToRoute('account_login');
    }
}
