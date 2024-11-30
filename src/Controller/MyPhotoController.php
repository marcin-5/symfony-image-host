<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Service\PhotoVisibilityService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class MyPhotoController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    #[Route('/my-photos', name: 'app_my_photos')]
    public function index(): Response
    {
        $entityManager = $this->doctrine->getManager();
        $myPhotos = $entityManager->getRepository(Photo::class)->findBy(['user' => $this->getUser()]);

        return $this->render('my_photo/index.html.twig', [
            'myPhotos' => $myPhotos,
        ]);
    }

    #[Route('/my-photos/set_visibility/{id}/{visibility}', name: 'app_change_photo_visibility')]
    public function myPhotoChangeVisibility(
        Request $request,
        PhotoVisibilityService $photoVisibilityService,
        int $id,
        bool $visibility
    ): Response {
        $messages = [
            '1' => 'publiczny',
            '0' => 'prywatny',
        ];
        if ($photoVisibilityService->makeVisible($id, $visibility)) {
            $this->addFlash('success', "Ustawiono plik jako {$messages[$visibility]}.");
        } else {
            $this->addFlash('error', "Wystąpił błąd przy ustawianiu pliku jako {$messages[$visibility]}.");
        }

        return $this->redirectToRoute($request->query->get('l') ? 'app_latest_photos' : 'app_my_photos');
    }

    #[Route('/my-photos/remove/{id}', name: 'app_remove_photo')]
    public function myPhotoRemove(int $id, Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();
        $myPhoto = $entityManager->getRepository(Photo::class)->find($id);
        if ($myPhoto && $this->getUser() === $myPhoto->getUser()) {
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

        return $this->redirectToRoute($request->query->get('l') ? 'app_latest_photos' : 'app_my_photos');
    }
}
