<?php
use App\Game\Cards\Card;
use App\Game\Cards\Mechanics;
use App\Models\HearthCloneTest;

/**
 * Created by PhpStorm.
 * User: Kegimaro
 * Date: 9/8/15
 * Time: 9:48 PM
 */
class BasicSpellTest extends HearthCloneTest
{
    /* Ancestral Healing */
    public function test_ancestral_healing_heals_minion_to_full_and_gives_taunt() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $chillwind_yeti->takeDamage(4);
        $this->assertEquals(1, $chillwind_yeti->getHealth());
        $this->playCard('Ancestral Healing', 1, [$chillwind_yeti]);
        $this->assertEquals(5, $chillwind_yeti->getHealth());
        $this->assertTrue($chillwind_yeti->hasMechanic(Mechanics::$TAUNT));
    }

    /* Animal Companion */
    public function test_animal_companion_summons_misha_on_random_0() {
        $this->game->getPlayer1()->setManaCrystalCount(1000);

        $random_mock = Mockery::mock('Random')->makePartial();
        $random_mock->shouldReceive('getFromRange')->once()->andReturn(0);
        $this->instance('Random', $random_mock);

        $card = Card::load($this->player1, 'Animal Companion');
        $this->player1->play($card, [], 1);

        $minions = $this->player1->getMinionsInPlay();
        $this->assertEquals(1, count($minions));
        $this->assertEquals('Misha', current($minions)->getName());
    }

    public function test_animal_companion_summons_leokk_on_random_1() {
        $this->game->getPlayer1()->setManaCrystalCount(1000);

        $random_mock = Mockery::mock('Random')->makePartial();
        $random_mock->shouldReceive('getFromRange')->once()->andReturn(1);
        $this->instance('Random', $random_mock);

        $card = Card::load($this->player1, 'Animal Companion');
        $this->player1->play($card, [], 1);

        $minions = $this->player1->getMinionsInPlay();
        $this->assertEquals(1, count($minions));
        $this->assertEquals('Leokk', current($minions)->getName());
    }

    public function test_animal_companion_summons_huffer_on_random_2() {
        $this->game->getPlayer1()->setManaCrystalCount(1000);

        $random_mock = Mockery::mock('Random')->makePartial();
        $random_mock->shouldReceive('getFromRange')->once()->andReturn(2);
        $this->instance('Random', $random_mock);

        $card = Card::load($this->player1, 'Animal Companion');
        $this->player1->play($card, [], 1);

        $minions = $this->player1->getMinionsInPlay();
        $this->assertEquals(1, count($minions));
        $this->assertEquals('Huffer', current($minions)->getName());
    }

    /* Arcane Explosion */
    public function test_arcane_explosion_deals_one_damage_to_all_enemy_minions() {
        $wisp1         = $this->playCard('Wisp', 1);
        $wisp2         = $this->playCard('Wisp', 1);
        $knife_juggler = $this->playCard('Knife Juggler', 1);

        $this->playCard('Arcane Explosion', 2);
        $this->assertFalse($wisp1->isAlive());
        $this->assertFalse($wisp2->isAlive());
        $this->assertEquals(1, $knife_juggler->getHealth());
        $this->assertEquals(30, $this->game->getPlayer1()->getHero()->getHealth());
    }

    /* Arcane Intellect */
    public function test_arcane_intellect_draws_two_cards() {
        $this->assertEquals(0, $this->game->getPlayer1()->getHandSize());
        $this->playCard('Arcane Intellect', 1);
        $this->assertEquals(2, $this->game->getPlayer1()->getHandSize());
    }

    /* Arcane Missiles */
    public function test_arcane_missiles_hits_hero_three_times_when_there_are_no_minions() {
        $this->playCard('Arcane Missiles', 2);
        $this->assertEquals(27, $this->player1->getHero()->getHealth());
    }

    public function test_arcane_missiles_can_target_minions() {
        $chillwindYeti = $this->playCard('Chillwind Yeti', 1);

        $random_mock = Mockery::mock('Random')->makePartial();
        $random_mock->shouldReceive('getFromRange')->times(3)->andReturn(0);
        $this->instance('Random', $random_mock);

        $this->playCard('Arcane Missiles', 2);
        $this->assertEquals(2, $chillwindYeti->getHealth());
    }

    public function test_arcane_missiles_can_target_two_minions() {
        $chillwindYeti  = $this->playCard('Chillwind Yeti', 1);
        $chillwindYeti2 = $this->playCard('Chillwind Yeti', 1);

        $random_mock = Mockery::mock('Random')->makePartial();
        $random_mock->shouldReceive('getFromRange')->times(3)->andReturn(0, 1, 0);
        $this->instance('Random', $random_mock);

        $this->playCard('Arcane Missiles', 2);
        $this->assertEquals(3, $chillwindYeti->getHealth());
        $this->assertEquals(4, $chillwindYeti2->getHealth());
    }

    /* Arcane Shot */
    public function test_arcane_shot_does_two_damage_to_hero_when_played() {
        $player2 = $this->game->getPlayer2();
        $this->playCard('Arcane Shot', 1, [$player2->getHero()]);
        $this->assertEquals(28, $player2->getHero()->getHealth());
    }

    public function test_arcane_shot_does_two_damage_to_minion_when_played() {
        $knife_juggler = $this->playCard('Knife Juggler', 1);
        $this->playCard('Arcane Shot', 2, [$knife_juggler]);
        $this->assertFalse($knife_juggler->isAlive());
    }

    /* Assassinate */
    public function test_assassinate_destroys_target_minion() {
        $wisp = $this->playCard('Wisp', 1);
        $this->playCard('Assassinate', 2, [$wisp]);
        $this->assertFalse($wisp->isAlive());
    }

    /** @expectedException App\Exceptions\InvalidTargetException */
    public function test_assassinate_throws_when_targeting_hero() {
        $this->playCard('Assassinate', 2, [$this->game->getPlayer1()->getHero()]);
    }

    /* Backstab */
    public function test_backstab_deals_two_damage_to_an_undamaged_minion() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCard('Backstab', 2, [$chillwind_yeti]);
        $this->assertEquals(3, $chillwind_yeti->getHealth());
    }

    /** @expectedException App\Exceptions\InvalidTargetException */
    public function test_backstab_throws_when_targeting_damaged_minion() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $chillwind_yeti->takeDamage(1);
        $this->playCard('Backstab', 2, [$chillwind_yeti]);
    }

    /* Blessing of Kings */
    public function test_blessing_of_kings_gives_wisp_4_4() {
        $wisp = $this->playCard('Wisp', 1);
        $this->playCard('Blessing of Kings', 1, [$wisp]);
        $this->assertEquals(5, $wisp->getAttack());
        $this->assertEquals(5, $wisp->getMaxHealth());
        $this->assertEquals(5, $wisp->getHealth());
    }

    /* Blessing of Might */
    public function test_blessing_of_might_gives_target_minion_three_attack() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCard('Blessing of Might', 1, [$chillwind_yeti]);
        $this->assertEquals(7, $chillwind_yeti->getAttack());
    }

    /* Bloodlust */
    // TODO test bloodlust wears off at end of turn
    public function test_bloodlust_gives_friendly_minions_three_attack() {
        $wisp1 = $this->playCard('Wisp', 1);
        $wisp2 = $this->playCard('Wisp', 1);
        $this->playCard('Bloodlust', 1);
        $this->assertEquals(4, $wisp1->getAttack());
        $this->assertEquals(4, $wisp2->getAttack());
    }

    /* Claw */
    public function test_claw_gives_friendly_hero_two_attack_and_two_armor() {
        $this->playCardStrict('Claw', 1, 1);
        $player1 = $this->game->getPlayer1();
        $this->assertEquals(2, $player1->getHero()->getAttack());
        $this->assertEquals(2, $player1->getHero()->getArmor());
        $player1->passTurn();
        $this->assertEquals(0, $player1->getHero()->getAttack());
    }

    /* Cleave */
    // todo

    /* Consecration */
    public function test_consecration_deals_two_damage_to_all_enemies() {
        $wisp          = $this->playCard('Wisp', 1);
        $wisp1         = $this->playCard('Wisp', 2);
        $chillwind_yei = $this->playCard('Chillwind Yeti', 2);
        $this->playCard('Consecration', 1);
        $this->assertEquals(28, $this->game->getPlayer2()->getHero()->getHealth());
        $this->assertFalse($wisp1->isAlive());
        $this->assertEquals(3, $chillwind_yei->getHealth());
        $this->assertTrue($wisp->isAlive());
    }

    /* Corruption */
    // todo

    /* Deadly Poison */
    public function test_deadly_poison_adds_two_attack_to_friendly_weapon() {
        $this->playWeaponCard('Light\'s Justice', 1);
        $this->playCard('Deadly Poison', 1);
        $this->assertEquals(3, $this->game->getPlayer1()->getHero()->getWeapon()->getAttack());
    }

    /* Divine Spirit */
    public function test_divine_spirit_doubles_targets_health() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCard('Divine Spirit', 1, [$chillwind_yeti]);
        $this->assertEquals(10, $chillwind_yeti->getHealth());
    }

    /* Drain Life */
    public function test_drain_life_deals_two_damage_to_a_character_and_heals_friendly_hero_for_2() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 2);
        $player1        = $this->game->getPlayer1();
        $player1->getHero()->takeDamage(5);
        $this->playCard('Drain Life', 1, [$chillwind_yeti]);
        $this->assertEquals(3, $chillwind_yeti->getHealth());
        $this->assertEquals(27, $player1->getHero()->getHealth());
    }

    /* Execute */
    public function test_execute_kills_provided_damaged_minion() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $chillwind_yeti->takeDamage(2);
        $this->playCard('Execute', 1, [$chillwind_yeti]);
        $this->assertFalse($chillwind_yeti->isAlive());
    }

    /** @expectedException App\Exceptions\InvalidTargetException */
    public function test_execute_throws_when_target_is_not_damaged() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCard('Execute', 1, [$chillwind_yeti]);
    }

    /* Fan of Knives */
    public function test_fan_of_knives_deals_1_damage_to_all_enemy_minions_and_player_draws_card() {
        $wisp1          = $this->playCard('Wisp', 2);
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 2);
        $this->playCard('Fan of Knives', 1);
        $this->assertEquals(1, $this->game->getPlayer1()->getHandSize());
        $this->assertFalse($wisp1->isAlive());
        $this->assertEquals(4, $chillwind_yeti->getHealth());
    }

    /* Fireball */
    public function test_fireball_does_six_damage() {
        $this->playCard('Fireball', 1, [$this->game->getPlayer2()->getHero()]);
        $this->assertEquals(24, $this->game->getPlayer2()->getHero()->getHealth());
    }

    /* Flamestrike */
    public function test_flamestrike_deals_four_damage_to_all_opponent_minions() {
        $wisp           = $this->playCard('Wisp', 2);
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 2);
        $this->playCard('Flamestrike', 1);
        $this->assertFalse($wisp->isAlive());
        $this->assertEquals(1, $chillwind_yeti->getHealth());
    }

    /* Frost Nova */
    public function test_frost_nova_freezes_all_opponent_minions() {
        $wisp           = $this->playCard('Wisp', 2);
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 2);
        $this->playCard('Frost Nova', 1, [], true);
        $this->assertTrue($wisp->isFrozen());
        $this->assertTrue($chillwind_yeti->isFrozen());
    }

    /* Frost Shock */
    public function test_frost_shock_deals_one_damage_and_freezes_target_enemy_minion() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCardStrict('Frost Shock', 2, 2, [$chillwind_yeti]);
        $this->assertEquals(4, $chillwind_yeti->getHealth());
        $this->assertTrue($chillwind_yeti->isFrozen());
    }

    /* Frostbolt */
    public function test_frostbolt_deals_three_damage_and_freezes_target_minion() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCardStrict('Frostbolt', 2, 2, [$chillwind_yeti]);
        $this->assertEquals(2, $chillwind_yeti->getHealth());
        $this->assertTrue($chillwind_yeti->isFrozen());
    }

    /* Hammer of Wrath */
    public function test_hammer_of_wrath_deals_three_damage_and_draws_one_card() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCardStrict('Hammer of Wrath', 2, 2, [$chillwind_yeti]);
        $this->assertEquals(2, $chillwind_yeti->getHealth());
        $this->assertEquals(1, $this->game->getPlayer2()->getHandSize());
    }

    /* Hand of Protection */
    public function test_hand_of_protection_gives_minion_divine_shield() {
        $wisp = $this->playCard('Wisp', 1);
        $this->playCard('Hand of Protection', 1, [$wisp]);
        $this->assertTrue($wisp->hasMechanic(Mechanics::$DIVINE_SHIELD));
    }

    /* Healing Touch */
    public function test_healing_touch_heals_eight_health_to_friendly_hero() {
        $player1 = $this->game->getPlayer1();
        $player1->getHero()->takeDamage(10);
        $this->playCard('Healing Touch', 1);
        $this->assertEquals(28, $player1->getHero()->getHealth());
    }

    public function test_healing_touch_can_only_heal_up_to_thirty_life() {
        $player1 = $this->game->getPlayer1();
        $player1->getHero()->takeDamage(5);
        $this->playCard('Healing Touch', 1);
        $this->assertEquals(30, $player1->getHero()->getHealth());
    }

    /* Hellfire */
    public function test_hellfire_deals_three_damage_to_all_characters() {
        $player1         = $this->game->getPlayer1();
        $player2         = $this->game->getPlayer2();
        $chillwind_yeti1 = $this->playCard('Chillwind Yeti', 1);
        $chillwind_yeti2 = $this->playCard('Chillwind Yeti', 2);
        $this->playCard('Hellfire', 1);
        $this->assertEquals(27, $player1->getHero()->getHealth());
        $this->assertEquals(27, $player2->getHero()->getHealth());
        $this->assertEquals(2, $chillwind_yeti1->getHealth());
        $this->assertEquals(2, $chillwind_yeti2->getHealth());
    }

    /* Heroic Strike */
    public function test_heroic_strike_gives_friendly_hero_four_attack() {
        $this->playCardStrict('Heroic Strike', 1, 2);
        $player1 = $this->game->getPlayer1();
        $this->assertEquals(4, $player1->getHero()->getAttack());
        $player1->passTurn();
        $this->assertEquals(0, $player1->getHero()->getAttack());
    }

    /* Hex */
    // todo

    /* Holy Light */
    public function test_holy_light_heals_six_health_to_friendly_hero() {
        $player1 = $this->game->getPlayer1();
        $player1->getHero()->takeDamage(10);
        $this->playCard('Holy Light', 1);
        $this->assertEquals(26, $player1->getHero()->getHealth());
    }

    public function test_holy_light_can_only_heal_up_to_thirty_life() {
        $player1 = $this->game->getPlayer1();
        $player1->getHero()->takeDamage(3);
        $this->playCard('Holy Light', 1);
        $this->assertEquals(30, $player1->getHero()->getHealth());
    }

    /* Holy Nova */
    public function test_holy_nova_deals_two_damage_to_all_enemies_and_heals_all_friendly_characters_by_two() {
        $player1 = $this->game->getPlayer1();
        $player2 = $this->game->getPlayer2();
        $player1->getHero()->takeDamage(5);
        $wisp1           = $this->playCard('Wisp', 1);
        $chillwind_yeti1 = $this->playCard('Chillwind Yeti', 1);
        $chillwind_yeti1->takeDamage(3);

        $wisp2           = $this->playCard('Wisp', 2);
        $chillwind_yeti2 = $this->playCard('Chillwind Yeti', 2);
        $this->playCard('Holy Nova', 1);

        $this->assertEquals(1, $wisp1->getHealth());
        $this->assertEquals(4, $chillwind_yeti1->getHealth());

        $this->assertFalse($wisp2->isAlive());
        $this->assertEquals(3, $chillwind_yeti2->getHealth());

        $this->assertEquals(27, $player1->getHero()->getHealth());
        $this->assertEquals(28, $player2->getHero()->getHealth());
    }

    /* Holy Smite */
    public function test_holy_smite_deals_two_damage() {
        $this->playCard('Holy Smite', 1, [$this->game->getPlayer2()->getHero()]);
        $this->assertEquals(28, $this->game->getPlayer2()->getHero()->getHealth());
    }

    /* Humility */
    public function test_humility_sets_target_attack_to_one() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCard('Humility', 2, [$chillwind_yeti]);
        $this->assertEquals(1, $chillwind_yeti->getAttack());
    }

    /* Hunters Mark */
    public function test_hunters_mark_sets_target_health_to_one() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCard('Hunter\'s Mark', 2, [$chillwind_yeti]);
        $this->assertEquals(1, $chillwind_yeti->getHealth());
    }

    /* Innervate */
    // todo

    /* Kill Command */
    // todo

    /* Mark of the Wild */
    public function test_mark_of_the_wild_gives_target_minion_taunt_and_2_2() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCard('Mark of the Wild', 1, [$chillwind_yeti]);
        $this->assertEquals(6, $chillwind_yeti->getAttack());
        $this->assertEquals(7, $chillwind_yeti->getHealth());
        $this->assertTrue($chillwind_yeti->hasMechanic(Mechanics::$TAUNT));
    }

    /* Mind Blast */
    public function test_mind_blast_deals_five_damage_to_opponent_hero() {
        $player2 = $this->game->getPlayer2();
        $this->playCard('Mind Blast', 1);
        $this->assertEquals(25, $player2->getHero()->getHealth());
    }

    /* Mind Control */
    // todo

    /* Mind Vision */
    // todo

    /* Mirror Image */
    // todo recursion due to spell and minion named the same
