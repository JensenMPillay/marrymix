<?php

namespace App\Controller;

use App\Entity\Order;
use App\Manager\CartManager;
use App\Repository\OrderRepository;
use App\Service\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;


#[Route('/payment/', name: 'app_payment_')]
class PaymentController extends AbstractController
{
    readonly private string $stripeSecretKey;


    public function __construct()
    {
        $this->stripeSecretKey = $_ENV['API_STRIPE_KEY'];
        Stripe::setApiKey($this->stripeSecretKey);
        Stripe::setApiVersion('2022-11-15');
    }

    #[Route('stripe', name: 'stripe')]
    public function index(CartManager $cartManager, UrlGeneratorInterface $generator): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_USER');

        $cart = $cartManager->getCurrentCart();

        if (!($cart->getStatus() === Order::STATUS_CHECKOUT)) {
            return $this->redirectToRoute('app_cart');
        }

        // List Products for Stripe
        $items = $cart->getItems();
        $productsStripe = [];
        foreach ($items as $item) {
            $product = $item->getProduct();
            $productsStripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPrice() * 100,
                    'product_data' => [
                        'name' => $product->getName(),
                    ]
                ],
                'quantity' => $item->getQuantity(),
            ];
        };

        // Shipping Fees for Stripe
        $productsStripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $cart->getShippingFees() * 100,
                'product_data' => [
                    'name' => 'Shipping Fees',
                ]
            ],
            'quantity' => 1,
        ];

        $checkout_session = Session::create([
            'customer_email' => $cart->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [$productsStripe],
            'mode' => 'payment',
            'success_url' =>  $generator->generate('app_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $generator->generate('app_payment_error', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        $cart->setPaymentId($checkout_session->id);

        $cartManager->save($cart);

        return new RedirectResponse($checkout_session->url);
    }

    #[Route('success', name: 'success')]
    public function success(CartManager $cartManager, FileService $fileService, MailerInterface $mailer): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_USER');

        $cart = $cartManager->getCurrentCart();

        // Retrieve Session from Stripe Session Id
        $stripeSession = Session::retrieve($cart->getPaymentId());

        // Retrieve Payment Id from Session
        $paymentIntentId = $stripeSession->payment_intent;

        // Retrieve Status from PaymentIntent
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        $paymentStatus = $paymentIntent->status;

        // NOT PAYMENT SUCCESS STATUS
        if (!$paymentStatus == "succeeded") {
            $this->addFlash('danger', "An error has occurred during the payment, please try again");
            return $this->redirectToRoute('app_cart');
        }

        // Update Status
        $cart->setStatus(Order::STATUS_PAYED);
        $cartManager->save($cart);

        // Send Email with Invoice
        $user = $this->getUser();
        $invoicePdf = $fileService->generateInvoicePdf($cart);

        // Mail
        /**
         * @var User $user
         */
        $email = (new TemplatedEmail())
            ->from(new Address('marrymixcocktails@gmail.com', 'MarryMix Bot'))
            ->to($user->getEmail())
            ->subject('MarryMix - Order Confirmation')
            ->htmlTemplate('mail/confirmation_order.html.twig')
            ->textTemplate('mail/confirmation_order.txt.twig')
            ->attachFromPath($invoicePdf, 'InvoiceMarryMix.pdf', 'application/pdf');

        // Send 
        $mailer->send($email);

        // Delete invoicePdf
        if (file_exists($invoicePdf)) {
            unlink($invoicePdf);
        }

        $this->addFlash('success', "Your order has been successfully completed. A confirmation email has been sent to your email address.");

        return $this->redirectToRoute('index');
    }

    #[Route('error', name: 'error')]
    public function error(): RedirectResponse
    {
        $this->addFlash('danger', "An error has occurred during the payment, please try again");
        return $this->redirectToRoute('app_cart');
    }

    #[Route('invoice/{payment_id}', name: 'invoice_pdf', requirements: ['payment_id' => '.+'])]
    public function downloadInvoiceOrder(string $payment_id, OrderRepository $orderRepo, FileService $fileService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_USER');

        $order = $orderRepo->findOneBy(['payment_id' => $payment_id]);

        if (!$order) {
            throw $this->createNotFoundException('Order not found.');
        }

        return $fileService->downloadInvoicePdf($order);
    }
}
