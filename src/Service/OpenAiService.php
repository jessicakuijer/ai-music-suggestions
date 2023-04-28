<?php

namespace App\Service;

use Tectalic\OpenAi\Authentication;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Tectalic\OpenAi\Manager;
use Tectalic\OpenAi\Models\ChatCompletions\CreateRequest;
use Symfony\Component\HttpClient\Psr18Client;

class OpenAiService
{
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    private function formatResponse(string $response): string
{
    // Transforme les URL en liens cliquables
    $response = preg_replace_callback(
        '~\b(?:https?://|www\.)\S+\b~',
        function ($matches) {
            $url = $matches[0];
            $urlWithPrefix = $url;
            if (strpos($url, 'http') !== 0) {
                $urlWithPrefix = "http://" . $url;
            }

            return '<a href="' . $urlWithPrefix . '" target="_blank">' . $url . '</a>';
        },
        $response
    );

    // Ajoute un retour à la ligne après chaque point qui suit un mot, en ignorant les URL
    $response = preg_replace('/(?<!\w\.\w.)(?<![A-Z][a-z]\.)(?<=\.(?!(com|org|net|gov|edu|io|co|us)\b))\s+/', "<br>", $response);

    return $response;
}


    public function getArtistSuggestions(string $query): array
    {
        $openAiKey = $this->parameterBag->get('OPENAI_API_KEY');
        $httpClient = new Psr18Client();
        $openAiClient = Manager::build($httpClient, new Authentication($openAiKey));
        
        $prompt = "Donne moi une dizaine d'artistes émergents et similaires à cet artiste, donne un lien pour acheter la musique de cet artiste sur bandcamp et suggère d'autres noms de plateformes sans url. Si tu n'es pas en mesure d'avoir un url de bandcamp pour l'artiste donné, réponds par une phrase qui induit une possibilité d'url invalide et suggère uniquement des noms de plateforme sans url et l'url direct de https://bandcamp.com/ pour faire une recherche manuelle: $query: \n\n";

        $request = $openAiClient->chatCompletions()->create(
        new CreateRequest([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 1000,
                'frequency_penalty' => 0.3,
                'presence_penalty' => 0.5,
                'n' => 1,
                'stop' => null,
                'best_of' => 1
        ])
        )->toModel();

        if (
            isset($request->choices) &&
            isset($request->choices[0]) &&
            isset($request->choices[0]->message) &&
            isset($request->choices[0]->message->content)
        ) {
            $response = $request->choices[0]->message->content;
            $response = $this->formatResponse($response);
        } else {
            $response = "Une erreur est survenue dans la réponse d'OpenAI.";
        }
    
        return ['suggestion' => $response];
    }
}