<?php

namespace App\Controller;

use App\Form\CommentaireType;
use App\Entity\Commentaire;
use App\Repository\ArticleRepository;
use App\Entity\Article;
use App\Form\NewArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class ArticleController extends AbstractController
{
    #[Route('/article/create', name: 'app_create')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $article = new Article();

        // Auteur = utilisateur connecté
        $article->setAuteur($this->getUser());

        // Crée le formulaire
        $form = $this->createForm(NewArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion de la photo AVANT l'enregistrement
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);

                // Nettoyage du nom de fichier
                $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalFilename);

                // Ajout d'un identifiant unique
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'), // défini dans services.yaml
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                    return $this->redirectToRoute('app_create');
                }

                // Enregistre le nom de la photo dans l'article
                $article->setPhoto($newFilename);
            }

            // Date de création
            $article->setDateCrea(new \DateTimeImmutable());
            $article->setDateModif(new \DateTimeImmutable());

            // Enregistre en base
            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'Article créé avec succès !');

            return $this->redirectToRoute('app_user');
        }

        return $this->render('article/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/edit/{id}', name: 'app_edit')]
    public function edit(Article $article, Request $request, EntityManagerInterface $em): Response
    {
        // Si ce n’est pas son article
        if ($article->getAuteur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cet article.');
        }

         // Créer le formulaire (même que pour la création)
        $form = $this->createForm(NewArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Gérer l’upload photo si modifiée
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                    $article->setPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo.');
                }
            }

            // Mettre à jour la date de modification
            $article->setDateModif(new \DateTimeImmutable());

            $em->flush();

            $this->addFlash('success', 'Article mis à jour avec succès !');

            return $this->redirectToRoute('app_user');
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }
    

    #[Route('/article/show/{id}', name: 'article_show')]
    public function show(Article $article, Request $request, EntityManagerInterface $em): Response
    {
        // Formulaire seulement si connecté
    $commentaireForm = null;

    if ($this->getUser()) {
        $commentaire = new Commentaire();
        $commentaire->setArticle($article);
        $commentaire->setAuteur($this->getUser());
        $commentaire->setDatePublication(new \DateTimeImmutable());

        $commentaireForm = $this->createForm(CommentaireType::class, $commentaire);
        $commentaireForm->handleRequest($request);

        if ($commentaireForm->isSubmitted() && $commentaireForm->isValid()) {
            
            $em->persist($commentaire);
            $em->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté.');
            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }
    }
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'commentaire_form' => $commentaireForm ? $commentaireForm->createView() : null
        ]);
    }
}
