<?php

namespace App\Service;

use Google_Client;
use Google_Service_YouTube;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;
class YoutubeService
{
    private ParameterBagInterface $parameterBag;
    private CacheInterface $cache;

    public function __construct(ParameterBagInterface $parameterBag, CacheInterface $cache)
    {
        $this->parameterBag = $parameterBag;
        $this->cache = $cache;
    }

    public function searchVideo(string $query): ?string
{
    return $this->cache->get(md5($query), function () use ($query) {
        $client = new Google_Client();
        $client->setDeveloperKey($this->parameterBag->get('YOUTUBE_API_KEY'));
        $youtube = new Google_Service_YouTube($client);

        $searchResponse = $youtube->search->listSearch('id', [
            'q' => $query,
            'type' => 'video',
            'maxResults' => 1,
            'videoDefinition' => 'high',
            'fields' => 'items(id(videoId))',
        ]);

        if (empty($searchResponse->items)) {
            return null;
        }

        $videoId = $searchResponse->items[0]->id->videoId;

        return "https://www.youtube.com/watch?v={$videoId}";
    });
}

}
