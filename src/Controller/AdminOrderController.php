<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderUpdateStatusType;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/order', name: 'app_admin_order')]
class AdminOrderController extends AbstractController
{
    #[Route(path: '/', name: '')]
    public function order(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findBy(
            [
                'status' => [Order::STATUS_PAYED, Order::STATUS_DELIVERY_WAITING, Order::STATUS_DELIVERED]
            ],
            ['id' => 'DESC']
        );
        // View
        return $this->render("admin/admin_order/order.html.twig", ["orders" => $orders]);
    }

    #[Route(path: '/edit/{id}', name: '_edit', requirements: ['id' => '\d+'])]
    public function orderEdit(Request $request, ManagerRegistry $managerRegistry, Order $order = null): Response
    {
        $orderForm = $this->createForm(OrderUpdateStatusType::class, $order);

        // is Order ? 
        if (!$order) {
            return $this->redirectToRoute('app_admin_order');
        }

        // Handle Submit
        $orderForm->handleRequest($request);

        if ($orderForm->isSubmitted() && $orderForm->isValid()) {

            $order->setUpdatedAt(new \DateTimeImmutable());
            $manager = $managerRegistry->getManager();
            $manager->persist($order);
            $manager->flush();
            $this->addFlash('edit_order_success', "Order Updated!");
            // Redirection
            return $this->redirectToRoute('app_admin_order');
        }

        // View
        return $this->render("admin/admin_order/order_edit.html.twig", ["orderForm" => $orderForm->createView()]);
    }

    #[Route(path: '/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    public function orderDelete(ManagerRegistry $managerRegistry, Order $order = null): RedirectResponse
    {
        if ($order) {
            // Suppression
            $manager = $managerRegistry->getManager();
            $manager->remove($order);
            $manager->flush();
            $this->addFlash('delete_order_success', "Order Deleted!");
        };
        // Redirection
        return $this->redirectToRoute('app_admin_order');
    }
}
