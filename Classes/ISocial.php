<?php
/**
 * Created by PhpStorm.
 * User: xterminate
 * Date: 11.03.2019
 * Time: 21:16
 */

namespace Classes;


interface ISocial
{
    public function getName();

    public function generateAuthUrl();

    public function generateTokenUrl();

    public function authorize($code);

    public function getUserInfo($token);
}