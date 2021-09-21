<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use App\Class\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Stripe\Checkout\Session;
use Symfony\Component\Dotenv\Dotenv;
use Doctrine\ORM\EntityManagerInterface;

class StripeController extends AbstractController
{
    #[Route('/commande/create-session', name: 'stripe_create_session')]
    public function index(Cart $cart, $reference, EntityManagerInterface $em): Response
    {
        
        $productsForStripe = [];
        (new Dotenv())->bootEnv(dirname(__DIR__).'/../.env');
        $DOMAIN = $_ENV['DOMAIN'];

        $order = $em->getRepository(Order::class)->findOneByReference($reference);

        if(!$order){
            return new JsonResponse(['error' => 'order']);
        }

        foreach ($order->getOrderDetails()->getValues() as $product) {

            $productsForStripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => [sprintf('%s/uploads/%s', $DOMAIN, $em->getRepository(Product::class)->findOneByName($product->getProduct())->getIllustration())],
                    ],
                ],
                'quantity' => $product->getQuantity(),
            ];
        }

        $productsForStripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                    'name' => $order->getCarrierName()
                ],
            ],
            'quantity' => 1,
        ];

        Stripe::setApiKey('sk_test_51JbnQcKr8GgznZLd4G0XdSfrQAoRl1j2Du1t6ikQPKovj0eQ3ucvdgFT1f0quMov7fz2pnELbsnj1cOl51rP7HIn008VxjZRpw');

        $checkout_session = Session::create([
            'customer_email' =>$this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [$productsForStripe],
            'mode' => 'payment',
            'success_url' => $DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionID($checkout_session->id);
        $em->flush();

        return new JsonResponse(['id' => $checkout_session->id]);
    }
}
