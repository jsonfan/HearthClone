<?php
/**
 * Created by PhpStorm.
 * User: stephenfiskell
 * Date: 1/25/15
 * Time: 5:19 PM
 */

namespace Card;


class AbstractCard {
	protected $name;
	protected $cost;
	protected $rarity;
	protected $foil;
	protected $owner; // Me or Them
}