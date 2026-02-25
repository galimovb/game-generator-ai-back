<?php

namespace App\Listener;

use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class CustomAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private JWTTokenManagerInterface $jwtManager;
    private RefreshTokenGeneratorInterface $refreshTokenGenerator;
    private RefreshTokenManagerInterface $refreshTokenManager;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        RefreshTokenGeneratorInterface $refreshTokenGenerator,
        RefreshTokenManagerInterface $refreshTokenManager
    ) {
        $this->jwtManager = $jwtManager;
        $this->refreshTokenGenerator = $refreshTokenGenerator;
        $this->refreshTokenManager = $refreshTokenManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $user = $token->getUser();

        // Создаём access token
        $accessToken = $this->jwtManager->create($user);

        // Генерируем refresh token через генератор (возвращает готовый объект)
        // TTL в секундах: 30 дней = 30 * 24 * 60 * 60 = 2592000
        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl(
            $user,
            2592000 // 30 дней в секундах
        );

        // Сохраняем refresh token в базу
        $this->refreshTokenManager->save($refreshToken);

        // Получаем строку токена
        $refreshTokenString = $refreshToken->getRefreshToken();

        $response = new JsonResponse([
            'access_token' => $accessToken,
        ]);

        // Устанавливаем refresh token в httpOnly cookie
        $response->headers->setCookie(
            Cookie::create('refresh_token')
                ->withValue($refreshTokenString)
                ->withHttpOnly(true)
                //->withSecure(true)
                ->withSameSite('Lax')
                ->withExpires(new \DateTime('+30 days'))
        );

        return $response;
    }
}