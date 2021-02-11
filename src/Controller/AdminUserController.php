<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    /**
     * @Route("/admin/users/{page<\d+>?1}", name="admin_users_index")
     */
    public function index(PaginationService $paginationService, UserRepository $repos, $page)
    {
        $paginationService->setEntityClass(User::class)
            ->setPage($page);

        return $this->render('admin/user/index.html.twig', [
            "pagination" => $paginationService,
        ]);
    }

    /**
     * Permet d'éditer un utilisateur
     * @Route("/admin/users/{id}/edit", name="admin_users_edit")
     *
     * @param User $User
     * @param Request $request
     * @return Response 
     */
    public function edit(User $user, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //la valeur 0 est considérée empty donc on appel la méthode prepersiste 

            $this->addFlash(
                'success',
                "la réservation N° <strong>{$user->getId()}</strong> a bien été modifée"
            );

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render("/admin/user/edit.html.twig", [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }



    /**
     * Permet de supprimer un utilisateur
     * @Route("/admin/users/{id}/delete", name="admin_users_delete")
     *
     * @param User $User
     * @param EntityManagerInterface $manager
     * @return Response 
     */
    public function delete(User $user, EntityManagerInterface $manager)
    {

        $manager->remove($user);
        $manager->flush();

        $this->addFlash(
            "success",
            "L'utilisateur {$user->getId()} a bien été supprimé"
        );

        return $this->redirectToRoute("admin_users_index");
    }
}