//    public function test_mirror_image_summons_two_mirror_image_minions() {
//        $this->playCard('Mirror Image', 1);
//        $minions = $this->game->getPlayer1()->getMinionsInPlay();
//        $this->assertEquals(2, count($minions));
//        $this->assertEquals('Mirror Image', $minions[0]->getName());
//        $this->assertTrue($minions[0]->hasMechanic(Mechanics::$TAUNT));
//        $this->assertEquals('Mirror Image', $minions[1]->getName());
//        $this->assertTrue($minions[1]->hasMechanic(Mechanics::$TAUNT));
//    }

    /* Moonfire */
    public function test_moonfire_deals_one_damage_to_target_character() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCard('Moonfire', 1, [$chillwind_yeti]);
        $this->assertEquals(4, $chillwind_yeti->getHealth());
    }

    /* Mortal Coil */
    // todo

    /* Multi-Shot */
    // todo

    /* Polymorph */
    // todo

    /* Power Word: Shield */
    public function test_power_word_shield_gives_target_minion_two_health_and_draws_one_card() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCard('Power Word: Shield', 1, [$chillwind_yeti]);
        $this->assertEquals(7, $chillwind_yeti->getHealth());
        $this->assertEquals(1, $this->game->getPlayer1()->getHandSize());
    }

    /* Rockbiter Weapon */
    public function test_rockbiter_weapon_gives_friendly_hero_three_attack() {
        $this->playCardStrict('Rockbiter Weapon', 1, 1);
        $player1 = $this->game->getPlayer1();
        $this->assertEquals(3, $player1->getHero()->getAttack());
        $player1->passTurn();
        $this->assertEquals(0, $player1->getHero()->getAttack());
    }

    /* Sacrificial Pact */
    // todo

    /* Sap */
    // todo

    /* Savage Roar */
    public function test_savage_roar_gives_friendly_characters_two_attack_this_turn() {
        $wisp           = $this->playCard('Wisp', 1);
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCardStrict('Savage Roar', 1, 3);
        $this->assertEquals(3, $wisp->getAttack());
        $this->assertEquals(6, $chillwind_yeti->getAttack());
        $this->assertEquals(2, $this->game->getPlayer1()->getHero()->getAttack());
    }

    /* Shadow Bolt */
    public function test_shadow_bolt_deals_four_damage_to_target_minion() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCard('Shadow Bolt', 1, [$chillwind_yeti]);
        $this->assertEquals(1, $chillwind_yeti->getHealth());
    }

    /* Shadow Word: Death */
    // todo

    /* Shadow Word: Pain */
    // todo

    /* Shield Block */
    public function test_shield_block_gains_five_armor_and_draws_one_card() {
        $this->playCard('Shield Block', 1);
        $this->assertEquals(5, $this->game->getPlayer1()->getHero()->getArmor());
        $this->assertEquals(1, $this->game->getPlayer1()->getHandSize());
    }

    /* Shiv */
    public function test_shiv_deals_one_damage_and_draws_one_card() {
        $chillwind_yeti = $this->playCard('Chillwind Yeti', 1);
        $this->playCard('Shiv', 2, [$chillwind_yeti]);
        $this->assertEquals(4, $chillwind_yeti->getHealth());
        $this->assertEquals(1, $this->game->getPlayer2()->getHandSize());
    }

    /* Sinister Strike */
    public function test_sinister_strike_deals_three_damage_to_opponent_hero() {
        $this->playCard('Sinister Strike', 1);
        $this->assertEquals(27, $this->game->getPlayer2()->getHero()->getHealth());
    }

    /* Soulfire */
    // todo

    /* Sprint */
    public function test_sprint_draws_four_cards() {
        $this->playCard('Sprint', 1);
        $this->assertEquals(4, $this->game->getPlayer1()->getHandSize());
    }

    /* Starfire */
    public function test_starfire_deals_five_damage_and_draws_one_card() {
        $archmage = $this->playCard('Archmage', 1);
        $this->playCard('Starfire', 2, [$archmage]);
        $this->assertEquals(2, $archmage->getHealth());
        $this->assertEquals(1, $this->game->getPlayer2()->getHandSize());
    }

    /* Swipe */
    // todo

    /* Totemic Might */
    public function test_totemic_might_gives_all_totems_two_health() {
        $healing_totem      = $this->playCard('Healing Totem', 1);
        $wrath_of_air_totem = $this->playCard('Wrath of Air Totem', 1);
        $this->playCard('Totemic Might', 1);
        $this->assertEquals(4, $healing_totem->getHealth());
        $this->assertEquals(4, $wrath_of_air_totem->getHealth());
    }

    /* Tracking */
    // todo

    /* Vanish */
    // todo

    /* Whirlwind */
    public function test_whirlwind_deals_one_damage_to_all_minions() {
        $chillwind_yeti1 = $this->playCard('Chillwind Yeti', 1);
        $chillwind_yeti2 = $this->playCard('Chillwind Yeti', 2);
        $this->playCard('Whirlwind', 1);
        $this->assertEquals(4, $chillwind_yeti1->getHealth());
        $this->assertEquals(4, $chillwind_yeti2->getHealth());
    }

    /* Wild Growth */
    public function test_playing_wild_growth_adds_one_mana_crystal() {
        $this->playCardStrict('Wild Growth', 1, 2, []);
        $this->assertEquals(3, $this->game->getPlayer1()->getManaCrystalCount());
    }

    /* Windfury */
    public function test_playing_spell_windfury_gives_a_minion_windfury() {
        $wisp = $this->playCard('Wisp', 1);
        $this->playCard('Windfury', 1, [$wisp]);
        $this->assertTrue($wisp->hasMechanic(Mechanics::$WINDFURY));
    }

}