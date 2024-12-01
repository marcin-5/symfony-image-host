<?php

namespace App\Command;

use App\Entity\Photo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:disable-photos-visibility',
    description: 'Set photos visibility as private for given user ID',
)]
class DisablePhotosVisibilityCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('user_id', InputArgument::REQUIRED, 'User ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $photoRepository = $this->entityManager->getRepository(Photo::class);
        $photosToSetPrivate = $photoRepository->findBy(['is_public' => '1', 'user' => $input->getArgument('user_id')]);
        foreach ($photosToSetPrivate as $photo) {
            $photo->setPublic(false);
            $this->entityManager->persist($photo);
        }
        $this->entityManager->flush();
        $output->writeln('Photos visibility set as private');

        return Command::SUCCESS;
    }
}
