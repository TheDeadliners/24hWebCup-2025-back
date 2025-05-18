<?php

namespace App\Controller;

use App\Entity\EndPage;
use App\Entity\User;
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

#[Route('/end-pages', name: 'endpages_')]
final class EndPageController extends AbstractController
{
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(EndPageRepository $endPageRepository, UserRepository $userRepository): JsonResponse
    {
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
                data: [
                    "count" => $count,
                    "views" => $views,
                    "likes" => $likes
                ],
                status: Response::HTTP_OK,
                json: false
            );
        } catch(Exception $exception) {
            return new JsonResponse(
                data: [
                    "message" => $exception->getMessage(),
                    "count" => $count,
                    "views" => $views,
                    "likes" => $likes
                ],
                status: Response::HTTP_BAD_REQUEST,
                json: false
            );
        }
    }

    #[Route('/like', name: 'like', methods: ['POST'])]
    public function like(Request $request, EndPageRepository $endPageRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent());

        $endPage = $endPageRepository->find($data->id);

        if (! is_null($endPage)) {

            $endPage->setLikes($endPage->getLikes() + 1);
            $entityManager->persist($endPage);
            $entityManager->flush();

            return new JsonResponse(
                data: [
                    "message" => "Le like a été envoyé !"
                ],
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

    #[Route('/dislike', name: 'dislike', methods: ['POST'])]
    public function dislike(Request $request, EndPageRepository $endPageRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent());

        $endPage = $endPageRepository->find($data->id);

        if (! is_null($endPage)) {

            if ($endPage->getLikes() >= 1) {
                $endPage->setLikes($endPage->getLikes() - 1);
            }

            $entityManager->persist($endPage);
            $entityManager->flush();

            return new JsonResponse(
                data: [
                    "message" => "Le dislike a été envoyé !"
                ],
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

    #[Route('', name: 'create', methods: ['POST'])]
    public function endPagesCreate(Request $request,EntityManagerInterface $entityManager): Response
    {
        try{
            $endPage = new EndPage();


            $user = $entityManager->getRepository(User::class)->find($this->getUser());

            $data = json_decode($request->getContent());
            $endPage
                ->setUser($user)
                ->setTitle($data->title)
                ->setCategory($data->category)
                ->setLikes(rand(300, 500))
                ->setText($data->text)
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

    #[Route('/my-endpages', name: 'get_all_my_end', methods: ['GET'])]
    public function get_all(SerializerInterface $serializer, EndPageRepository $endPageRepository, UserRepository $userRepository): Response
    {
        try{
            $user = $userRepository->find($this->getUser());
            $paginationSize = 10;
            $pages = array_chunk($endPageRepository->findBy(["user" => $user]), $paginationSize)[0];

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
