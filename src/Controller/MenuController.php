<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Manager\CartManager;

#[Route('/menu', name: 'app_menu_')]
class MenuController extends AbstractController
{
    #[Route('', name: 'menu')]
    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
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

    // For Search Bar 
    #[Route('/search/products', name: 'search_products_api')]
    public function searchBar(Request $request, ProductRepository $productRepository): Response
    {
        // If no Ajax Request Component, Redirect
        $searchTerm = $request->query->get('term');
        if (!$searchTerm) {
            return $this->redirectToRoute('index');
        }

        // Separate words from request
        $searchTermArray = explode(" ", $searchTerm);

        $productsFoundIds = [];

        // Initialize Result from 1st Request & compare Ids with others to Intersect 
        foreach ($searchTermArray as $searchWord) {
            $productsWordFound = $productRepository->findBySearchTerm($searchWord);

            if (empty($productsFoundIds)) {
                $productsFoundIds = $this->extractProductIds($productsWordFound);
            } else {
                $productsWordIds = $this->extractProductIds($productsWordFound);
                $productsFoundIds = array_intersect($productsFoundIds, $productsWordIds);
            }
        }

        // Find by Ids Result Intersection 
        $productsFound = $productRepository->findBy(["id" => $productsFoundIds]);

        return $this->render('search/products_search_navbar.html.twig', [
            'productsFound' => $productsFound,
            'searchTerm' => $searchTerm,
        ]);
    }

    // For Search Page  
    #[Route('/search/products/results/{searchTerm}', name: 'search_products_results', requirements: ['searchTerm' => '.+'])]
    public function searchPage(Request $request, ProductRepository $productRepository): Response
    {
        $searchTerm = $request->attributes->get('searchTerm');

        return $this->render('search/search_page.html.twig', [
            'searchTerm' => $searchTerm,
        ]);
    }

    // For Search Result Page 
    #[Route('/search/products/results', name: 'search_products_results_api')]
    public function searchPageResults(Request $request, ProductRepository $productRepository): Response
    {
        // If no Ajax Request Component, Redirect
        $searchTerm = $request->query->get('term');
        if (!$searchTerm) {
            return $this->redirectToRoute('index');
        }

        if ($searchTerm == "All") {
            return $this->render('search/products_search_page.html.twig', [
                'productsFound' => $productRepository->findAll(),
                'searchTerm' => $searchTerm,
            ]);
        };

        // Separate words from request
        $searchTermArray = explode(" ", $searchTerm);

        $productsFoundIds = [];

        // Initialize Result from 1st Request & compare Ids with others to Intersect 
        foreach ($searchTermArray as $searchWord) {
            $productsWordFound = $productRepository->findBySearchTerm($searchWord);

            if (empty($productsFoundIds)) {
                $productsFoundIds = $this->extractProductIds($productsWordFound);
            } else {
                $productsWordIds = $this->extractProductIds($productsWordFound);
                $productsFoundIds = array_intersect($productsFoundIds, $productsWordIds);
            }
        }

        // Find by Ids Result Intersection 
        $productsFound = $productRepository->findBy(["id" => $productsFoundIds]);

        return $this->render('search/products_search_page.html.twig', [
            'productsFound' => $productsFound,
            'searchTerm' => $searchTerm,
        ]);
    }

    private function extractProductIds(array $products): array
    {
        $productIds = [];

        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        return $productIds;
    }
}
