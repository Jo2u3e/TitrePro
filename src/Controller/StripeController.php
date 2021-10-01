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
use Symfony\Component\HttpFoundation\JsonResponse;

class StripeController extends AbstractController
{
    #[Route('/commande/create-session/{reference}', name: 'stripe_create_session', methods:"POST")]
    public function index(Cart $cart, $reference, EntityManagerInterface $entityManager): Response
    {
        

        $YOUR_DOMAIN = 'http://localhost:8000';


        $productsForStripe = [];
        //(new Dotenv())->bootEnv(dirname(__DIR__).'/../.env');
      //  $DOMAIN = $_ENV['DOMAIN'];

        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);

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
                        'images' => [$YOUR_DOMAIN.'/uploads/'. $entityManager->getRepository(Product::class)->findOneByName($product->getProduct())->getIllustration()],
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
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionID($checkout_session->id);
        $entityManager->flush();


        

        return $this->json(['id' => $checkout_session->id]);
    }
}
