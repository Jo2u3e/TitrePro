<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;
use App\Class\Search;
use App\Form\SearchType;
use Symfony\Component\HttpFoundation\Request;



class ProductController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }     
    


    #[Route('/nos-product', name: 'products')]
    public function index(Request $request): Response
    {
        
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $products =  $this->entityManager->getRepository(Product::class)->findWithSearch($search);
        } else {
            $products = $this->entityManager->getRepository(Product::class)->findAll();
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView()
        ]);
    }


    #[Route('/produit/{slug}', name: 'product')]
    public function show($slug)
    {

        $bestProducts = $this->entityManager->getRepository(Product::class)->findByIsBest(true);
        
        $product = $this->entityManager->getRepository(Product::class)->findOneBySlug($slug);

        if(!$product){
            return $this->redirectToRoute('product');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'bestProducts' => $bestProducts
        ]);
    }

}
