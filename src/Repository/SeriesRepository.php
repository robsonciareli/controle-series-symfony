<?php

namespace App\Repository;

use Exception;
use App\Entity\Series;
use App\DTO\SeriesCreateFormInput;
use App\Repository\SeasonRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Series>
 *
 * @method Series|null find($id, $lockMode = null, $lockVersion = null)
 * @method Series|null findOneBy(array $criteria, array $orderBy = null)
 * @method Series[]    findAll()
 * @method Series[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeriesRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry, 
        private SeasonRepository $seasonRepository,
        private EpisodeRepository $episodeRepository)
    {
        parent::__construct($registry, Series::class);
    }

    public function save(Series $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Series $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function removeById(int $id)
    {
        $series = $this->getEntityManager()->getPartialReference(Series::class, $id);
        $this->remove($series, true);
    }

    public function add(SeriesCreateFormInput $input): Series
    {
        $entityManager = $this->getEntityManager();

        $series = new Series($input->seriesName);
        $entityManager->persist($series);
        $entityManager->flush();

        try {
            $this->seasonRepository->addSeasonsQuantity($input->seasonsQuantity, $series->getId());
            $seasons = $this->seasonRepository->findBy(['series' => $series]);
            $this->episodeRepository->addEpisodesPerSeason($input->episodesPerSeason, $seasons);
        } catch (Exception $e) {
            $this->remove($series, true);
        }

        return $series;
    }
}
