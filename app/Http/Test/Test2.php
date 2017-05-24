<?php
namespace App\Http\Test;
use App\Http\Controller;
use GuzzleHttp\Client;

class Test2 extends Controller
{
	private $api;
	private $apiKey;
	private $client;
	private $headers;
	private $competitions;
	/** @var \Illuminate\Database\Connection */
	private $db;

	public function __construct($request, $response, $view, $logger)
	{
		parent::__construct($request, $response, $view, $logger);

		$this->db = \Lib\Framework\App::instance()->resolve('database');
		$this->apiKey = 'd147ef27803d4f2a8c94a658386d7b1a';
		$this->api = new \Lib\FootballData($this->apiKey);
	}


	public function update()
	{
		$dbSeasons = $this->db->select("select * from Seasons inner join Leagues on Seasons.LeagueID = Leagues.LeagueID");

		foreach ($dbSeasons as $dbSeason) {

			$this->updateFixtures($dbSeason);

//			$this->updateTeams($dbSeason);
//			$teams = $this->db->select("select * from Teams where CountryID = ?", [$dbSeason->CountryID]);
//
//			foreach ($teams as $team) {
//				$this->updatePlayers($dbSeason, $team);
//			}
		}
	}


	private function updateTeams($dbSeason)
	{
		$teams = $this->api->getLeagueTeams($dbSeason->ApiID);

		foreach ($teams->teams as $team) {
			$dbTeam = $this->db->table('Teams')->where('ApiID', $team->id)->first();
			if (!$dbTeam) {
				echo "inserting team {$team->name}<br/>";

				$this->db->insert(
					"insert into Teams(CountryID, Name, ShortName, Logo, ApiID) values(?, ?, ?, ?, ?)", [
					$dbSeason->CountryID, $team->name, $team->shortName, $team->crestUrl, $team->id
				]);

				$this->db->insert(
					"insert into SeasonTeams(LeagueID, SeasonID, TeamID) values(?, ?, ?)", [
					$dbSeason->LeagueID, $dbSeason->SeasonID, $team->id
				]);
			}
		}
	}


	private function updatePlayers($dbSeason, $team)
	{
		$players = $this->api->getTeamPlayers($team->ApiID);

		foreach ($players->players as $player) {
			$dbPlayer = $this->db->table('Players')->where('Name', $player->name)->where('DateOfBirth', $player->dateOfBirth)->first();
			if (!$dbPlayer) {
				echo "  inserting player {$player->name}<br/>";

				$this->db->insert(
					"insert into Players(Name, DateOfBirth, Nationality, MarketValue) values(?, ?, ?, ?)", [
					$player->name, $player->dateOfBirth, $player->nationality, $player->marketValue
				]);

				$dbPlayer = $this->db->table('Players')->where('Name', $player->name)->where('DateOfBirth', $player->dateOfBirth)->first();
			}

			try{
				$this->db->insert(
					"insert into TeamPlayers(LeagueID, SeasonID, TeamID, PlayerID, Position, JerseyNumber, contractUntil) values(?, ?, ?, ?, ?, ?, ?)", [
					$dbSeason->LeagueID, $dbSeason->SeasonID, $team->TeamID, $dbPlayer->PlayerID,
					$player->position, $player->jerseyNumber, $player->contractUntil
				]);
			}
			catch (\Exception $e){}
		}
	}


	private function updateFixtures($dbSeason)
	{
		$fixtures = $this->api->getLeagueFixtures($dbSeason->ApiID);

		foreach ($fixtures->fixtures as $fixture) {

			$homeTeam = $this->db->table('Teams')->where('Name', $fixture->homeTeamName)->first();
			$awayTeam = $this->db->table('Teams')->where('Name', $fixture->awayTeamName)->first();

			if ($homeTeam == null || $awayTeam == null) {
				throw new \Exception("teams {$fixture->homeTeamName} or {$fixture->awayTeamName} not found!");
			}

			$fixture->date = (new \DateTime($fixture->date))->format('Y-m-d H:i:s');

			if (!isset($fixture->result->halfTime)) {
				$fixture->result->halfTime = new \stdClass();
				$fixture->result->halfTime->goalsHomeTeam = 0;
				$fixture->result->halfTime->goalsAwayTeam = 0;
			}
			if (!isset($fixture->odds)) {
				$fixture->odds = new \stdClass();
				$fixture->odds->homeWin = 0;
				$fixture->odds->draw = 0;
				$fixture->odds->awayWin = 0;
			}

			$this->db->insert(
				"insert into Games(
					LeagueID, SeasonID, Week, Schedule, 
					HomeTeamID, AwayTeamID, 
					HomeTeamScoreHT, AwayTeamScoreHT, 
					HomeTeamScoreFT, AwayTeamScoreFT, Finished,
					HomeWinOdd, AwayWinOdd, DrawOdd
					) 
					values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
				$dbSeason->LeagueID, $dbSeason->SeasonID, $fixture->matchday, $fixture->date,
				$homeTeam->TeamID, $awayTeam->TeamID,
				$fixture->result->halfTime->goalsHomeTeam, $fixture->result->halfTime->goalsAwayTeam,
				$fixture->result->goalsHomeTeam, $fixture->result->goalsAwayTeam, $fixture->status,
				$fixture->odds->homeWin, $fixture->odds->draw, $fixture->odds->awayWin
			]);
		}
	}

}