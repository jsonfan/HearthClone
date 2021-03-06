<?php namespace App\Models;

use App\Game\Cards\Card;
use App\Game\Cards\Minion;
use App\Game\Game;
use App\Game\Player;
use Mockery;
use TestCase;

/**
 * Created by PhpStorm.
 * User: Kegimaro
 * Date: 8/30/15
 * Time: 8:09 PM
 */
class HearthCloneTest extends TestCase
{
    /** @var  Game $game */
    public $game;

    /** @var Player $player1 */
    public $player1;

    /** @var Player $player2 */
    public $player2;

    /**
     * Setup condition for every test.
     * Forces player 1 to be the default starting player
     */
    public function setUp() {
        parent::setUp();
        $this->game = app('Game');
        $this->player1 = $this->game->getPlayer1();
        $this->player2 = $this->game->getPlayer2();
        if($this->player1->getPlayerId() != $this->game->getActivePlayer()->getPlayerId()) {
            $this->game->toggleActivePlayer();
        }
        $this->initPlayers();
    }

    /**
     * What goes up must come down.
     */
    public function tearDown() {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * Play card with endless mana.
     *
     * @param $name
     * @param int $player_id
     * @param array $targets
     * @param bool|false $summoning_sickness
     * @param null $choose_mechanic
     * @return Minion
     * @throws \App\Exceptions\MissingCardNameException
     * @throws \App\Exceptions\NotEnoughManaCrystalsException
     */
    public function playCard($name, $player_id = 1, $targets = [], $summoning_sickness = false, $choose_mechanic = null, $position = 3) {

        /** @var Player $player */
        $player = $this->player1;
        if ($player_id == 2) {
            $player = $this->player2;
        }

        $card = Card::load($player, $name);
        $card->setChooseOption($choose_mechanic);

        $this->game->getPlayer1()->setManaCrystalCount(1000);
        $this->game->getPlayer2()->setManaCrystalCount(1000);

        $player->play($card, $targets, $position);

        if (!$summoning_sickness) {
            $active_player = $this->game->getActivePlayer();
            $active_player->passTurn();

            $active_player = $this->game->getActivePlayer();
            $active_player->passTurn();
        }

        return $card;
    }

    /**
     * This is mean to allow control over turn summoning sickness.
     *
     * It also allows you to test EOT effects since the
     * turn is not automatically ended.
     *
     * @param $name
     * @param int $player_id
     * @param int $turn
     * @param array $targets
     * @param null $choose_mechanic
     * @return Card
     */
    public function playCardStrict($name, $player_id = 1, $turn = 1, $targets = [], $choose_mechanic = null) {
        /** @var Player $player */
        $player = $this->game->getPlayer1();
        if ($player_id == 2) {
            $player = $this->game->getPlayer2();
        }

        $card = Card::load($player, $name);
        $card->setChooseOption($choose_mechanic);

        if ($turn > 1) {
            $player_a = $this->game->getActivePlayer();
            $player_b = $this->game->getDefendingPlayer();

            for ($i = 1; $i <= ($turn - 1); $i++) {
                $player_a->passTurn();
                $player_b->passTurn();
            }
        }

        $player->play($card, $targets);

        return $card;
    }

    /**
     * Initialize different classes and decks to test with.
     *
     * @param string $player1_class
     * @param array $player1_deck
     * @param string $player2_class
     * @param array $player2_deck
     */
    public function initPlayers($player1_class = 'Hunter', $player1_deck = [], $player2_class = 'Mage', $player2_deck = []) {
        $player1_deck = app('Deck', [app($player1_class, [$this->game->getPlayer1()]), $player1_deck]);
        $player2_deck = app('Deck', [app($player2_class, [$this->game->getPlayer2()]), $player2_deck]);

        $this->game->init($player1_deck, $player2_deck);
    }

    /**
     * Play a weapon card
     *
     * @param $weapon_name
     * @param int $player_id
     * @param array $targets
     */
    public function playWeaponCard($weapon_name, $player_id = 1, $targets = []) {
        /** @var Player $player */
        $player = $this->game->getPlayer1();
        if ($player_id == 2) {
            $player = $this->game->getPlayer2();
        }

        $card = Card::load($player, $weapon_name);
        $player->play($card, $targets);
    }

    /**
     * Get the active player id
     *
     * @return mixed
     */
    public function getActivePlayerId() {
        return $this->game->getActivePlayer()->getPlayerId();
    }

    /**
     * Get the defending player id
     *
     * @return mixed
     */
    public function getDefendingPlayerId() {
        return $this->game->getDefendingPlayer()->getPlayerId();
    }

    /**
     * Helper function to make testing position code easier.
     *
     * @param $player_id
     * @param $position
     * @return Minion
     */
    public function playWispAtPosition($player_id, $position) {
        return $this->playCard('Wisp', $player_id, [], false, null, $position);
    }
}