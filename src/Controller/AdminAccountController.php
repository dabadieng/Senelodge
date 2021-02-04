<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminAccountController extends AbstractController
{
    /**
     * @Route("/admin/login", name="admin_account_login")
     */
    public function login(AuthenticationUtils $utile)
    {
        //renvoie un booléan en cas d'erreur
        $error = $utile->getLastAuthenticationError();

        //permet de ne pas retapper l'adresse mail en cas d'erreur
        $username = $utile->getLastUsername();

        return $this->render('admin/account/login.html.twig', [
            'hasError' => $error !== null,
            'username' => $username,
        ]);
    }

    /**
     * Permet de se déconnecter
     * @Route("/admin/logout", name="admin_account_logout")
     *
     * @return void
     */
    public function logout()
    {
    }
}
