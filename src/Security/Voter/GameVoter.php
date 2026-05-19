<?php

namespace App\Security\Voter;

use App\Game\Entity\Game;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class GameVoter extends Voter
{
    public const MANAGE = 'MANAGE_GAME';
    public const VIEW = 'VIEW_GAME';
    public const EDIT = 'EDIT_GAME';
    public const DELETE = 'DELETE_GAME';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::MANAGE, self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Game;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Game $game */
        $game = $subject;

        return match ($attribute) {
            self::MANAGE => $this->canManage($user, $game),
            self::VIEW => $this->canView($user, $game),
            self::EDIT => $this->canEdit($user, $game),
            self::DELETE => $this->canDelete($user, $game),
            default => false,
        };
    }

    private function canManage(UserInterface $user, Game $game): bool
    {
        // Проверка на админа
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Проверка на автора игры
        if ($user instanceof User && $user->getId() === $game->getAuthor()->getId()) {
            return true;
        }

        return false;
    }

    private function canView(UserInterface $user, Game $game): bool
    {
        return true;
    }

    private function canEdit(UserInterface $user, Game $game): bool
    {
        return $this->canManage($user, $game);
    }

    private function canDelete(UserInterface $user, Game $game): bool
    {
        return $this->canManage($user, $game);
    }
}
