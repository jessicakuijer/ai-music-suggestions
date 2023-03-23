<?php

// src/Controller/HomeController.php
namespace App\Controller;

use App\Form\ArtistFormType;
use App\Service\OpenAiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(Request $request, OpenAiService $OpenAiService): Response
    {
        $form = $this->createForm(ArtistFormType::class);
        $form->handleRequest($request);

        $artistSuggestions = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $artistQuery = $form->get('artistQuery')->getData();
            $artistSuggestions = $OpenAiService->getArtistSuggestions($artistQuery);
        }

        return $this->render('home/index.html.twig', [
            'artistForm' => $form->createView(),
            'artistSuggestions' => $artistSuggestions,
        ]);
    }
}

