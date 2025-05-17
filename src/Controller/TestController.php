<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/test', name: 'test_')]
final class TestController extends AbstractController
{
    #[Route('', name: 'post', methods: ["POST"])]
    public function post_test(Request $request): JsonResponse
    {
        return new JsonResponse(data: [
            "status" => "success",
            "data" => [
                "context" => "POST request",
                "message" => "It's working!",
                "data_posted" => json_decode($request->getContent(), true)
            ]
        ]);
    }
    #[Route('', name: 'get', methods: ["GET"])]
    public function get_test(): JsonResponse
    {
        return new JsonResponse(data: [
            "status" => "success",
            "data" => [
                "context" => "GET request",
                "message" => "It's working!",
            ]
        ]);
    }

    #[Route('/mail', name: 'mail', methods: ["GET"])]
    public function get_mail(MailService $mailService): JsonResponse
    {
        $user = new User();
        $user
            ->setEmail("deadliners@yopmail.com")
            ->setFirstname("Dead")
            ->setLastname("Liners")
        ;

        $mailService->sendRegistrationMail($user);
        return new JsonResponse(data: [
            "status" => "success",
            "data" => [
                "context" => "GET request",
                "message" => "It's working!",
            ]
        ]);
    }
}
