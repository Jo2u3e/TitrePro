<?php

namespace App\Controller;


use App\Class\Cart;
use App\Entity\Order;
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



        
        return $this->render('order_success/index.html.twig',[
            'order' => $order
        ]);
    }
}
