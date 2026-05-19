<?php

namespace App\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken as BaseRefreshToken;

#[ORM\Entity]
#[ORM\Table(name: 'user_refresh_tokens')]
class RefreshToken extends BaseRefreshToken
{}
