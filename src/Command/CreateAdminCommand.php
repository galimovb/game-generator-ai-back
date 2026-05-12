<?php

namespace App\Command;

use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create admin user',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Admin email')
            ->addArgument('password', InputArgument::OPTIONAL, 'Admin password')
            ->addArgument('login', InputArgument::OPTIONAL, 'Admin login')
            ->addOption('interactive', 'i', null, 'Interactive mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        if (!$email || $input->getOption('interactive')) {
            $email = $io->ask('Email', null, function ($value) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new \RuntimeException('Invalid email');
                }
                return $value;
            });
        }

        $login = $input->getArgument('login');
        if (!$login || $input->getOption('interactive')) {
            $login = $io->ask('Login', 'admin');
        }

        $password = $input->getArgument('password');
        if (!$password || $input->getOption('interactive')) {
            $password = $io->askHidden('Password', function ($value) {
                if (strlen($value) < 6) {
                    throw new \RuntimeException('Password must be at least 6 characters');
                }
                return $value;
            });
        }

        $existingUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if ($existingUser) {
            $io->error("User with email {$email} already exists");

            if ($io->confirm('Make this user an admin?', false)) {
                $existingUser->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
                $this->entityManager->flush();
                $io->success("User {$email} is now admin");
                return Command::SUCCESS;
            }

            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setLogin($login);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success("Admin created: {$email} / {$login}");

        return Command::SUCCESS;
    }
}