<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ProductRepository;
use App\Form\AddToCartType;
use App\Manager\CartManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/front/', name: 'app_front_')]
class FrontProductController extends AbstractController
{
    #[Route(path: 'products/', name: 'products')]
    public function products(ProductRepository $productRepository)
    {
        $products = $productRepository->findAll();
        // View
        return $this->render("front_product/products.html.twig", ["products" => $products]);
    }

    #[Route(path: 'product/{id}', name: 'product', requirements: ['id' => '\d+'])]
    public function product(Request $request,  CartManager $cartManager, Product $product = null)
    {
        // is Product ? 
        if (!$product) {
            return $this->redirectToRoute('app_front_products');
        }

        $form = $this->createForm(AddToCartType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();
            $item->setProduct($product);

            $cart = $cartManager->getCurrentCart();
            $cart
                ->addItem($item)
                ->setUpdatedAt(new \DateTimeImmutable());

            $cartManager->save($cart);

            return $this->redirectToRoute('app_front_product', ['id' => $product->getId()]);
        }

        // View
        return $this->render("front_product/product.html.twig", [
            "product" => $product,
            'form' => $form->createView(),
        ]);
    }
}
