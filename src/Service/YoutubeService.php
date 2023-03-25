<?php

namespace App\Service;

use Google_Client;
use Google_Service_YouTube;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class YoutubeService
{
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function searchVideo(string $query): ?string
    {
        $client = new Google_Client();
        $client->setDeveloperKey($this->parameterBag->get('YOUTUBE_API_KEY'));
        $youtube = new Google_Service_YouTube($client);

        $searchResponse = $youtube->search->listSearch('id,snippet', [
            'q' => $query,
            'type' => 'video',
            'maxResults' => 1,
            'videoDefinition' => 'high',
            'fields' => 'items(id(videoId),snippet(publishedAt,channelId,title,description))',
        ]);

        if (empty($searchResponse->items)) {
            return null;
        }

        $videoId = $searchResponse->items[0]->id->videoId;

        return "https://www.youtube.com/watch?v={$videoId}";
    }
}
