<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailService;
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
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, MailService $mailService): JsonResponse
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

            if ($_ENV["APP_ENV"] == "prod") {
                $mailService->sendRegistrationMail($user);
            }

            return new JsonResponse(
                data: [
                    "message" => "Inscription terminée !"
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

    #[Route('/forgot-password', name: 'forgot-password', methods: ['POST'])]
    public function forgot(Request $request, EntityManagerInterface $entityManager, MailService $mailService): JsonResponse
    {
        try {
            $data = json_decode($request->getContent());

            $user = $entityManager->getRepository(User::class)->findOneBy(["email" => $data->email]);

            if (! is_null($user)) {
                if ($_ENV["APP_ENV"] == "prod") {
                    $mailService->sendForgotPasswordMail($user);
                }

                return new JsonResponse(
                    data: [
                        "message" => "Un lien a été envoyé !"
                    ],
                    status: Response::HTTP_OK,
                    json: false
                );
            } else {
                return new JsonResponse(
                    data: [
                        "message" => "Inscription terminée !"
                    ],
                    status: Response::HTTP_BAD_REQUEST,
                    json: false
                );
            }
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


    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    public function reset_password(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $data = json_decode($request->getContent());

            $user = $entityManager->getRepository(User::class)->find(base64_decode($data->token));

            if (! is_null($user)) {
                $user->setPassword($passwordHasher->hashPassword($user, $data->confirmPassword));
                $entityManager->persist($user);
                $entityManager->flush();

                return new JsonResponse(
                    data: [
                        "message" => "Mot de passe modifié."
                    ],
                    status: Response::HTTP_OK,
                    json: false
                );
            } else {
                return new JsonResponse(
                    data: [
                        "message" => "Utilisateur introuvable."
                    ],
                    status: Response::HTTP_BAD_REQUEST,
                    json: false
                );
            }
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
