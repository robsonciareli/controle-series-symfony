<?php

namespace App\Controller;

use App\Entity\Series;
use App\Form\SeriesType;
use App\DTO\SeriesCreateFormInput;
use App\Message\SerieWasCreated;
use App\Repository\SeriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;

class SeriesController extends AbstractController
{
    public function __construct(
        private SeriesRepository $seriesRepository, 
        private EntityManagerInterface $entityManagerInterface,
        private MessageBusInterface $message
        )
    {
    }

    #[Route('/series', name: 'app_series')]
    public function seriesList(Request $request): Response
    {
        $seriesList = $this->seriesRepository->findAll();

        return $this->render('series/index.html.twig', [
            'seriesList' => $seriesList,
        ]);
    }

    #[Route("/series/create", name: 'app_series_form', methods:['GET'])]
    public function addSeriesForm(): Response
    {
        $seriesForm = $this->createForm(SeriesType::class, new SeriesCreateFormInput());
        return $this->renderForm('series/form.html.twig', compact('seriesForm'));
    }

    #[Route("/series/create", name: 'app_add_series', methods:['POST'])]
    public function addSeries(Request $request): Response
    {
        $input = new SeriesCreateFormInput();
        $seriesForm = $this->createForm(SeriesType::class, $input)
            ->handleRequest($request);

        if(!$seriesForm->isValid()) {
            return $this->renderForm('series/form.html.twig', compact('seriesForm'));
        }

        $series = $this->seriesRepository->add($input);

        $this->message->dispatch( new SerieWasCreated($series));
        
        $this->addFlash(
            'success', 
            "Série \"{$series->getName()}\" inserida com sucesso"
        );

        return new RedirectResponse('/series/');
    }

    #[Route('/series/delete/{id}', 
        name:'app_delete_series', 
        methods:['DELETE'], 
        requirements: ['id' => '[0-9]+']
    )]
    public function deleteSerie(int $id, Request $request): Response
    {
        $this->seriesRepository->removeById($id);
        $this->addFlash('success', 'Série removida com sucesso');

        return new RedirectResponse('/series/');
    }

    #[Route("/series/edit/{series}", name: "app_edit_series_form", methods:['GET'])]
    public function editSeriesForm(Series $series): Response
    {
        $seriesForm = $this->createForm(SeriesType::class, $series, ['is_edit' => true]);
        return $this->renderForm('series/form.html.twig', compact('seriesForm', 'series'));
    }

    #[Route("/series/edit/{series}", name: 'app_store_series_changes', methods:['PATCH'])]
    public function storeSeriesChanges(Series $series, Request $request): Response
    {
        $seriesForm = $this->createForm(SeriesType::class, $series, ['is_edit' => true]);
        $seriesForm->handleRequest($request);

        if(!$seriesForm->isValid()){
            return $this->renderForm('series/form.html.twig', compact('seriesForm', 'series'));
        }

        $this->addFlash('success', "Série \"{$series->getName()}\" editada com sucesso");
        $this->entityManagerInterface->flush();
        
        return new RedirectResponse('/series/');
    }

}
