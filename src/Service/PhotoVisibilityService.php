<?php

namespace App\Service;

use App\Entity\Photo;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class PhotoVisibilityService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private PhotoRepository $photoRepository,
        private Security $security,
    ) {
    }

    public function makeVisible(int $id, bool $visibility): bool
    {
        $photo = $this->photoRepository->find($id);
        if ($this->isPhotoBelongsToCurrentUser($photo)) {
            $photo->setPublic($visibility);
            $this->entityManager->persist($photo);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    private function isPhotoBelongsToCurrentUser(Photo $photo): bool
    {
        return $this->security->getUser() === $photo->getUser();
    }
}
