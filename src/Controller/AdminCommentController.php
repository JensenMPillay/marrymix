<?php

namespace App\Controller;

use App\Entity\Comment;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/comment", name="app_admin_comment")
 */
class AdminCommentController extends AbstractController
{
    /**
     * @Route("/", name="")
     */
    public function comment(CommentRepository $commentRepository)
    {
        $comments = $commentRepository->findAll();
        // View
        return $this->render("admin/admin_comment/comment.html.twig", ["comments" => $comments]);
    }

    /**
     * @Route("comment/delete/{id}", name="_delete", requirements={"id"="\d+"})
     */
    public function commentDelete(ManagerRegistry $managerRegistry, Comment $comment = null)
    {
        if ($comment) {
            // Suppression
            $manager = $managerRegistry->getManager();
            $manager->remove($comment);
            $manager->flush();
            $this->addFlash('delete_comment_success', "Comment Deleted!");
        };
        // Redirection
        return $this->redirectToRoute('app_admin_comment');
    }
}
