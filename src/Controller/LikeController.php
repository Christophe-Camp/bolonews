<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LikeController extends AbstractController
{
    #[Route('/article/like/{id}', name: 'article_like')]
public function like(Article $article, EntityManagerInterface $em): Response
{
    /** @var User $user */
    $user = $this->getUser();

    if (!$user) {
        $this->addFlash('error', 'Vous devez être connecté pour liker un article.');
        return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
    }

    if ($article->getLikes()->contains($user)) {
        $article->removeLike($user);
    } else {
        $article->addLike($user);
    }

    $em->flush();

    return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
}
}