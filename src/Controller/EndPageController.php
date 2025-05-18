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
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/end-pages', name: 'endpages_')]
final class EndPageController extends AbstractController
{
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(EndPageRepository $endPageRepository, UserRepository $userRepository,  SerializerInterface $serializer) {
        try {
            $userEndPages = $endPageRepository->findBy(["user" => $userRepository->find($this->getUser())]);
            $count = sizeof($userEndPages);

            $views = 0;
            $likes = 0;
            foreach ($userEndPages as $endPage) {
                $views += $endPage->getViews();
                $likes += $endPage->getLikes();
            }

            return new JsonResponse(
                data: json_decode($serializer->serialize([
                    "count" => $count,
                    "views" => $views,
                    "likes" => $likes
                ], format: "json", context: ["groups" => "customers:get"])),
                status: Response::HTTP_OK,
                json: false
            );
        } catch(Exception $exception) {
            return new JsonResponse(
                data: [
                    "message" => $exception->getMessage(),
                ],
                status: Response::HTTP_BAD_REQUEST,
                json: false
            );
        }
    }

    #[Route('', name: 'list', methods: ['POST'])]
    public function endPagesIndex(Request $request,EntityManagerInterface $entityManager): Response
    {
        try{
            $endPage = new EndPage();

            $data = json_decode($request->getContent());
            $endPage
                ->setTitle($data->title)
                ->setCategory($data->category)
                ->setText($data->text)
                ->setCreatedAt(new DateTimeImmutable($data->createdAt))
                ->setImage($data->image)
                ->setMusic($data->music)
                ->setBackground($data->background)
                ->setGif($data->gif)
                ;

            $entityManager->persist($endPage);
            $entityManager->flush();
            return new JsonResponse(
                data: [
                    "message" => "EndPage postée !"
                ],
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
