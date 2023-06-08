<?php

namespace App\Controller;

use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CartType;
use App\Form\DeliveryType;
use App\Manager\CartManager;
use App\Service\LocationService;
use DateTimeImmutable;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function cart(CartManager $cartManager, Request $request): Response
    {
        $cart = $cartManager->getCurrentCart();
        $form = $this->createForm(CartType::class, $cart);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cartManager->save($cart);

            // Msg Flash
            $this->addFlash('save_cart_success', "Your cart has been successfully updated!");

            return $this->redirectToRoute('app_cart');
        }

        return $this->render('cart/cart.html.twig', [
            'cart' => $cart,
            'form' => $form->createView()
        ]);
    }

    #[Route('/delivery-information', name: 'app_delivery_information')]
    public function delivery_information(CartManager $cartManager, Request $request, LocationService $locationService): Response
    {

        $cart = $cartManager->getCurrentCart();
        $form = $this->createForm(DeliveryType::class, $cart);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (
                isset($_POST) &&
                array_key_exists('delivery', $_POST) &&
                array_key_exists('latitude_address', $_POST['delivery']) &&
                array_key_exists('longitude_address', $_POST['delivery'])
            ) {

                // Date 
                $datePOST = $_POST['delivery']['delivery_date'];

                $date = DateTimeImmutable::createFromFormat('Y-m-d', $datePOST);

                if ($date instanceof DateTimeImmutable) {
                    $cart->setDeliveryDate($date);
                } else {
                    $this->addFlash('danger', "Please select a date");
                    return $this->redirectToRoute('app_delivery_information');
                }

                // Address Behavior
                define('COUNTRY', "France");
                $addressCustomer = $form->getData()->getDeliveryAddress();

                if (isset($addressCustomer) && !str_contains($addressCustomer, COUNTRY)) {
                    $this->addFlash('danger', "Delivery is only in " . COUNTRY);
                    return $this->redirectToRoute('app_delivery_information');
                }

                // Shipping Fees & Calcul Distance & Duration With Coordinates
                $latitude_address = $_POST['delivery']['latitude_address'];
                $longitude_address = $_POST['delivery']['longitude_address'];
                if (!$latitude_address || !$longitude_address) {
                    $routingInfo = ['distance' => 0, 'duration' => 0];
                } else {
                    $coordinatesCustomer = [
                        "lat" => $latitude_address,
                        "lng" => $longitude_address,
                    ];
                    $routingInfo = $locationService->getDistanceAndDurationFromShopToCustomer($coordinatesCustomer);
                }

                $cart->setDistance($routingInfo['distance']);

                $shippingFees = $locationService->calculateShippingFeesFromDistance($routingInfo);

                $shippingFeesRounded = round($shippingFees, 2);

                $cart->setShippingFees($shippingFeesRounded);

                // Update Cart
                $cartManager->save($cart);

                return $this->redirectToRoute('app_checkout');
            }
        }

        return $this->render('cart/delivery-information.html.twig', [
            'cart' => $cart,
            'form' => $form->createView()
        ]);
    }

    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(CartManager $cartManager, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_USER');

        $user = $this->getUser();

        if ($user) {

            $cart = $cartManager->getCurrentCart();

            // Link Order to the User Connected
            $cart->setUser($user);
            $cart->setStatus(Order::STATUS_CHECKOUT);
            $cartManager->save($cart);

            // Link Address to the User Connected if is empty
            $addressOrder = $cart->getDeliveryAddress();

            $parts = explode(", ", $addressOrder);

            /**
             * @var $user User
             */
            if (count($parts) === 4 && $user instanceof User) {

                $userAddress = $user->getAddress();
                $userCity = $user->getCity();
                $userPostalCode = $user->getPostalCode();

                if (!($userAddress) && !($userCity) && !($userPostalCode)) {
                    $address = $parts[0];
                    $city = $parts[1];
                    $postalCode = $parts[2];
                    $user->setAddress($address);
                    $user->setCity($city);
                    $user->setPostalCode($postalCode);
                    $entityManager->persist($user);
                }
            }

            $entityManager->flush();

            return $this->render('cart/checkout.html.twig', [
                'cart' => $cart,
            ]);
        }
        return $this->redirectToRoute('app_login');
    }
}
