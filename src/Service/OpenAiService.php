<?php

namespace App\Service;

use Tectalic\OpenAi\Manager;
use Tectalic\OpenAi\Authentication;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Tectalic\OpenAi\Models\ChatCompletions\CreateRequest;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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

    // Transforme la liste d'artistes en liste numérotée sans les numéros répétés
    $response = preg_replace('/(\d+)\.\s*(.*\s*-\s*<a\s*href=\".*\"\s*target=\"_blank\">.*<\/a>\s*)/', '<li>$2</li>', $response);

    // Ajoute les balises <ol> et </ol> autour de la liste numérotée
    $response = preg_replace('/(<li>.*<\/li>)/s', '<ol>$1</ol>', $response);

    return $response;
    }


    public function getArtistSuggestions(string $query): array
    {
            // Appelez l'API et récupérez les données
            $apiData = $this->callOpenAiApi($query);
    
            return $apiData;
    }

    private function callOpenAiApi(string $query): array
    {
    $openAiKey = $this->parameterBag->get('OPENAI_API_KEY');
    
    $symfonyHttpClient = HttpClient::create(['timeout' => 120]); // Augmente le délai d'attente à 120 secondes
    $httpClient = new Psr18Client($symfonyHttpClient);
    $openAiClient = Manager::build($httpClient, new Authentication($openAiKey));
    
    $messages = [
        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
        ['role' => 'user', 'content' => "Je recherche cinq artistes émergents similaires à $query."],
        ['role' => 'user', 'content' => "Fournissez un lien pour acheter leur musique sur Bandcamp pour chaque artiste suggéré."],
        ['role' => 'user', 'content' => "Si un lien Bandcamp valide n'est pas disponible, mentionnez les noms des plateformes et l'URL directe de https://bandcamp.com/ pour effectuer une recherche manuelle."],
        ['role' => 'user', 'content' => "Justifiez vos choix d'artistes similaires émergents à la fin de la liste de manière générale."]
        ];

    $request = $openAiClient->chatCompletions()->create(
        new CreateRequest([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'temperature' => 0.5, // Réduisez la valeur de la température
            'max_tokens' => 500, // Réduisez le nombre maximal de tokens
            'frequency_penalty' => 0.5,
            'presence_penalty' => 0.6,
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