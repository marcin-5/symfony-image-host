<?php

namespace App\Controller;

use App\Entity\Photo;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LatestPhotosController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    #[Route('/latest', name: 'app_latest_photos')]
    public function index(): Response
    {
        $entityManager = $this->doctrine->getManager();
        $latestPhotosPublic = $entityManager->getRepository(Photo::class)->findAllPublicPhotos();
        return $this->render('latest_photos/index.html.twig', [
            'latestPhotosPublic' => $latestPhotosPublic,
        ]);
    }
}
