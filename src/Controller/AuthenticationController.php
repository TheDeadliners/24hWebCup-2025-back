<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/auth', name: 'auth_')]
class AuthenticationController extends AbstractController
{
    protected SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    #[Route('/refresh', name: 'refresh', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function refresh(JWTTokenManagerInterface $jwtTokenManager, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $entityManager->getRepository(User::class)->find($this->getUser());
            $token = $jwtTokenManager->createFromPayload($user, [
                "firstname" => $user->getFirstname(),
                "lastname" => $user->getLastname()
            ]);

            return new JsonResponse(
                data: [
                    "token" => $token,
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

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $user = new User();

            $data = json_decode($request->getContent());
            $user
                ->setEmail($data->email)
                ->setFirstname($data->firstname)
                ->setLastname($data->lastname)
                ->setPassword($passwordHasher->hashPassword($user, $data->password))
            ;

            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse(
                data: [
                    "message" => "Inscription terminée, bienvenue " . ucfirst($data->firstname) . " !"
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

    #[Route('/change-password', name: 'change_password', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function change_password(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $user = $entityManager->getRepository(User::class)->find($this->getUser());
            $data = json_decode($request->getContent());

            if ($passwordHasher->isPasswordValid($user, $data->currentPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $data->newPassword));
                $entityManager->persist($user);
                $entityManager->flush();

                return new JsonResponse(
                    data: [
                        "message" => "Mot de passe modifié."
                    ],
                    status: Response::HTTP_OK,
                    json: false
                );
            }

            return new JsonResponse(
                data: [
                    "message" => "Mot de passe actuel incorrect."
                ],
                status: Response::HTTP_BAD_REQUEST,
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

    #[Route('/edit-account', name: 'edit_account', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit_account(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $user = $entityManager->getRepository(User::class)->find($this->getUser());
            $data = json_decode($request->getContent());

            if ($passwordHasher->isPasswordValid($user, $data->currentPassword)) {
                $user
                    ->setFirstname($data->firstname)
                    ->setLastname($data->lastname)
                    ->setUsername($data->username)
                ;

                $entityManager->persist($user);
                $entityManager->flush();

                return new JsonResponse(
                    data: [
                        "message" => "Informations du compte modifiées."
                    ],
                    status: Response::HTTP_OK,
                    json: false
                );
            }

            return new JsonResponse(
                data: [
                    "message" => "Mot de passe actuel incorrect."
                ],
                status: Response::HTTP_BAD_REQUEST,
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
