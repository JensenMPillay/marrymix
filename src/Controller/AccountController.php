<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Entity\User;
use App\Form\UserType;

#[Route('/account', name: 'app_account_')]
class AccountController extends AbstractController
{
    #[Route('/', name: 'my')]
    public function index(): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_USER');

        $user = $this->getUser();

        return $this->render('account/account_my.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/edit', name: 'my_edit')]
    public function accountEdit(Request $request, EntityManagerInterface $entityManager, FormFactoryInterface $formFactory, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_USER');

        /**
         * @var User|PasswordAuthenticatedUserInterface $user
         */
        $user = $this->getUser();
        // is User ? 
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Form
        $form = $formFactory->createNamed('userForm', UserType::class, $user);

        // Handle Submit
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Date & Encode Password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setUpdatedAt(new \DateTimeImmutable());

            $lastnameFormatted = strtoupper($form->get('lastName')->getData());

            $user->setLastName($lastnameFormatted);

            // Traitement BDD
            $entityManager->persist($user);
            $entityManager->flush();

            // Msg Flash
            $this->addFlash('edit_account_success', "Your account has been updated.");

            // Redirection
            return $this->redirectToRoute('app_account_my');
        }
        return $this->render("account/account_my_edit.html.twig", [
            'userForm' => $form->createView(),
        ]);
    }

    #[Route('/orders', name: 'orders', requirements: ['id' => '\d+'])]
    public function orders(): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_USER');

        return $this->render('account/orders.html.twig', []);
    }

    #[Route('/order/{id}', name: 'order', requirements: ['id' => '\d+'])]
    public function order(Order $order = null): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_USER');

        $user = $this->getUser();

        /**
         * @var User|PasswordAuthenticatedUserInterface $user
         */
        if (!$order || !in_array($order, $user->getOrders()->toArray())) {
            $this->addFlash('danger', 'Order was not found.');
            return $this->redirectToRoute('index');
        }

        return $this->render('account/order.html.twig', [
            'order' => $order,
        ]);
    }
}
