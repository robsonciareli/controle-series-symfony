<?php

namespace App\MessageHandler;

use App\Message\SerieWasCreated;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LogNewSeriesHandler
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(SerieWasCreated $message)
    {
        $this->logger->info("A new serie was created", [
            'Series Name' => $message->series->getName()
        ]);
    }
}