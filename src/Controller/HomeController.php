<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    /**
     * @Route("/hello/{prenom}/age/{age}", name="hello")
     * @Route("/hello", name="hello_base")
     * @Route("/hello/{prenom}", name="hello_prenom")
     * dis hello dès que l'on appelle 
     *
     * @return void
     */
    public function hello($prenom = "anonyme", $age = 0)
    {
        return $this->render(
            "home/index.html.twig",
            [
                "title" => "bienvenue sur mon site",
                "prenomm" => $prenom,
                "age" => $age
            ]
        );
    }
    /**
     * @Route("/", name="homepage")
     */
    public function home()
    {
        $prenoms = ["daba" => 37, "aly" => 41, "salimata" => 9];

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'title' => 'bonjour',
            'age' => 31,
            'tableau' => $prenoms
        ]);
    }
}
