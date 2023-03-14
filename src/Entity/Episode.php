<?php

namespace App\Entity;

use App\Repository\EpisodeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EpisodeRepository::class)]
class Episode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Season::class, inversedBy: 'episodes')]
    #[ORM\JoinColumn(nullable:false)]
    private Season $season;

    #[ORM\Column]
    private ?bool $watched = null;

    public function __construct(
        #[ORM\Column(type: Types::SMALLINT)]
        private ?int $number = null
    ){
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): self
    {
        $this->setWatched(false);
        $this->season = $season;

        return $this;
    }

    public function isWatched(): ?bool
    {
        return $this->watched;
    }

    public function setWatched(bool $watched): self
    {
        $this->watched = $watched;

        return $this;
    }
}
