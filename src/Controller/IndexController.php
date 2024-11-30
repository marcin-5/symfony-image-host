<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Form\UploadPhotoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    public function __construct(private readonly \Doctrine\Persistence\ManagerRegistry $doctrine)
    {
    }

    #[Route('/', name: 'app_index')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(UploadPhotoType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->getUser()) {
                /** @var UploadedFile $pictureFileName */
                $pictureFileName = $form->get('filename')->getData();
                if ($pictureFileName) {
                    $originalFilename = pathinfo($pictureFileName->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFileName = transliterator_transliterate(
                        'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
                        $originalFilename
                    );
                    try {
                        $newFilename = $safeFileName . '-' . uniqid() . '.' . $pictureFileName->guessExtension();
                        $pictureFileName->move('images/hosting', $newFilename);

                        $entityPhoto = new Photo();
                        $entityPhoto->setFilename($newFilename);
                        $entityPhoto->setPublic($form->get('public')->getData());
                        $entityPhoto->setUploadedAt(new \DateTimeImmutable());
                        $entityPhoto->setUser($this->getUser());

                        $entityManager = $this->doctrine->getManager();
                        $entityManager->persist($entityPhoto);
                        $entityManager->flush();

                        $this->addFlash('success', 'Plik został wgrany pomyślnie.');
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Wystąpił nieoczekiwany błąd.');
                    }
                }
            }
        }

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'form' => $form->createView(),
        ]);
    }
}
