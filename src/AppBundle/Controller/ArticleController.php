<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Form\ArticleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{

    /**
     *--------------------------------------------------------------------------------------------------------------
     *==============   PARTIE PUBLIQUE   ======================================================================
     * -------------------------------------------------------------------------------------------------------------
     */

    /**
     * index page
     *
     * @Route("/", defaults={"page": "1", "_format"="html"}, name="home")
     * @Route("/page/{page}", defaults={"_format"="html"}, requirements={"page": "[0-9]\d*"}, name="home_paginated")
     * @Method("GET")
     */
    public function indexAction($page)
    {
        if ($page < 1) {
            throw $this->createNotFoundException("La page " . $page . " n'existe pas.");
        }


        // On récupère notre objet Paginator
        $listArticles = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Article')
            ->getArticlesPaginated($page);

        // Si il n'y a pas d'articles on renvoit vers le formulaire d'ajout
        if (count($listArticles) < 1) {
            return $this->redirectToRoute('add');
        }

        // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
        $nbPages = ceil(count($listArticles) / Article::NUM_ITEMS);

        // Si la page n'existe pas, on retourne une 404
        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page " . $page . " n'existe pas.");
        }

        // On donne toutes les informations nécessaires à la vue
        return $this->render(
            'Article/index.html.twig',
            array(
                'listArticles' => $listArticles,
                'nbPages' => $nbPages,
                'page' => $page,
            )
        );
    }

    /**
     * Display article content
     * @Route("/article/{slug}", name="view_article")
     * @Method("GET")
     */
    public function viewAction(Article $article)
    {
        $listeComments = $this->getDoctrine()->getRepository("AppBundle:Comment")->findBy(
            array('article' => $article, 'level' => 1)
        );


        return $this->render(
            'Article/view.html.twig',
            array(
                'comments' => $listeComments,
                'article' => $article,


            )
        );
    }


    /**
     *--------------------------------------------------------------------------------------------------------------
     *==============   PARTIE ADMIN   ======================================================================
     * -------------------------------------------------------------------------------------------------------------
     */

    /**
     * view all articles on admin page
     * @Route("/admin/articles", name="adminArticles")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewArticlesAction()
    {
        $listArticles = $this->getDoctrine()->getRepository("AppBundle:Article")->findAll();
        return $this->render('Admin/articles.html.twig', array('listArticles' => $listArticles));
    }

    /**
     * Display form to add a NEW article
     * @Method({"GET", "POST"})
     * @param Request $request
     * @Security("has_role('ROLE_ADMIN')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route("/add", name="add")
     */
    public function addAction(Request $request)
    {
        $Article = new Article();
        $form = $this->createForm(ArticleType::class, $Article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($Article);
            $em->flush();

            $request->getSession()->getFlashbag()->add('success', 'Le nouveau chapitre a bien été enregistré.');

            return $this->redirectToRoute('view_article', array('slug' => $Article->getSlug()));
        }

        return $this->render(
            'article/add.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Displays a form to edit an existing Article entity.
     * @Security("has_role('ROLE_ADMIN')")
     * @Method({"GET", "POST"})
     * @Route("/{slug}/edit", requirements={"id": "\d+"}, name="edit")
     */
    public function editAction(Article $article, Request $request)
    {
        $referer = $request->headers->get('referer');
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Article modifié avec succès');
            return $this->redirect($referer);

        }

        return $this->render(
            'Article/edit.html.twig',
            [
                'article' => $article,
                'form' => $form->createView(),
            ]
        );
    }


    /**
     * Delete Article
     * @Route("/{slug}/delete", name="delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     */
    public function deleteAction(Article $article, Request $request)

    {
        $referer = $request->headers->get('referer');

        if ($this->getDoctrine()->getRepository("AppBundle:Article")->countAll() > 1) {

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article Supprimé avec succès');

            if ($referer == $this->get('router')->generate('view_article', array('slug' => $article->getSlug()))) {
                return $this->redirectToRoute('home');
            } else {
                return $this->redirect($referer);
            }


        } else {
            $this->addFlash('alert', 'Vous ne pouvez pas supprimer le dernier article !');

            return $this->redirect($referer);

        }


    }


}
