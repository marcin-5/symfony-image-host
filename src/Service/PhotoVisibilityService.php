<?php

namespace App\Service;

use App\Entity\Photo;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

readonly class PhotoVisibilityService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PhotoRepository $photoRepository,
        private Security $security,
    ) {
    }

    public function updatePhotoVisibility(int $id, bool $visibility): bool
    {
        $photo = $this->photoRepository->find($id);

        if ($photo && $this->security->getUser() === $photo->getUser()) {
            $this->setVisibility($photo, $visibility);
            return true;
        }

        return false;
    }

    private function setVisibility(Photo $photo, bool $visibility): void
    {
        $photo->setPublic($visibility);
        $this->entityManager->persist($photo);
        $this->entityManager->flush();
    }
}
