<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AdType;
use App\Entity\Image;
use App\Entity\SearchAd;
use App\Form\AdEditType;
use App\Form\SearchAdType;
use App\Entity\Localisation;
use App\Repository\AdRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{

    /**
     * @Route("/ads/index", name="ad_index")
     */
    public function index(Request $request, PaginatorInterface $paginator, AdRepository $repos)
    {
        $searchAd = new SearchAd();

        $form = $this->createForm(SearchAdType::class, $searchAd);
        $form->handleRequest($request);

        // Paginate the results of the query
        $appointments = $paginator->paginate(
            $repos->findAllFiltreQuery($searchAd),
            $request->query->getInt('page', 1),
            9
        );

        //$appointments = $repos->findByFiltre($searchAd);

        return $this->render('ad/index.html.twig', [
            //"pagination" => $pagination,
            'appointments' => $appointments,
            'form' => $form->createView()
        ]);
    }


    /**
     * permet de créer une annonce
     *@Route("/ad/new", name="ads_create")
     *Permet de gérer les droits 
     *
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->render("account/renvoiMail.html.twig");
        }
        $ad = new Ad();

        $form = $this->createForm(AdType::class, $ad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            
            $this->addFlash(
                'success',
                "l'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistré"
            );

            //affectation de l'annonce au user connecté
            $ad->setAuthor($this->getUser());

            //On ne fait pas persister l'entity img car elle est reliée par la mention cascade dans l'entity ad 
            $manager->persist($ad);
            $manager->flush();


            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug(),
            ]);
        }

        return $this->render('ad/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * Permet d'afficher le formulaie d'édition
     *@Route("/ads/{slug}/edit", name="ads_edit")
     *@Security("user === ad.getAuthor()", 
     *message="Cette annonce ne vous appartient pas. Vous ne pouvez pas l'éditer")
     *
     * @return Response 
     */
    public function edit(Ad $ad, Request $request, EntityManagerInterface $manager)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->render("account/renvoiMail.html.twig");
        }

        $form = $this->createForm(AdType::class, $ad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //On récupère les images transmises
            $images = $form->get("images")->getData();

            //On boucle sur les images 
            foreach ($images as $image) {
                //On génère un nouveau nom de fichier 
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                //On copie le fichier dans le dossier upload 
                $image->move(
                    //Lieu de stockage défini dans service.yml 
                    $this->getParameter("image_directory"),
                    //nom du fichier à déplacer dans le dossier 
                    $fichier
                );

                //On stocke l'image dans la db (son nom)
                $img = new Image();
                $img->setUrl($fichier);
                $ad->addImage($img);
            }
            $this->addFlash(
                'success',
                "l'annonce <strong>{$ad->getTitle()}</strong> a bien été modifée"
            );

            $manager->persist($ad);
            $manager->flush();

            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug(),
            ]);
        }

        return $this->render('ad/edit.html.twig', [
            'form' => $form->createView(),
            'ad' => $ad,
        ]);
    }

    /**
     * permet de retourner le détail d'une annonce
     *@Route("/ads/{slug}", name="ads_show")
     * @return Response
     */
    public function show(Ad $ad)
    {
        return $this->render('ad/show.html.twig', [
            'ad' => $ad,
        ]);
    }
    /**
     * Permet de supprimer une annonce 
     * @Route("/ads/{slug}/delete", name="ads_delete")
     * @Security("user == ad.getAuthor()", message="Vous n'avez pas le droit à acceder à cette resources")
     *
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(Ad $ad, EntityManagerInterface $manager)
    {
        //Permet de vérifier si l'utilisateur à valider son compte 
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->render("account/renvoiMail.html.twig");
        }

        $manager->remove($ad);
        $manager->flush();

        $this->addFlash(
            "success",
            "l'annonce {ad.getTitle()} à bien été supprimée "
        );

        return $this->redirectToRoute("ad_index");
    }



    /**
     * @Route("/ads/supprime/image/{id}", name="ad_delete_image", methods={"DELETE"})
     * @Security("user == ad.getAuthor()", message="Vous n'avez pas le droit à acceder à cette resources")
     */
    public function deleteImage(Image $image, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // On vérifie si le token est valide
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {
            // On récupère le nom de l'image
            $nom = $image->getUrl();
            // On supprime le fichier
            unlink($this->getParameter('image_directory') . '/' . $nom);

            // On supprime l'entrée de la base
            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();

            // On répond en json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
}
