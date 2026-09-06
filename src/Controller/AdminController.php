<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Creation;
use App\Entity\ContactRequest;
use App\Entity\PageContent;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\CreationRepository;
use App\Repository\ContactRequestRepository;
use App\Repository\PageContentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(
        CreationRepository $creationRepo,
        ContactRequestRepository $contactRepo,
        CategoryRepository $categoryRepo
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'total_creations' => count($creationRepo->findAll()),
            'total_categories' => count($categoryRepo->findAll()),
            'pending_contacts' => $contactRepo->countByStatus('pending'),
            'recent_contacts' => $contactRepo->findBy([], ['createdAt' => 'DESC'], 5),
            'menu_counts' => [
                'creations' => count($creationRepo->findAll()),
                'categories' => count($categoryRepo->findAll()),
                'contacts_pending' => $contactRepo->countByStatus('pending'),
            ],
        ]);
    }

    #[Route('/categories', name: 'admin_categories')]
    public function categories(
        CategoryRepository $categoryRepo,
        CreationRepository $creationRepo,
        ContactRequestRepository $contactRepo,
        Request $request
    ): Response {
        $search = $request->query->get('search', '');
        
        if ($search) {
            $categories = $categoryRepo->createQueryBuilder('c')
                ->where('c.name LIKE :search OR c.description LIKE :search OR c.slug LIKE :search')
                ->setParameter('search', '%' . $search . '%')
                ->orderBy('c.name', 'ASC')
                ->getQuery()
                ->getResult();
        } else {
            $categories = $categoryRepo->findBy([], ['name' => 'ASC']);
        }
        
        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
            'search' => $search,
            'menu_counts' => [
                'creations' => count($creationRepo->findAll()),
                'categories' => count($categoryRepo->findAll()),
                'contacts_pending' => $contactRepo->countByStatus('pending'),
            ],
        ]);
    }

    #[Route('/categories/new', name: 'admin_category_new', methods: ['GET', 'POST'])]
    public function newCategory(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($request->isMethod('POST')) {
            $category = new Category();
            $category->setName($request->request->get('name'));
            $category->setSlug($slugger->slug($request->request->get('name'))->lower());
            $category->setDescription($request->request->get('description'));

            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Catégorie créée avec succès');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/categories/new.html.twig');
    }

    #[Route('/categories/{id}/edit', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function editCategory(Category $category, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($request->isMethod('POST')) {
            $category->setName($request->request->get('name'));
            $category->setSlug($slugger->slug($request->request->get('name'))->lower());
            $category->setDescription($request->request->get('description'));

            $em->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/categories/edit.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/categories/{id}/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function deleteCategory(Category $category, EntityManagerInterface $em): Response
    {
        $em->remove($category);
        $em->flush();

        $this->addFlash('success', 'Catégorie supprimée avec succès');
        return $this->redirectToRoute('admin_categories');
    }

    #[Route('/creations', name: 'admin_creations')]
    public function creations(
        CreationRepository $creationRepo,
        CategoryRepository $categoryRepo,
        ContactRequestRepository $contactRepo,
        Request $request
    ): Response {
        $search = $request->query->get('search', '');
        
        if ($search) {
            $creations = $creationRepo->createQueryBuilder('c')
                ->leftJoin('c.category', 'cat')
                ->where('c.title LIKE :search OR c.description LIKE :search OR cat.name LIKE :search')
                ->setParameter('search', '%' . $search . '%')
                ->orderBy('c.createdAt', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $creations = $creationRepo->findBy([], ['createdAt' => 'DESC']);
        }
        
        return $this->render('admin/creations/index.html.twig', [
            'creations' => $creations,
            'search' => $search,
            'menu_counts' => [
                'creations' => count($creationRepo->findAll()),
                'categories' => count($categoryRepo->findAll()),
                'contacts_pending' => $contactRepo->countByStatus('pending'),
            ],
        ]);
    }

    #[Route('/creations/new', name: 'admin_creation_new', methods: ['GET', 'POST'])]
    public function newCreation(
        Request $request,
        EntityManagerInterface $em,
        CategoryRepository $categoryRepo
    ): Response {
        if ($request->isMethod('POST')) {
            $creation = new Creation();
            $creation->setTitle($request->request->get('title'));
            $creation->setDescription($request->request->get('description'));
            $creation->setIsPublished($request->request->get('is_published') === '1');
            
            $categoryId = $request->request->get('category');
            $category = $categoryRepo->find($categoryId);
            $creation->setCategory($category);

            // Gestion des images multiples
            $imageFiles = $request->files->get('images');
            $uploadedImages = [];
            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    if ($imageFile) {
                        $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                        $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads', $newFilename);
                        $uploadedImages[] = $newFilename;
                    }
                }
            }
            if (!empty($uploadedImages)) {
                $creation->setImages($uploadedImages);
            }

            $em->persist($creation);
            $em->flush();

            $this->addFlash('success', 'Création ajoutée avec succès');
            return $this->redirectToRoute('admin_creations');
        }

        return $this->render('admin/creations/new.html.twig', [
            'categories' => $categoryRepo->findAll(),
        ]);
    }

    #[Route('/creations/{id}/edit', name: 'admin_creation_edit', methods: ['GET', 'POST'])]
    public function editCreation(
        Creation $creation,
        Request $request,
        EntityManagerInterface $em,
        CategoryRepository $categoryRepo
    ): Response {
        if ($request->isMethod('POST')) {
            $creation->setTitle($request->request->get('title'));
            $creation->setDescription($request->request->get('description'));
            $creation->setIsPublished($request->request->get('is_published') === '1');
            
            $categoryId = $request->request->get('category');
            $category = $categoryRepo->find($categoryId);
            $creation->setCategory($category);

            // Gestion de l'ordre des images (existantes et nouvelles)
            $imageOrder = $request->request->get('image_order');
            $imageFiles = $request->files->get('images');
            
            // Upload des nouvelles images d'abord
            $uploadedFiles = [];
            if ($imageFiles) {
                foreach ($imageFiles as $index => $imageFile) {
                    if ($imageFile) {
                        $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                        $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads', $newFilename);
                        $uploadedFiles[$index] = $newFilename;
                    }
                }
            }
            
            // Reconstruire le tableau d'images dans l'ordre visuel
            if ($imageOrder) {
                $orderData = json_decode($imageOrder, true);
                if ($orderData && is_array($orderData)) {
                    $finalImages = [];
                    
                    foreach ($orderData as $item) {
                        if (isset($item['type'])) {
                            if ($item['type'] === 'existing' && isset($item['name'])) {
                                // Image existante
                                $finalImages[] = $item['name'];
                            } elseif ($item['type'] === 'new' && isset($item['index'])) {
                                // Nouvelle image - utiliser le fichier uploadé correspondant
                                $fileIndex = $item['index'];
                                if (isset($uploadedFiles[$fileIndex])) {
                                    $finalImages[] = $uploadedFiles[$fileIndex];
                                }
                            }
                        }
                    }
                    
                    $creation->setImages($finalImages);
                }
            } elseif (!empty($uploadedFiles)) {
                // Pas d'ordre spécifié, ajouter les nouvelles images à la fin
                $currentImages = $creation->getImages() ?? [];
                $creation->setImages(array_merge($currentImages, array_values($uploadedFiles)));
            }

            $em->flush();

            $this->addFlash('success', 'Création modifiée avec succès');
            return $this->redirectToRoute('admin_creations');
        }

        return $this->render('admin/creations/edit.html.twig', [
            'creation' => $creation,
            'categories' => $categoryRepo->findAll(),
        ]);
    }

    #[Route('/creations/{id}/delete-image', name: 'admin_creation_delete_image', methods: ['POST'])]
    public function deleteCreationImage(
        Creation $creation,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $imageToDelete = $request->request->get('image');
        $currentImages = $creation->getImages() ?? [];
        
        $key = array_search($imageToDelete, $currentImages);
        if ($key !== false) {
            // Supprimer le fichier physique
            $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $imageToDelete;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            // Retirer l'image du tableau
            unset($currentImages[$key]);
            $creation->setImages(array_values($currentImages));
            $em->flush();
            
            $this->addFlash('success', 'Image supprimée avec succès');
        }
        
        return $this->redirectToRoute('admin_creation_edit', ['id' => $creation->getId()]);
    }

    #[Route('/creations/{id}/delete', name: 'admin_creation_delete', methods: ['POST'])]
    public function deleteCreation(Creation $creation, EntityManagerInterface $em): Response
    {
        $em->remove($creation);
        $em->flush();

        $this->addFlash('success', 'Création supprimée avec succès');
        return $this->redirectToRoute('admin_creations');
    }

    #[Route('/contacts', name: 'admin_contacts')]
    public function contacts(
        ContactRequestRepository $contactRepo,
        CreationRepository $creationRepo,
        CategoryRepository $categoryRepo,
        Request $request
    ): Response {
        $search = $request->query->get('search', '');
        $statusFilter = $request->query->get('status', 'pending');
        
        $qb = $contactRepo->createQueryBuilder('c');
        
        if ($statusFilter && $statusFilter !== 'all') {
            $qb->where('c.status = :status')
               ->setParameter('status', $statusFilter);
        }
        
        if ($search) {
            if ($statusFilter && $statusFilter !== 'all') {
                $qb->andWhere('c.name LIKE :search OR c.email LIKE :search OR c.phone LIKE :search OR c.message LIKE :search');
            } else {
                $qb->where('c.name LIKE :search OR c.email LIKE :search OR c.phone LIKE :search OR c.message LIKE :search');
            }
            $qb->setParameter('search', '%' . $search . '%');
        }
        
        $contacts = $qb->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
        
        return $this->render('admin/contacts/index.html.twig', [
            'contacts' => $contacts,
            'search' => $search,
            'status_filter' => $statusFilter,
            'menu_counts' => [
                'creations' => count($creationRepo->findAll()),
                'categories' => count($categoryRepo->findAll()),
                'contacts_pending' => $contactRepo->countByStatus('pending'),
            ],
        ]);
    }

    #[Route('/contacts/{id}/view', name: 'admin_contact_view')]
    public function viewContact(
        ContactRequest $contact,
        CreationRepository $creationRepo,
        CategoryRepository $categoryRepo,
        ContactRequestRepository $contactRepo
    ): Response {
        return $this->render('admin/contacts/view.html.twig', [
            'contact' => $contact,
            'menu_counts' => [
                'creations' => count($creationRepo->findAll()),
                'categories' => count($categoryRepo->findAll()),
                'contacts_pending' => $contactRepo->countByStatus('pending'),
            ],
        ]);
    }

    #[Route('/contacts/{id}/process', name: 'admin_contact_process', methods: ['POST'])]
    public function processContact(ContactRequest $contact, EntityManagerInterface $em): Response
    {
        $contact->setStatus('processed');
        $contact->setProcessedAt(new \DateTime());
        $em->flush();

        $this->addFlash('success', 'Demande marquée comme traitée');
        return $this->redirectToRoute('admin_contacts');
    }

    #[Route('/contacts/{id}/pending', name: 'admin_contact_pending', methods: ['POST'])]
    public function pendingContact(ContactRequest $contact, EntityManagerInterface $em): Response
    {
        $contact->setStatus('pending');
        $contact->setConsultedAt(null);
        $contact->setProcessedAt(null);
        $em->flush();

        $this->addFlash('success', 'Demande remise à traiter');
        return $this->redirectToRoute('admin_contacts');
    }

    #[Route('/contacts/{id}/delete', name: 'admin_contact_delete', methods: ['POST'])]
    public function deleteContact(ContactRequest $contact, EntityManagerInterface $em): Response
    {
        $em->remove($contact);
        $em->flush();

        $this->addFlash('success', 'Demande supprimée');
        return $this->redirectToRoute('admin_contacts');
    }

    #[Route('/content', name: 'admin_content')]
    public function content(
        PageContentRepository $contentRepo,
        CreationRepository $creationRepo,
        CategoryRepository $categoryRepo,
        ContactRequestRepository $contactRepo,
        Request $request
    ): Response {
        $search = $request->query->get('search', '');
        
        if ($search) {
            $contents = $contentRepo->createQueryBuilder('c')
                ->where('c.key LIKE :search OR c.value LIKE :search OR c.section LIKE :search')
                ->setParameter('search', '%' . $search . '%')
                ->orderBy('c.section', 'ASC')
                ->getQuery()
                ->getResult();
        } else {
            $contents = $contentRepo->findBy([], ['section' => 'ASC']);
        }
        
        return $this->render('admin/content/index.html.twig', [
            'contents' => $contents,
            'search' => $search,
            'menu_counts' => [
                'creations' => count($creationRepo->findAll()),
                'categories' => count($categoryRepo->findAll()),
                'contacts_pending' => $contactRepo->countByStatus('pending'),
            ],
        ]);
    }

    #[Route('/content/{id}/edit', name: 'admin_content_edit', methods: ['GET', 'POST'])]
    public function editContent(PageContent $content, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $content->setValue($request->request->get('value'));
            $em->flush();

            $this->addFlash('success', 'Contenu modifié avec succès');
            return $this->redirectToRoute('admin_content');
        }

        return $this->render('admin/content/edit.html.twig', [
            'content' => $content,
        ]);
    }

    #[Route('/logo', name: 'admin_logo', methods: ['GET', 'POST'])]
    public function logo(
        Request $request,
        CreationRepository $creationRepo,
        CategoryRepository $categoryRepo,
        ContactRequestRepository $contactRepo
    ): Response {
        if ($request->isMethod('POST')) {
            $logoFile = $request->files->get('logo');
            if ($logoFile) {
                $logoFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/images',
                    'logo.png'
                );
                $this->addFlash('success', 'Logo mis à jour avec succès');
            }
            return $this->redirectToRoute('admin_logo');
        }

        return $this->render('admin/logo.html.twig', [
            'menu_counts' => [
                'creations' => count($creationRepo->findAll()),
                'categories' => count($categoryRepo->findAll()),
                'contacts_pending' => $contactRepo->countByStatus('pending'),
            ],
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function users(
        UserRepository $userRepo,
        CreationRepository $creationRepo,
        CategoryRepository $categoryRepo,
        ContactRequestRepository $contactRepo
    ): Response {
        return $this->render('admin/users/index.html.twig', [
            'users' => $userRepo->findAll(),
            'menu_counts' => [
                'creations' => count($creationRepo->findAll()),
                'categories' => count($categoryRepo->findAll()),
                'contacts_pending' => $contactRepo->countByStatus('pending'),
            ],
        ]);
    }

    #[Route('/users/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function newUser(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($request->isMethod('POST')) {
            $user = new User();
            $user->setEmail($request->request->get('email'));
            
            // Gestion des rôles
            $roles = $request->request->all('roles');
            if (empty($roles)) {
                $roles = ['ROLE_USER'];
            } else {
                // Toujours ajouter ROLE_USER
                if (!in_array('ROLE_USER', $roles)) {
                    $roles[] = 'ROLE_USER';
                }
            }
            $user->setRoles($roles);
            
            $plainPassword = $request->request->get('password');
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/new.html.twig');
    }

    #[Route('/users/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($request->isMethod('POST')) {
            $user->setEmail($request->request->get('email'));
            
            // Gestion des rôles
            $roles = $request->request->all('roles');
            
            // Empêcher un admin de retirer son propre rôle ROLE_ADMIN
            $currentUser = $this->getUser();
            if ($currentUser && $currentUser->getUserIdentifier() === $user->getEmail()) {
                if (in_array('ROLE_ADMIN', $user->getRoles()) && !in_array('ROLE_ADMIN', $roles)) {
                    $this->addFlash('error', 'Vous ne pouvez pas retirer votre propre rôle administrateur');
                    return $this->redirectToRoute('admin_user_edit', ['id' => $user->getId()]);
                }
            }
            
            if (empty($roles)) {
                $roles = ['ROLE_USER'];
            } else {
                // Toujours ajouter ROLE_USER
                if (!in_array('ROLE_USER', $roles)) {
                    $roles[] = 'ROLE_USER';
                }
            }
            $user->setRoles($roles);
            
            $plainPassword = $request->request->get('password');
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $em->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        // Empêcher la suppression de l'utilisateur connecté
        if ($this->getUser() && $this->getUser()->getId() === $user->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte');
            return $this->redirectToRoute('admin_users');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès');
        return $this->redirectToRoute('admin_users');
    }
}
