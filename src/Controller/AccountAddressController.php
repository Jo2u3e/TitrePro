<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AddressType;
use App\Entity\Address;


class AccountAddressController extends AbstractController
{


    #[Route('/compte/adresses', name: 'account_address')]
    public function index(): Response
    {
        return $this->render('account/address.html.twig');
    }

    #[Route('/compte/ajouter-une-adresse', name: 'account_address_add')]
    public function add(): Response
    {
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        return $this->render('account/add_address.html.twig', [
            'form' => $form->createView()
        ]);
    }


}



