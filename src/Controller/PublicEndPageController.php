<?php

namespace App\Controller;

use App\Entity\EndPage;
use App\Repository\EndPageRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/end-pages/public', name: 'public_endpages_')]
final class PublicEndPageController extends AbstractController
{
    #[Route('/view', name: 'view', methods: ['POST'])]
    public function view(Request $request, EndPageRepository $endPageRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent());
        $endPage = $endPageRepository->find($data->id);
        if (!is_null($endPage)) {
            $endPage->setViews($endPage->getViews() + 1);
            $entityManager->persist($endPage);
            $entityManager->flush();

            $serialized_endpage = $serializer->serialize($endPage ,format: "json", context: ["groups" => "endpage:solo"]);

            return new JsonResponse(
                data: json_decode($serialized_endpage),
                status: Response::HTTP_OK,
                json: false
            );
        } else {
            return new JsonResponse(
                data: [
                    "message" => "EndPage introuvable"
                ],
                status: Response::HTTP_BAD_REQUEST,
                json: false
            );
        }
    }

    #[Route('/leaderboard', name: 'leaderboard', methods: ['GET'])]
    public function leaderboard(SerializerInterface $serializer, EndPageRepository $endPageRepository): Response
    {
        try{
            $paginationSize = 10;
            $pages = $endPageRepository->findBy([], orderBy: ["likes" => "desc"], limit: 10);

            $endpages = $serializer->serialize($pages, format: "json", context: ["groups" => "endpage:view"]);
            return new JsonResponse(
                data: json_decode($endpages),
                status: Response::HTTP_OK,
                json: false
            );
        } catch (Exception $exception) {
            return new JsonResponse(
                data: [
                    "message" => $exception->getMessage(),
                ],
                status: Response::HTTP_BAD_REQUEST,
                json: false
            );
        }
    }
}
