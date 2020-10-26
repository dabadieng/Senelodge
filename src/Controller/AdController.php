<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AdType;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class AdController extends AbstractController
{
    /**
     * @Route("/ads", name="ad_index")
     */
    public function index(AdRepository $repos)
    {
        $ads = $repos->findAll();

        return $this->render('ad/index.html.twig', [
            'ads' => $ads,
        ]);
    }
    /**
     * permet de créer une annonce
     *@Route("/ads/new", name="ads_create")
     * 
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager)
    {

        $ad = new Ad();
        $image = new Image();

        $image->setUrl("http://placehold.it")
            ->setCaption("ma 1er image");
        $image2 = new Image();

        $image2->setUrl("http://placehold.it")
            ->setCaption("ma 1er image");

        $ad->addImage($image)
            ->addImage($image2);




        $form = $this->createForm(AdType::class, $ad);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash(
                'success',
                "l'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistré"
            );

            $manager->persist($ad);
            $manager->flush();

            return $this->redirectToRoute("ads_show", [
                "slug" => $ad->getSlug()
            ]);
        }

        return $this->render("ad/new.html.twig", [
            "form" => $form->createView()
        ]);
    }



    /**
     * permet de retourner le détail d'une annonce 
     *@Route("/ads/{slug}", name="ads_show")
     * @return Response
     */
    public function show(Ad $ad)
    {

        return $this->render("ad/show.html.twig", [
            'ad' => $ad,
        ]);
    }
}
