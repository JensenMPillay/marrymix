<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class MenuController extends AbstractController
{
    #[Route('/menu', name: 'app_menu')]
    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository, SerializerInterface $serializer): Response
    {
        $categories = $categoryRepository->findAll();

        $products = $productRepository->findAll();
        $productsData = [];

        foreach ($products as $product) {
            $productJSON = $serializer->serialize($product, 'json', ['groups' => 'product:read']);
            $productData = json_decode($productJSON, true);
            $productData['json'] = $productJSON;
            $productsData[] = $productData;
        }

        return $this->render('menu/menu.html.twig', [
            "categories" => $categories,
            "products" => $productsData,
        ]);
    }
}
