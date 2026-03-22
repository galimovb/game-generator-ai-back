<?php

namespace App\Security\Voter;

use App\Entity\Game;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter для проверки прав доступа к играм
 */
class GameVoter extends Voter
{
    public const VIEW = 'GAME_VIEW';
    public const EDIT = 'GAME_EDIT';
    public const DELETE = 'GAME_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Game;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        // Для просмотра публичных игр авторизация не обязательна
        if ($attribute === self::VIEW && $subject->isPublic()) {
            return true;
        }

        if (!$user instanceof User) {
            return false;
        }

        /** @var Game $game */
        $game = $subject;

        // Администратор имеет все права
        if ($user->isAdmin()) {
            return true;
        }

        return match($attribute) {
            self::VIEW => $game->isPublic() || $this->isAuthor($user, $game),
            self::EDIT, self::DELETE => $this->isAuthor($user, $game),
            default => false,
        };
    }

    /**
     * Проверить, является ли пользователь автором игры
     */
    private function isAuthor(User $user, Game $game): bool
    {
        return $user->getId() === $game->getAuthor()?->getId();
    }
}
