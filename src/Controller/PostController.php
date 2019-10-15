<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Category;
use App\Entity\Reaction;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\ReactionRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\{TextType,ButtonType,EmailType,HiddenType,PasswordType,TextareaType,SubmitType,NumberType,DateType,MoneyType,BirthdayType,FileType,ChoiceType};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Vich\UploaderBundle\Form\Type\VichFileType;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository, CategoryRepository $categoryRepository): Response
    {
        // Set limit of post amount
        $limit = 40;
        $realOffset = 0;

        //Default post submit success
        $success = 0;

        // If post is successfully submitted, show messege
        if(isset($_GET['success'])) {
            // echo($_GET['success']);
            $success = $_GET['success'];
        }

        // Login check
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $userId = $user->getId();

            
            if($user->getId() == 1 && $user->getRoles()[0] != "ROLE_ADMIN") {
                $user->setRoles(['ROLE_ADMIN']);
                $user->setAllowed('1');
                $this->getDoctrine()->getManager()->flush();
            }
            else if($user->getId() == 1 && $user->getRoles()[0] == "ROLE_ADMIN" && $user->getAllowed() == 0) {
                $user->setAllowed('1');
                $this->getDoctrine()->getManager()->flush();
            }
            
            //is he allowed?
            $userAllow = $user->getAllowed();

            if($userAllow == 0 ) {
                $this->get('security.token_storage')->setToken(null);
                // $request->getSession()->invalidate(1);
        
                return $this->redirect('/exit');
            } 
        }

        return $this->render('post/index.html.twig', [
            'success' => $success,
            'array' =>  $postRepository->findBy(['allowed' => 1]),
            'pagePage' => true,
            'currentPage' => 1,
            'categories' => $categoryRepository->findAll(),
            'posts' => $postRepository->findBy(['allowed' => 1], null, $limit, $realOffset),
        ]);
    }

        
    /**
     * @Route("/allow", name="post_allow", methods={"GET","POST"})
     */
    public function allow(PostRepository $postRepository, Request $request): Response
    {   
        $form = $this->createFormBuilder()
        ->add('Reason', TextareaType::class)
        ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn-success btn']])
        ->getForm();

        return $this->render('post/allow.html.twig', [
            'posts' => $postRepository->findAll(),
            'form' => $form->createView()
        ]);
    }
        
    /**
     * @Route("/allow/{yesOrYes}", defaults={"offset"=1}, name="post_allow_allowed", methods={"GET","POST"})
     */
    public function allowAllowed(PostRepository $postRepository, Request $request, $yesOrYes): Response
    {   
        $form = $this->createFormBuilder()
        ->add('Reason', TextareaType::class)
        ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn-success btn']])
        ->getForm();

        return $this->render('post/allow.html.twig', [
            'posts' => $postRepository->findBy(['allowed' => $yesOrYes]),
            'form' => $form->createView(),
            'current' => $yesOrYes
        ]);
    }

    /**
     * @Route("/reason/{yesOrYes}", defaults={"yesOrYes"=2}, name="post_reason", methods={"GET","POST"})
     */
    public function reason(PostRepository $postRepository, Request $request, $yesOrYes): Response
    {   
        
        $search = $postRepository->findBy(['allowed' => $yesOrYes]);
        
        if($yesOrYes == 2) {
            $search = $postRepository->findAll();
        }
        return $this->render('post/plebs.html.twig', [
            'posts' => $search,
            'current' => $yesOrYes
        ]);
    }

    /**
     * @Route("/allow/{id}/{value}", name="post_allow_update", methods={"GET","POST"})
     */
    public function allowUpdate(PostRepository $postRepository, Request $request, Post $post, $id, $value): Response
    {        
        $entityManager = $this->getDoctrine()->getManager();
        $post = $entityManager->getRepository(Post::class)->find($id);        

        if($value == 'true') {
            $value = 1;
        } else {
            $value = 0;
        }

        $post->setAllowed($value);

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('post_allow');

    }

    /**
     * @Route("/allow/reason/{id}/", name="post_allow_reason", methods={"GET","POST"})
     */
    public function allowReason(PostRepository $postRepository, Request $request, Post $post, $id): Response
    {        
        $entityManager = $this->getDoctrine()->getManager();
        $post = $entityManager->getRepository(Post::class)->find($id);        

        $post->setReason($request->request->get('_reason'));

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('post_allow');

    }

    /**
     * @Route("/page/{offset}", defaults={"offset"=1}, name="post_index_page", methods={"GET"})
     */
    public function limitShow(PostRepository $postRepository, $offset, CategoryRepository $categoryRepository): Response
    {
        // Set post limit
        $limit = 40 * $offset;
        $getOffset = $offset - 1;
        $realOffset = $getOffset * 40;

        // If page is 1 then just go to /post
        if($offset == 1) {
            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/index.html.twig', [
            'array' =>  $postRepository->findBy(['allowed' => 1]),
            'success' => false,
            'pagePage' => true,
            'currentPage' => $offset,
            'categories' => $categoryRepository->findAll(),
            'posts' => $postRepository->findBy(['allowed' => 1], null, $limit, $realOffset),
        ]);
    }

    /**
     * @Route("/category/{id}", name="post_category", methods={"GET"})
     */
    public function category(PostRepository $postRepository, CategoryRepository $categoryRepository, $id): Response
    {
        // Limit post amount
        $limit = 40;
        $realOffset = 0;

        return $this->render('post/index.html.twig', [
            'array' =>  $postRepository->findBy(['allowed' => 1]),
            'success' => false,
            'pagePage' => true,
            'currentPage' => 1,
            'categories' => $categoryRepository->findAll(),
            'posts' => $postRepository->findBy(['category' => $id, 'allowed' => 1], null, $limit, $realOffset),
        ]);
    }

    /**
     * @Route("/new", name="post_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createFormBuilder($post)
        ->add('Title', TextType::class)
        ->add('Body', TextareaType::class)
        ->add('imageFile', VichFileType::class, [
            'label' => 'Brochure (PDF file)',
            'required'   => false,
        ])
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
            $post->setAllowed(0);
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('post_index', [
                'success' => true,
            ]);
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
        ->add('save', SubmitType::class, ['label' => 'Submit', 'disabled' => $disabled, 'attr' => ['class' => 'btn-success btn btn-raised']])
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
     * @Route("/{id}/page/{offset}", defaults={"offset"=1}, name="post_show_reaction", methods={"GET", "POST"})
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
        ->add('save', SubmitType::class, ['label' => 'Submit', 'disabled' => $disabled, 'attr' => ['class' => 'btn-success btn btn-raised']])
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
        ->add('imageFile', VichFileType::class, [
            'label' => 'Brochure (PDF file)',
            'required'   => false,
        ])
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
