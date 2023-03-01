<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


/**
 * @Route("/admin/user", name="app_admin_user")
 */
class AdminUserController extends AbstractController
{
    /**
     * @Route("/", name="")
     */
    public function user(UserRepository $userRepo)
    {
        $users = $userRepo->findAll();
        // View
        return $this->render("admin/admin_user/user.html.twig", ["users" => $users]);
    }

    /**
     * @Route("/create", name="_create")
     */
    public function userCreate(User $user = null, Request $request, ManagerRegistry $managerRegistry, UserPasswordHasherInterface $userPasswordHasher, FormFactoryInterface $formFactory)
    {
        $user = new User;

        // Form
        $form = $formFactory->createNamed('userForm', UserType::class, $user);

        // Handle Submit
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Date & Encode Password
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Role
            // $user->setRoles(array($form->get('role')->getData()));

            // Traitement BDD
            $manager = $managerRegistry->getManager();
            $manager->persist($user);
            $manager->flush();

            // Msg Flash
            $this->addFlash('add_user_success', "User Created!");

            // Redirection
            return $this->redirectToRoute('app_admin_user');
        }

        // View
        // If AJAX
        if ($request->isXmlHttpRequest()) {
            $html = $this->renderView("admin/_partials/userForm.html.twig", [
                'userForm' => $form->createView(),
                'edit' => false,
            ]);
            return new JsonResponse(['html' => $html]);
        }
        // If Others
        else {
            return $this->render("admin/admin_user/user_create_edit.html.twig", [
                'userForm' => $form->createView(),
                'edit' => false,
            ]);
        }
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     */
    public function userEdit(User $user = null, Request $request, ManagerRegistry $managerRegistry, FormFactoryInterface $formFactory)
    {
        // is User ? 
        if (!$user) {
            return $this->redirectToRoute('app_admin_user_create');
        }

        // Form
        $form = $formFactory->createNamed('userForm', UserType::class, $user);

        // Unable Password Changing by the Admin
        $form->remove('plainPassword');
        $form->remove('confirmPassword');

        // Handle Submit
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Date & Encode Password
            $user->setUpdatedAt(new \DateTimeImmutable());

            // Role
            // $user->setRoles(array($form->get('role')->getData()));

            // Traitement BDD
            $manager = $managerRegistry->getManager();
            $manager->persist($user);
            $manager->flush();

            // Msg Flash
            $this->addFlash('edit_user_success', "User Updated!");

            // Redirection
            return $this->redirectToRoute('app_admin_user');
        }

        // View
        // If AJAX
        if ($request->isXmlHttpRequest()) {
            $html = $this->renderView("admin/_partials/userForm.html.twig", [
                'userForm' => $form->createView(),
                'edit' => true,
            ]);
            return new JsonResponse(['html' => $html]);
        }
        // If Others
        else {
            return $this->render("admin/admin_user/user_create_edit.html.twig", [
                'userForm' => $form->createView(),
                'edit' => true,
            ]);
        }
    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     */
    public function userDelete(ManagerRegistry $managerRegistry, User $user = null)
    {
        if ($user) {
            // Suppression
            $manager = $managerRegistry->getManager();
            $manager->remove($user);
            $manager->flush();
            $this->addFlash('delete_user_success', "User Deleted!");
        };
        // Redirection
        return $this->redirectToRoute('app_admin_user');
    }
}
