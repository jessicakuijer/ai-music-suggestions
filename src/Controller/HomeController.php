<?php

namespace App\Controller;

use App\Form\ArtistFormType;
use App\Service\OpenAiService;
use App\Service\YoutubeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(Request $request, OpenAiService $OpenAiService, YoutubeService $youtubeService): Response
    {
        $form = $this->createForm(ArtistFormType::class);
        $form->handleRequest($request);

        $artistSuggestions = null;
        $youtubeUrl = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $artistQuery = $form->get('artistQuery')->getData();
            $artistSuggestions = $OpenAiService->getArtistSuggestions($artistQuery);
            $youtubeUrl = $youtubeService->searchVideo($artistQuery);
            return $this->render('home/artists.html.twig', [
                'artistSuggestions' => $artistSuggestions ?? null,
                'youtubeUrl' => $youtubeUrl,
            ]);
        }

        return $this->render('home/index.html.twig', [
            'artistForm' => $form->createView(),
            'artistSuggestions' => $artistSuggestions,
            'youtubeUrl' => $youtubeUrl,
        ]);
    }
}

