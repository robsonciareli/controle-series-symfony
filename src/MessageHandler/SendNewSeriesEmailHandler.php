<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\SerieWasCreated;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendNewSeriesEmailHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private MailerInterface $mailer
    )
    {
    }

    public function __invoke(SerieWasCreated $message)
    {
        $users = $this->userRepository->findAll();
        $usersEmail = array_map(fn (User $user ) => $user->getEmail(), $users);
        $series = $message->series;

        $email = (new TemplatedEmail())
            ->to(...$usersEmail)
            ->subject('Nova série criada')
            ->text("Série {$series->getName()} foi criada")
            ->htmlTemplate("emails/series-created.html.twig")
            ->context(compact('series'));

        $this->mailer->send($email);

    }
}