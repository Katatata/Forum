<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Category;
use App\Entity\Reaction;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\ReactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\{TextType,ButtonType,EmailType,HiddenType,PasswordType,TextareaType,SubmitType,NumberType,DateType,MoneyType,BirthdayType};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;



/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository): Response
    {
        // return $this->render('post/index.html.twig', [
        //     'posts' => $postRepository->findAll(),
        //     'amount' => 40,
        //     'pagePage' => false,
        // ]);

        $limit = 40;
        $realOffset = 0;

        $amount = $postRepository->createQueryBuilder('a')
        // Filter by some parameter if you want
        // ->where('a.published = 1')
        ->select('count(a.id)')
        ->getQuery()
        ->getSingleScalarResult();

        return $this->render('post/index.html.twig', [
            'array' =>  $postRepository->findAll(),
            'pagePage' => true,
            'currentPage' => 1,
            'amount' => $amount,
            'posts' => $postRepository->findBy([], null, $limit, $realOffset),
        ]);
    }

    /**
     * @Route("/page/{offset}", name="post_index_page", methods={"GET"})
     */
    public function limitShow(PostRepository $postRepository, $offset): Response
    {
        $limit = 40 * $offset;
        $getOffset = $offset - 1;
        $realOffset = $getOffset * 40;

        $amount = $postRepository->createQueryBuilder('a')
        // Filter by some parameter if you want
        // ->where('a.published = 1')
        ->select('count(a.id)')
        ->getQuery()
        ->getSingleScalarResult();

        if($offset == 1) {
            return $this->redirectToRoute('post_index');

        }
        return $this->render('post/index.html.twig', [
            'array' =>  $postRepository->findAll(),
            'pagePage' => true,
            'currentPage' => $offset,
            'posts' => $postRepository->findBy([], null, $limit, $realOffset),
        ]);
    }

    /**
     * @Route("/new", name="post_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $post = new Post();
        // $form = $this->createForm(PostType::class, $post);
        $form = $this->createFormBuilder($post)
        ->add('Title', TextType::class)
        ->add('Body', TextareaType::class)
        ->add('Category', EntityType::class,[
            // looks for choices from this entity
            'class' => Category::class,
        
            // uses the User.username property as the visible option string
            'choice_label' => 'Name',

        ])
        ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn-success btn']])
        ->getForm();

        $form->handleRequest($request);

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $user->getId();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $post->setOP($user);
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="post_show", methods={"GET", "POST"})
     */
    public function show(ReactionRepository $reactionRepository, Post $post, $id, Request $request): Response
    {
        $user = 'none';
        $op = $this->getDoctrine()->getRepository(Post::class)->find($id);
        $op = $op->getOP()->getId();

        $limit = 15;
        $realOffset = 0;

        $disabled = true;

        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $user = $user->getId();

            $disabled = false;
        }

        $reaction = new Reaction();

        $comment = $this->createFormBuilder($reaction)
        ->add('Body', TextareaType::class, ['disabled' => $disabled])
        ->add('save', SubmitType::class, ['label' => 'Submit', 'disabled' => $disabled, 'attr' => ['class' => 'btn-success btn']])
        ->getForm();

        $comment->handleRequest($request);

        if ($comment->isSubmitted() && $comment->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $reaction->setPost($post);
            $reaction->setUser($this->container->get('security.token_storage')->getToken()->getUser());
            $entityManager->persist($reaction);
            $entityManager->flush();

            return $this->redirect($request->getUri());

        }

        return $this->render('post/show.html.twig', [
            'op' => $op,
            'user' => $user,
            'post' => $post,
            'currentPage' => 1,
            'currentPost' => $id,
            'comments' => $reactionRepository->findBy(['post' => $id], null, $limit, $realOffset),
            'commentsTotal' => $reactionRepository->findBy(['post' => $id]),
            'comment' => $comment->createView(),
        ]);
    }

    /**
     * @Route("/{id}/page/{offset}", name="post_show_reaction", methods={"GET", "POST"})
     */
    public function showReaction(ReactionRepository $reactionRepository, Post $post, $id, Request $request, $offset): Response
    {
        $user = 'none';
        $op = $this->getDoctrine()->getRepository(Post::class)->find($id);
        $op = $op->getOP()->getId();

        // $reactionRepository = $reactionRepository->find($id);

        $disabled = true;

        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $user = $user->getId();

            $disabled = false;
        }

        $reaction = new Reaction();

        $comment = $this->createFormBuilder($reaction)
        ->add('Body', TextareaType::class, ['disabled' => $disabled])
        ->add('save', SubmitType::class, ['label' => 'Submit', 'disabled' => $disabled, 'attr' => ['class' => 'btn-success btn']])
        ->getForm();

        $comment->handleRequest($request);

        if ($comment->isSubmitted() && $comment->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $reaction->setPost($post);
            $reaction->setUser($this->container->get('security.token_storage')->getToken()->getUser());
            $entityManager->persist($reaction);
            $entityManager->flush();

            return $this->redirect($request->getUri());

        }

        $limit = 15;
        $getOffset = $offset - 1;
        $realOffset = $getOffset * 15;

        return $this->render('post/show.html.twig', [
            'op' => $op,
            'user' => $user,
            'post' => $post,
            'currentPost' => $id,
            'currentPage' => $offset,
            'comments' => $reactionRepository->findBy(['post' => $id], null, $limit, $realOffset),
            'commentsTotal' => $reactionRepository->findBy(['post' => $id]),
            'comment' => $comment->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="post_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Post $post, $id): Response
    {
        // $form = $this->createForm(PostType::class, $post);
        $form = $this->createFormBuilder($post)
        ->add('Title', TextType::class)
        ->add('Body', TextareaType::class)
        ->add('Category', EntityType::class,[
            // looks for choices from this entity
            'class' => Category::class,
        
            // uses the User.username property as the visible option string
            'choice_label' => 'Name',

        ])
        ->add('save', SubmitType::class, ['label' => 'Save edit', 'attr' => ['class' => 'btn-success btn']])
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect('/post/'.$id);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'currentPost' => $id
        ]);
    }

    /**
     * @Route("/{id}", name="post_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('post_index');
    }
}
