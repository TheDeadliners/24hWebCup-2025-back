<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
final class TestController extends AbstractController
{
    #[Route('/test', name: 'post_test', methods: ["POST"])]
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
    #[Route('/test', name: 'get_test', methods: ["GET"])]
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
}
