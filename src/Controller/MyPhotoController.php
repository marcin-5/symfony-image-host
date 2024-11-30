<?php

namespace App\Controller;

use App\Entity\Photo;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MyPhotoController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    #[Route('/my-photos', name: 'app_my_photos')]
    public function index()
    {
    }

    #[Route('/my-photos/set_private/{id}', name: 'app_set_photo_as_private')]
    public function myPhotoSetAsPrivate(int $id): Response
    {
        $entityManager = $this->doctrine->getManager();
        $myPhoto = $entityManager->getRepository(Photo::class)->find($id);
        if ($this->getUser() === $myPhoto->getUser()) {
            try {
                $myPhoto->setPublic(false);
                $entityManager->persist($myPhoto);
                $entityManager->flush();
                $this->addFlash('success', 'Ustawiono plik jako prywatny.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Wystąpił błąd przy ustawianiu pliku jako prywatny.');
            }
        } else {
            $this->addFlash('warning', 'Nie jesteś właścicielem tego pliku.');
        }
        return $this->redirectToRoute('app_latest_photos');
    }

    #[Route('/my-photos/set_public/{id}', name: 'app_set_photo_as_public')]
    public function myPhotoSetAsPublic(int $id): Response
    {
        $entityManager = $this->doctrine->getManager();
        $myPhoto = $entityManager->getRepository(Photo::class)->find($id);
        if ($this->getUser() === $myPhoto->getUser()) {
            try {
                $myPhoto->setPublic(true);
                $entityManager->persist($myPhoto);
                $entityManager->flush();
                $this->addFlash('success', 'Ustawiono plik jako publiczny.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Wystąpił błąd przy ustawianiu pliku jako publiczny.');
            }
        } else {
            $this->addFlash('warning', 'Nie jesteś właścicielem tego pliku.');
        }
        return $this->redirectToRoute('app_latest_photos');
    }

    #[Route('/my-photos/remove/{id}', name: 'app_remove_photo')]
    public function myPhotoRemove(int $id): Response
    {
        $entityManager = $this->doctrine->getManager();
        $myPhoto = $entityManager->getRepository(Photo::class)->find($id);
        if ($this->getUser() === $myPhoto->getUser()) {
            try {
                $fileManager = new Filesystem();
                $fileManager->remove('images/hosting/' . $myPhoto->getFilename());
                if ($fileManager->exists('images/hosting/' . $myPhoto->getFilename())) {
                    $this->addFlash('error', 'Nie udało się usunąć pliku.');
                } else {
                    $entityManager->remove($myPhoto);
                    $entityManager->flush();
                    $this->addFlash('success', 'Plik został usunięty.');
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Wystąpił błąd przy usuwaniu pliku.');
            }
        } else {
            $this->addFlash('warning', 'Nie jesteś właścicielem tego pliku.');
        }
        return $this->redirectToRoute('app_latest_photos');
    }
}
