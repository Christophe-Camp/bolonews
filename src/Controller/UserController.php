<?php

namespace App\Controller;

use App\Repository\ArticleRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    /*#[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }*/

    #[Route('/user', name: 'app_user')]
    public function index(ArticleRepository $articleRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $articlesPublies = $articleRepository->findBy([
            'auteur' => $user,
            'publier' => true
        ]);

        $articlesNonPublies = $articleRepository->findBy([
            'auteur' => $user,
            'publier' => false
        ]);

        return $this->render('user/index.html.twig', [
            'articlesPublies' => $articlesPublies,
            'articlesNonPublies' => $articlesNonPublies
        ]);
    }
}
