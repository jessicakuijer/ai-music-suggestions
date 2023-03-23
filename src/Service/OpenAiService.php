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

    public function getArtistSuggestions(string $query): array
    {
        $openAiKey = $this->parameterBag->get('OPENAI_API_KEY');
        $httpClient = new Psr18Client();
        $openAiClient = Manager::build($httpClient, new Authentication($openAiKey));
        $prompt = "donne moi 10 artistes sous forme de liste structurée qui partagent les mêmes influences ou style musical que $query: \n\n";

        $request = $openAiClient->chatCompletions()->create(
        new CreateRequest([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
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
        } else {
            $response = "Une erreur est survenue dans la réponse d'OpenAI.";
        }
    
        return ['suggestion' => $response];
    }
}