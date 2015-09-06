<?php
use App\Models\HearthCloneTest;
use App\Models\Mechanics;

/**
 * Created by PhpStorm.
 * User: Kegimaro
 * Date: 9/5/15
 * Time: 7:00 PM
 */
class BattlecryTest extends HearthCloneTest
{
    /* Houndmaster */

    public function test_houndmaster_does_not_target_himself() {
        $this->initPlayers();
        $houndmaster = $this->playCard($this->houndmaster_name, 1);
        $this->assertEquals(4, $houndmaster->getAttack());
        $this->assertEquals(3, $houndmaster->getHealth());
    }

    /** @expectedException \App\Exceptions\InvalidTargetException */
    public function test_houndmaster_fails_when_target_is_not_a_beast() {
        $this->initPlayers();
        $wisp = $this->playCard($this->wisp_name, 1);
        $this->playCard($this->houndmaster_name, 1, [$wisp]);
    }

    public function test_houndmaster_adds_2_2_and_taunt_to_valid_beast_target() {
        $this->initPlayers();
        $timber_wolf = $this->playCard($this->timber_wolf_name, 1);
        $this->playCard($this->houndmaster_name, 1, [$timber_wolf]);
        $this->assertEquals(3, $timber_wolf->getAttack());
        $this->assertEquals(3, $timber_wolf->getHealth());
        $this->assertTrue($timber_wolf->hasMechanic(Mechanics::$TAUNT));
    }

    /* Guardian of Kings */
    public function test_guardian_of_kings_heals_friendly_hero_by_6() {
        $this->initPlayers();
        $this->game->getPlayer1()->getHero()->takeDamage(20);
        $this->playCard($this->guardian_of_kings_name, 1);
        $this->assertEquals(16, $this->game->getPlayer1()->getHero()->getHealth());
    }


}