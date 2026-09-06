<?php

namespace App\Controller;

use App\Entity\ContactRequest;
use App\Repository\CategoryRepository;
use App\Repository\CreationRepository;
use App\Repository\PageContentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    public function __construct(
        private PageContentRepository $contentRepo
    ) {}

    private function getContents(): array
    {
        $allContents = $this->contentRepo->findAll();
        $contents = [];
        foreach ($allContents as $content) {
            $contents[$content->getSection()][$content->getKey()] = $content->getValue();
        }
        return $contents;
    }

    #[Route('/', name: 'app_home')]
    public function index(
        CreationRepository $creationRepo,
        CategoryRepository $categoryRepo
    ): Response {
        return $this->render('main/home.html.twig', [
            'latest_creations' => $creationRepo->findLatestPublished(6),
            'categories' => $categoryRepo->findAll(),
            'contents' => $this->getContents(),
        ]);
    }

    #[Route('/api/creations/load-more', name: 'api_load_more_creations')]
    public function loadMoreCreations(
        Request $request,
        CreationRepository $creationRepo
    ): JsonResponse {
        $categoryId = $request->query->get('category');
        $offset = (int) $request->query->get('offset', 0);
        $limit = (int) $request->query->get('limit', 9);

        if ($categoryId) {
            $creations = $creationRepo->findPublishedByCategory($categoryId, $limit, $offset);
            $total = $creationRepo->countPublishedByCategory($categoryId);
        } else {
            // Pour "Tout", on doit aussi utiliser offset
            $qb = $creationRepo->createQueryBuilder('c')
                ->where('c.isPublished = :published')
                ->setParameter('published', true)
                ->orderBy('c.createdAt', 'DESC')
                ->setMaxResults($limit)
                ->setFirstResult($offset);
            $creations = $qb->getQuery()->getResult();
            $total = count($creationRepo->findBy(['isPublished' => true]));
        }

        $data = [];
        foreach ($creations as $creation) {
            $images = $creation->getImages();
            $data[] = [
                'id' => $creation->getId(),
                'title' => $creation->getTitle(),
                'description' => $creation->getDescription(),
                'images' => $images ?? [],
                'firstImage' => $creation->getFirstImage(),
                'category' => $creation->getCategory()->getSlug(),
                'categoryName' => $creation->getCategory()->getName(),
            ];
        }

        return new JsonResponse([
            'creations' => $data,
            'hasMore' => ($offset + $limit) < $total,
        ]);
    }

    #[Route('/contact/submit', name: 'app_contact_submit', methods: ['POST'])]
    public function submitContact(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $contact = new ContactRequest();
        $contact->setName($request->request->get('name'));
        $contact->setEmail($request->request->get('email'));
        $contact->setPhone($request->request->get('phone'));
        $contact->setMessage($request->request->get('message'));

        $em->persist($contact);
        $em->flush();

        $this->addFlash('success', 'Votre message a été envoyé avec succès. Nous vous répondrons rapidement.');
        return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
    }

    #[Route('/a-propos', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('main/about.html.twig');
    }

    #[Route('/galerie', name: 'app_gallery')]
    public function gallery(): Response
    {
        return $this->render('main/gallery.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('main/contact.html.twig');
    }
}
