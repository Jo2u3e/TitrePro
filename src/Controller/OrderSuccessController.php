<?php

namespace App\Controller;


use App\Class\Cart;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{

    private $entityManager;

    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/commande/merci/{stripeSessionId}', name: 'order_success')]
    public function index($stripeSessionId, Cart $cart): Response
    {
        
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);
        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');

        }
        if ($order->getState() === 0) {
            $order->setState(1);
            $this->entityManager->flush();
            $cart->remove();

            $mail = new Mail();
            $content = sprintf('Bonjour %s <br> Merci pour votre commande.', $order->getUser()->getFirstName());
            $mail->send(
                $order->getUser()->getEmail(),
                sprintf('%s %s', $order->getUser()->getFirstName(), $order->getUser()->getLastName()),
                'Commande validée - "La boutique"',
                $content
            );
        }

        
        return $this->render('order_success/index.html.twig',[
            'order' => $order
        ]);
    }
}
