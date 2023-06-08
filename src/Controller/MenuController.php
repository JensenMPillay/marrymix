<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Manager\CartManager;

#[Route('/menu', name: 'app_menu_')]
class MenuController extends AbstractController
{
    #[Route('/', name: 'menu')]
    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository, SerializerInterface $serializer): Response
    {
        $categories = $categoryRepository->findAll();

        $products = $productRepository->findAll();

        return $this->render('menu/menu.html.twig', [
            "categories" => $categories,
            "products" => $products,
        ]);
    }

    #[Route(path: '/add-to-cart/{id}', name: 'add-to-cart', requirements: ['id' => '\d+'])]
    public function product(Request $request,  CartManager $cartManager, Product $product = null): JsonResponse
    {
        // is Product ? 
        if (!$product) {
            return $this->redirectToRoute('app_menu_menu');
        }

        $item = new OrderItem();
        $item->setProduct($product);
        $item->setQuantity(1);
        $cart = $cartManager->getCurrentCart();
        $cart->addItem($item);
        $cartManager->save($cart);

        return new JsonResponse([
            'code' => 'ITEM_ADDED_SUCCESSFULLY',
            'id' => $product->getId()
        ]);
    }
}
