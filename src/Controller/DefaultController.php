<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(Request $request)
    {
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

        return $this->redirect($this->generateUrl('post_index'));  
    }

    /**
     * @Route("/exit", name="exit")
     */
    public function exit() 
    {
        return $this->render('default/logingout.html.twig');
    }
}
