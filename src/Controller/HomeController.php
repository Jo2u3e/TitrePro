<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Entity\Header;
use Doctrine\ORM\EntityManagerInterface;


class HomeController extends AbstractController
{


    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        
        $bestProducts = $this->entityManager->getRepository(Product::class)->findByIsBest(true);
        $headers = $this->entityManager->getRepository(Header::class)->findAll();
        

        return $this->render('home/index.html.twig',[
            'bestProducts' => $bestProducts,
            'headers' => $headers
        ]);
    }
}
