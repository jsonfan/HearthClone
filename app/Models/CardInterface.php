<?php
/**
 * Created by PhpStorm.
 * User: Kegimaro
 * Date: 8/23/15
 * Time: 3:39 PM
 */

namespace App\Models;


interface CardInterface
{
    public function load($handle=null);
}