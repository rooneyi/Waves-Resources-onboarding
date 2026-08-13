<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Command;

use App\Entity\User;
use App\Enum\Role;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-admin',
    description: 'Create or update the seeded administrator account',
)]
final class SeedAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Admin email', 'admin@waves.local')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Admin password', 'AdminPass1')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Admin full name', 'Waves Administrator');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = strtolower(trim((string) $input->getOption('email')));
        $password = (string) $input->getOption('password');
        $name = trim((string) $input->getOption('name'));

        $user = $this->userRepository->findOneByEmail($email);

        if (!$user instanceof User) {
            $user = new User();
            $user->setEmail($email);
            $user->setFullName($name);
            $this->entityManager->persist($user);
            $io->success(sprintf('Created admin user %s', $email));
        } else {
            $user->setFullName($name);
            $io->success(sprintf('Updated admin user %s', $email));
        }

        $user->setRoles([Role::Admin->value, Role::User->value]);
        $user->setEmailVerified(true);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->flush();

        $io->writeln(sprintf('Email: %s', $email));
        $io->writeln('Password: (the value you provided — not logged)');

        return Command::SUCCESS;
    }
}
