<?php

class LeagueActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCorrectSelectResultJsonFormatWhenLeaguesWithoutLiveCount(): void
    {
        $firstSport = (new SportCreator())->create();
        $secondSport = (new SportCreator())->create();

        $leagueWithoutLiveCount = (new LeagueCreator())->create([
            'sport_id' => $firstSport->getId(),
            'liveEventsCount'  => 0,
        ]);
        $leagueWithLiveCount = (new LeagueCreator())->create([
            'sport_id' => $secondSport->getId(),
            'liveEventsCount'  => 2,
        ]);

        (new EventCreator())->create([
            'time' => date('Y-m-d H:i:00', time() - 60 * 60),
            'status' => Event::S_ONLINE,
            'sport_id' => $firstSport->getId(),
            'league_id' => $leagueWithoutLiveCount->getId()
        ]);

        (new EventCreator())->create([
            'time' => date('Y-m-d H:i:00', time() + 60 * 60),
            'status' => Event::S_ONLINE,
            'sport_id' => $secondSport->getId(),
            'league_id' => $leagueWithLiveCount->getId()
        ]);

        $this->menuRepository->saveEvents(array_merge(
            $this->eventRepository->findLineEvents(null),
            $this->eventRepository->findLiveEvents(Language::EN)
        ));

        $controller = new LeagueController();
        $response = $controller->selectAction();

        $this->assertResponseFieldEquals($response, 'data.leagues.0.id', $leagueWithLiveCount->getId());
        $this->assertResponseFieldEquals($response, 'data.sports.0.id', $secondSport->getId());
    }
}
