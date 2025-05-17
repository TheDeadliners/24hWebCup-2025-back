<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

readonly class MailService
{
    private ?Mailer $mailer;

    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->mailer = new Mailer(Transport::fromDsn("smtp://". urlencode($_ENV["MAILER_SMTP"]) .":". $_ENV["MAILER_PORT"] . "?verify_peer=0"));
        $this->twig = $twig;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendRegistrationMail(User $user): void {
        $email = new Email();
        $email
            ->from(new Address("no-reply@deadliners.lareunion.webcup.hodi.host", name: "TheEnd.page - Deadliners"))
            ->to(new Address($user->getEmail(), name: $user->getFirstname() . " " . $user->getLastname()))
            ->subject('TheEnd.page - Deadliners - Bienvenue sur TheEnd.page')
            ->html($this->twig->render('registration.html.twig', ["user" => $user]));
        ;

        $this->mailer->send($email);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendForgotMail(User $user): void {
        $email = new Email();
        $email
            ->from(new Address("no-reply@deadliners.lareunion.webcup.hodi.host", name: "TheEnd.page - Deadliners"))
            ->to(new Address($user->getEmail(), name: $user->getFirstname() . " " . $user->getLastname()))
            ->subject('TheEnd.page - Deadliners - Mot de passe oublié sur TheEnd.page')
            ->html($this->twig->render('forgot.html.twig', ["user" => $user, "token" => base64_encode($user->getId())]));
        ;

        $this->mailer->send($email);
    }
}