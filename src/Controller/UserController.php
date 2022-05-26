<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Form\UserType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{   
    /**
     * @Route("/user", name="app_user")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(User::class);
        $users = $repository->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }
    /**
     * @Route("/addUser", name="add_user")
     */
    public function addUser(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(userType::class, $user);
        $form->handleRequest($request);
        $tokenValue = $request->request->get('inputToken_add');
        if ($form->isSubmitted() && $form->isValid() && $this->isCsrfTokenValid('add-item', $tokenValue)) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/addUser.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     *  @Route("/delete", name="delete_user")
     */
    public function delete(Request $request, ManagerRegistry $doctrine){
        $tokenValue = $request->request->get('inputToken_delete');
        if ($request->isMethod('POST') && $this->isCsrfTokenValid('delete-item', $tokenValue)) {
            $user = new User();
      
            $id = $request->request->get('id');
    
            $entityManager = $doctrine->getManager();
            $user = $entityManager->getRepository(User::class)->find($id);

            $entityManager->remove($user);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_user');
        }   
        
        else {
            return $this->redirectToRoute('app_user');
        }
    }
}
