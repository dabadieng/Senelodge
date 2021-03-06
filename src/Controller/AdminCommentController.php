<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\AdminCommentType;
use App\Repository\CommentRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCommentController extends AbstractController
{
    /**
     * @Route("/admin/comments/{page<\d+>?1}", name="admin_comments_index")
     */
    public function index(PaginationService $pagination, CommentRepository $repos, $page)
    {
        $pagination->setEntityClass(Comment::class)
            ->setLimit(5)
            ->setPage($page);

        return $this->render('admin/comment/index.html.twig', [
            "pagination" => $pagination,
        ]);
    }

    /**
     * Permet d'éditer un commentaire  
     * @Route("/admin/comments/{id}/edit", name="admin_comment_edit")
     *
     * @param Comment $comment
     * @param Request $request
     * @return Response 
     */
    public function edit(Comment $comment, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(AdminCommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash(
                'success',
                "le commentaire de l'annonce <strong>{$comment->getAd()->getTitle()}</strong> a bien été modifée"
            );

            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('admin_comments_index');
        }

        return $this->render("/admin/comment/edit.html.twig", [
            "form" => $form->createView(),
            "comment" => $comment
        ]);
    }



    /**
     * Permet de supprimer un commentaire 
     * @Route("/admin/comments/{id}/delete", name="admin_comment_delete")
     *
     * @param Comment $comment
     * @param EntityManagerInterface $manager
     * @return Response 
     */
    public function delete(Comment $comment, EntityManagerInterface $manager)
    {
        $manager->remove($comment);
        $manager->flush();

        $this->addFlash(
            "success",
            "Le commentaire {$comment->getId()} concernant l'annonce {$comment->getAd()->getTitle()} a bien été supprimée"
        );

        return $this->redirectToRoute("admin_comments_index");
    }
}
