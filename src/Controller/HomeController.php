<?php

namespace App\Controller;

use App\Form\ArtistFormType;
use App\Service\OpenAiService;
use App\Service\YoutubeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class HomeController extends AbstractController
{
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }
    
    #[Route('/', name: 'home')]
    public function index(Request $request, OpenAiService $OpenAiService, YoutubeService $youtubeService, SessionInterface $session): Response
    {
        if (!$session->get('isAuthenticated')) {
            return $this->redirectToRoute('password_prompt');
        }

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

    #[Route('/password-prompt', name: 'password_prompt')]
    public function passwordPrompt(Request $request, SessionInterface $session): Response
    {
        $password = $this->parameterBag->get('PASSWORD_PROMPT');
        if ($request->isMethod('POST')) {
            $enteredPassword = $request->request->get('password');
            $correctPassword = $password; // Changer pour votre mot de passe dans vos variables d'environnement

            if ($enteredPassword === $correctPassword) {
                $session->set('isAuthenticated', true);
                return $this->redirectToRoute('home');
            } else {
                $this->addFlash('error', 'Mot de passe incorrect.');
            }
        }

        return $this->render('home/password_prompt.html.twig');
    }
}

