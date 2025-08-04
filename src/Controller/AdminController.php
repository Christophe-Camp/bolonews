<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleALaUne;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ArticleALaUneRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AdminController extends AbstractController
{
     #[Route('/admin/article-a-la-une', name: 'admin_article_une')]
    public function choisirArticleUne(ArticleRepository $articleRepo, ArticleALaUneRepository $alaUneRepo): Response
    {
        $articles = $articleRepo->findBy(['publier' => true], ['date_crea' => 'DESC']);
        $articleALaUne = $alaUneRepo->findOneBy([]);

        return $this->render('admin/article_a_la_une.html.twig', [
            'articles' => $articles,
            'articleALaUne' => $articleALaUne
        ]);
    }

    #[Route('/article-a-la-une/set/{id}', name: 'admin_set_article_une')]
    public function setArticleUne(Article $article, EntityManagerInterface $em, ArticleALaUneRepository $alaUneRepo): Response
    {
        // Supprimer l'ancien article à la une s'il existe
        $old = $alaUneRepo->findOneBy([]);
        if ($old) {
            $em->remove($old);
            $em->flush();
        }

        // Définir le nouvel article à la une
        $alaUne = new ArticleALaUne();
        $alaUne->setArticle($article);

        $em->persist($alaUne);
        $em->flush();

        $this->addFlash('success', 'L\'article "' . $article->getTitre() . '" est maintenant à la une.');
        return $this->redirectToRoute('admin_article_une');
    }

    #[Route('/admin', name: 'admin_articles')]
    public function articles(): Response
    {
        return $this->render('admin/articles.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin', name: 'admin_categories')]
    public function categories(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin', name: 'admin_users')]
    public function users(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
