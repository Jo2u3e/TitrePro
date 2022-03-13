<?php

namespace App\Controller;

use App\Class\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
         $this->entityManager = $entityManager;
    }



    #[Route('/inscription', name: 'register')]
    public function index(Request $request, UserPasswordEncoderInterface $encoder): Response
    {

        $user = new User();
        $form = $this->createForm(type: RegisterType::class);
        $notification = null;

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user = $form->getData();

            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if ($search_email){
                $notification = 'Cette adresse mail existe déjà !';
            } else{
                $password = $encoder->encodePassword($user,$user->getPassword());
                $user->setPassword($password);
               
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $mail = new Mail();
                $content = 'Bonjour  <br> Merci pour votre inscription '. $user->getFirstName();
                $mail->send(
                    $user->getEmail(),
                    $user->getFirstName(), $user->getLastName(),
                    'Bienvenue sur le site "Gwada Boutik"',
                    $content
                );

                $notification = 'Félicitation ! Vous êtes désormais inscrit !';
                return $this->redirectToRoute('app_login');
            }
           
        }

        return $this->render('register/index.html.twig', [
            'controller_name' => 'RegisterController',
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}
