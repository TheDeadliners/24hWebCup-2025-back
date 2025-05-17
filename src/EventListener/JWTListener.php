<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTListener
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        $payload = $event->getData();
        if ($user instanceof User) {
            $payload["firstname"] = $user->getFirstname();
            $payload["lastname"] = $user->getLastname();
            $payload["created_at"] = $user->getCreatedAt()->getTimestamp();
        }

        $event->setData($payload);
    }
}
