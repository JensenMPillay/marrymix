<?php

namespace App\Storage;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\SecurityBundle\Security as Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
// use Symfony\Component\Security\Core\Security;

class CartSessionStorage
{
    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * The cart repository.
     *
     * @var OrderRepository
     */
    private OrderRepository $cartRepository;

    /**
     * To Get User.
     *
     * @var Security
     */
    private Security $security;

    /**
     * @var string
     */
    const CART_KEY_NAME = 'cart_id';

    /**
     * CartSessionStorage constructor.
     *
     * @param RequestStack $requestStack
     * @param OrderRepository $cartRepository
     * @param Security $security
     */
    public function __construct(RequestStack $requestStack, OrderRepository $cartRepository, Security $security)
    {
        $this->requestStack = $requestStack;
        $this->cartRepository = $cartRepository;
        $this->security = $security;
    }

    /**
     * Gets the cart in session.
     *
     * @return Order|null
     */
    public function getCart(): ?Order
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();

        if ($user) {
            $cart = $this->cartRepository->findOneBy(
                [
                    'user' => $user,
                    'status' => [Order::STATUS_CART, Order::STATUS_CHECKOUT]
                ],
                ['createdAt' => 'DESC']
            );
            if ($cart) {
                $this->setCart($cart);
                return $cart;
            }
        }

        return $this->cartRepository->findOneBy(
            [
                'id' => $this->getCartId(),
                'status' => [Order::STATUS_CART, Order::STATUS_CHECKOUT]
            ]
        );
    }

    /**
     * Sets the cart in session.
     *
     * @param Order $cart
     */
    public function setCart(Order $cart): void
    {
        $this->getSession()->set(self::CART_KEY_NAME, $cart->getId());
    }

    /**
     * Returns the cart id.
     *
     * @return int|null
     */
    private function getCartId(): ?int
    {
        return $this->getSession()->get(self::CART_KEY_NAME);
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getCurrentRequest()->getSession();
    }
}
