<?php

namespace App\Controller;

use App\Entity\Reaction;
use App\Form\Reaction1Type;
use App\Repository\ReactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\{TextType,ButtonType,EmailType,HiddenType,PasswordType,TextareaType,SubmitType,NumberType,DateType,MoneyType,BirthdayType};


/**
 * @Route("/reaction")
 */
class ReactionController extends AbstractController
{
    /**
     * @Route("/", name="reaction_index", methods={"GET"})
     */
    public function index(ReactionRepository $reactionRepository): Response
    {
        return $this->render('reaction/index.html.twig', [
            'reactions' => $reactionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="reaction_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $reaction = new Reaction();
        $form = $this->createForm(Reaction1Type::class, $reaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reaction);
            $entityManager->flush();

            return $this->redirectToRoute('reaction_index');
        }

        return $this->render('reaction/new.html.twig', [
            'reaction' => $reaction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="reaction_show", methods={"GET"})
     */
    public function show(Reaction $reaction): Response
    {
        return $this->render('reaction/show.html.twig', [
            'reaction' => $reaction,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="reaction_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Reaction $reaction): Response
    {
        $form = $this->createForm(Reaction1Type::class, $reaction);

        // $form = $this->createForm(PostType::class, $post);
        $form = $this->createFormBuilder($reaction)
        ->add('Body', TextareaType::class)
        ->add('save', SubmitType::class, ['label' => 'Save edit', 'attr' => ['class' => 'btn-success btn']])
        ->getForm();

        $form->handleRequest($request);

        $goBack = '/post/'.strval($request->request->get('_post'));

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            // return $this->redirectToRoute('reaction_index');

            return $this->redirect($request->request->get('_post'));
        }

        return $this->render('reaction/edit.html.twig', [
            'reaction' => $reaction,
            'form' => $form->createView(),
            'post' => $goBack
        ]);
    }

    /**
     * @Route("/{id}", name="reaction_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Reaction $reaction): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reaction->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reaction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reaction_index');
    }

    /**
     * @Route("/{id}", name="reaction_delete_custom", methods={"CUSTOM"})
     */
    public function deleteCustom(Request $request, Reaction $reaction): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reaction->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reaction);
            $entityManager->flush();
        }

        $goBack = '/post/'.strval($request->request->get('_post'));

        return $this->redirect($goBack);
    }
}
