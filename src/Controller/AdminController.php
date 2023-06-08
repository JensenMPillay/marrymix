<?php

namespace App\Controller;

use App\Repository\ContactRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Entity\Order;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin', name: 'app_admin_')]
class AdminController extends AbstractController
{
    #[Route(path: '/', name: '')]
    public function index(UserRepository $userRepo, CategoryRepository $categoryRepo, ProductRepository $productRepo, ContactRepository $contactRepo, OrderRepository $orderRepo): Response
    {
        $users = $userRepo->findAll();

        $categories = $categoryRepo->findAll();

        $products = $productRepo->findAll();

        $contacts = $contactRepo->findAll();

        $orders = $orderRepo->findBy(
            ["status" => [Order::STATUS_PAYED, Order::STATUS_DELIVERY_WAITING, Order::STATUS_DELIVERED]],
        );

        $ordersPayed = $orderRepo->findBy(
            ["status" => Order::STATUS_PAYED],
        );

        $ordersWaiting = $orderRepo->findBy(
            ["status" => Order::STATUS_DELIVERY_WAITING],
        );

        $OrdersDelivered = $orderRepo->findBy(
            ["status" => Order::STATUS_DELIVERED],
        );



        return $this->render(
            'admin/index.html.twig',
            [
                "users" => count($users),
                "categories" => count($categories),
                "products" => count($products),
                "contacts" => count($contacts),
                "orders" => count($orders),
                "ordersPayed" => count($ordersPayed),
                "ordersWaiting" => count($ordersWaiting),
                "OrdersDelivered" => count($OrdersDelivered),
            ]
        );
    }
}
