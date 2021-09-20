<?php

namespace App\EventSubscriber;


use App\Entity\Product;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use ReflectionClass;


class EasyAdminSubscriber implements EventSubscriberInterface
{

    private $appKernel;

    public function __construct(KernelInterface $appKernel)
    {
        $this->appKernel = $appKernel;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['setIllustration'],
            
        ];
    }



    public function setIllustration(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();
        $entityName = (new ReflectionClass($entity))->getShortName();
        $tmp_name = $entity->getIllustration();
        $fileName = uniqid();
        $extension = pathinfo($_FILES[$entityName]['name']['illustration'], PATHINFO_EXTENSION);
        $project_dir = $this->appKernel->getProjectDir();
        move_uploaded_file($tmp_name, $project_dir.'/'. $fileName.'.'.$extension);
        $entity->setIllustration($fileName);
    }

   

   
}