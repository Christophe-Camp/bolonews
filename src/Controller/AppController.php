<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use App\Repository\ArticleALaUneRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AppController extends AbstractController
{
    #[Route('/', name: 'app_app')]
    public function index(ArticleRepository $articleRepository, ArticleALaUneRepository $alaUneRepo): Response
    {
        $alaUne = $alaUneRepo->findOneBy([]);

        // On récupère les 4 derniers articles publiés
        $articles = $articleRepository->findBy(
            ['publier' => true], // seulement publiés
            ['date_crea' => 'DESC'], // tri du plus récent au plus ancien
            4 // limite
        );

        return $this->render('app/index.html.twig', [
            'articles' => $articles,
            'articleALaUne' => $alaUne ? $alaUne->getArticle() : null
        ]);
    }

    #[Route('/articles', name: 'app_articles')]
    public function articles(Request $request, ArticleRepository $articleRepository, CategorieRepository $categorieRepository): Response {
        
       // Création du formulaire de recherche (GET)
    $form = $this->createForm(SearchType::class, null, ['method' => 'GET']);
    $form->handleRequest($request);

    // Récupérer toutes les catégories
    $categories = $categorieRepository->findAll();

    // Par défaut : tous les articles publiés
    $articles = $articleRepository->findBy(
        ['publier' => true],
        ['date_crea' => 'DESC']
    );

    // Si recherche
    if ($form->isSubmitted() && $form->isValid()) {
        $searchData = $form->get('recherche')->getData();

        if (!empty($searchData)) {
            $articles = $articleRepository->findBySearch($searchData);
        }
    }

    // Si filtre par catégorie (et pas de recherche)
    $categorieId = $request->query->get('categorie');
    if ($categorieId && empty($form->get('recherche')->getData())) {
        $articles = $articleRepository->findBy(
            ['publier' => true, 'categorie' => $categorieId],
            ['date_crea' => 'DESC']
        );
    }


        return $this->render('app/articles.html.twig', [
            'categories' => $categories,
            'articles' => $articles,
            'form' => $form->createView(),  // Nécessaire pour afficher le form de recherche
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('app/contact.html.twig');
    }
}
