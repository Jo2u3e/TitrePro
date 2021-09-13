<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Class\Cart;
use App\Entity\Order;
use App\Form\OrderType;
use Doctrine\ORM\EntityManagerInterface;

class OrderController extends AbstractController
{
     
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    

    #[Route('/commande', name: 'order')]
    public function index(Cart $cart): Response
    {

       
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);


        return $this->render('order/index.html.twig',[
            'form' => $form->createView(),
            'cart' => $cart->getFull()
        ]);
    }

     

    
    #[Route('/commande/recapitulatif', name: 'order_recap')]
    public function add(Cart $cart, Request $request): Response
    {

       
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);
        

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $carrier = $form->get('carriers')->getData();
            $delivery = $form->get('addresses')->getData();
            // $delivery_content = sprintf(
            //     '%s %s <br> %s %s <br> %s <br> %s %s <br> %s',
            //     $delivery->getFirstName(),
            //     $delivery->getLastname(),
            //     $delivery->getPhone(),
            //     $delivery->getCompany() ? sprintf('<br>%s', $delivery->getCompany()) : '',
            //     $delivery->getAddress(),
            //     $delivery->getPostal(),
            //     $delivery->getCity(),
            //     $delivery->getCountry()
            // );

            $delivery_address = sprintf(
                '%s %s <br> %s <br> %s %s',
                $delivery->getFirstName(),
                $delivery->getLastname(),
                $delivery->getAddress(),
                $delivery->getPostal(),
                $delivery->getCity()
            );
            $dayDate = new \DateTime();
            $order = new Order();
            $order->setReference(sprintf('%s-%s', $dayDate->format('dmY'), uniqid()))
                ->setUser($this->getUser())
                ->setCreatedAt($dayDate)
                ->setCarrierName($carrier->getName())
                ->setCarrierPrice($carrier->getPrice())
                ->setDelivery($delivery_address)
                ->setState(0);

            $this->entityManager->persist($order);

            foreach ($cart->getFull() as $product) {
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order)
                    ->setProduct($product['product']->getName())
                    ->setQuantity($product['quantity'])
                    ->setPrice($product['product']->getPrice())
                    ->setTotal($product['product']->getPrice() * $product['quantity']);

                $this->entityManager->persist($orderDetails);
            }

            $this->entityManager->flush();

            return $this->render('order/add.html.twig', [
                'cart' => $cart->getFull(),
                'carrier' => $carrier,
                'delivery_address' => $delivery_address,
                'reference' => $order->getReference()
            ]);
        }
        return $this->redirectToRoute('cart');

    }



}