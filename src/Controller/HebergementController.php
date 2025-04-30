<?php

namespace App\Controller;

use App\Entity\Hebergement;
use App\Form\HebergementType;
use App\Repository\HebergementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\HebergementExcelExporter;


#[Route('/hebergement')]
final class HebergementController extends AbstractController
{
    #[Route('', name: 'app_hebergement_index')]
    public function index(Request $request, PaginatorInterface $paginator, HebergementRepository $repo): Response
    {
        $criteria = [
            'nom' => $request->query->get('nom'),
            'type' => $request->query->get('type'),
            'min_capacity' => $request->query->get('min_capacity'),
            'max_price' => $request->query->get('max_price'),
            'disponibilite' => $request->query->get('disponibilite'),
            'sort' => $request->query->get('sort'), // Add sort parameter
        ];
    
        $queryBuilder = $repo->getPaginatedQueryBuilder($criteria);
    
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5,
            [
                'pageParameterName' => 'page',
                'sortFieldParameterName' => null, // Disable KnpPaginator's sorting
                'sortDirectionParameterName' => null, // Disable KnpPaginator's sorting
            ]
        );
    
        return $this->render('hebergement/index.html.twig', [
            'pagination' => $pagination,
            'current_sort' => $criteria['sort'], // Pass current sort to template
        ]);
    }
    #[Route('/list', name: 'app_hebergement_list', methods: ['GET'])]
public function list(Request $request, PaginatorInterface $paginator, HebergementRepository $hebergementRepository): Response
{
    $criteria = [
        'nom' => $request->query->get('nom'),
        'type' => $request->query->get('type'),
        'min_capacity' => $request->query->get('min_capacity'),
        'max_price' => $request->query->get('max_price'),
        'disponibilite' => $request->query->get('disponibilite'),
        'sort' => $request->query->get('sort'),
    ];

    $queryBuilder = $hebergementRepository->getPaginatedQueryBuilder($criteria);

    $pagination = $paginator->paginate(
        $queryBuilder,
        $request->query->getInt('page', 1),
        5,
        [
            'pageParameterName' => 'page',
            'sortFieldParameterName' => null, // Disable KnpPaginator's sorting
            'sortDirectionParameterName' => null, // Disable KnpPaginator's sorting
        ]
    );

    return $this->render('hebergement/list.html.twig', [
        'pagination' => $pagination,
        'current_sort' => $criteria['sort']
    ]);
}
    #[Route('/new', name: 'app_hebergement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hebergement = new Hebergement();
        $form = $this->createForm(HebergementType::class, $hebergement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageh')->getData();
        
            if ($imageFile) {
                try {
                    $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/img/',
                        $newFilename
                    );
                    $hebergement->setImageh($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
                    return $this->render('hebergement/new.html.twig', [
                        'hebergement' => $hebergement,
                        'form' => $form->createView(),
                    ]);
                }
            }
        
            $entityManager->persist($hebergement);
            $entityManager->flush();
            $this->addFlash('success', 'Hébergement créé avec succès!');
        
            return $this->redirectToRoute('app_hebergement_index');
        }

        return $this->render('hebergement/new.html.twig', [
            'hebergement' => $hebergement,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/admin/search', name: 'app_hebergement_admin_search', methods: ['GET'])]
    public function adminSearch(Request $request, HebergementRepository $hebergementRepository, PaginatorInterface $paginator): JsonResponse
    {
        $criteria = [
            'nom' => $request->query->get('nom'),
            'type' => $request->query->get('type'),
            'min_capacity' => $request->query->get('min_capacity'),
            'max_price' => $request->query->get('max_price'),
            'disponibilite' => $request->query->get('disponibilite'),
        ];
    
        $queryBuilder = $hebergementRepository->getPaginatedQueryBuilder($criteria);
        
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5
        );
    
        $results = [];
        foreach ($pagination as $hebergement) {
            $results[] = [
                'id' => $hebergement->getId(),
                'nom' => $hebergement->getNomh(),
                'type' => $hebergement->getTypeh(),
                'adresse' => $hebergement->getAdressh(),
                'capacite' => $hebergement->getCapaciteh(),
                'prix' => $hebergement->getPrixh(),
                'disponibilite' => $hebergement->getDisponibleh(),
                'description' => $hebergement->getDescriptionh(),
                'image' => $hebergement->getImageh(),
                'show_url' => $this->generateUrl('app_hebergement_show', ['id_hebergement' => $hebergement->getId()]),
                'edit_url' => $this->generateUrl('app_hebergement_edit', ['id_hebergement' => $hebergement->getId()]),
                'delete_url' => $this->generateUrl('app_hebergement_delete', ['id_hebergement' => $hebergement->getId()]),
                'reserver_url' => $this->generateUrl('app_reservation_hebergement_new', ['id_hebergement' => $hebergement->getId()]),
                'csrf_token' => $this->container->get('security.csrf.token_manager')->getToken('delete'.$hebergement->getId())->getValue(),
            ];
        }
    
        return $this->json([
            'items' => $results,
            'pagination' => [
                'current_page' => $pagination->getCurrentPageNumber(),
                'total_pages' => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()),
                'total_items' => $pagination->getTotalItemCount(),
            ]
        ]);
    }

    #[Route('/list/search', name: 'app_hebergement_search', methods: ['GET'])]
    public function search(Request $request, HebergementRepository $hebergementRepository, PaginatorInterface $paginator): JsonResponse
    {
        $criteria = [
            'nom' => $request->query->get('nom'),
            'type' => $request->query->get('type'),
            'min_capacity' => $request->query->get('min_capacity'),
            'max_price' => $request->query->get('max_price'),
            'disponibilite' => $request->query->get('disponibilite'),
            'sort' => $request->query->get('sort'),
        ];
    
        $queryBuilder = $hebergementRepository->getPaginatedQueryBuilder($criteria);
    
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5
        );
    
        $results = [];
        foreach ($pagination as $hebergement) {
            $results[] = [
                'id' => $hebergement->getId(),
                'nom' => $hebergement->getNomh(),
                'type' => $hebergement->getTypeh(),
                'capacite' => $hebergement->getCapaciteh(),
                'prix' => $hebergement->getPrixh(),
                'disponibilite' => $hebergement->getDisponibleh(),
                'image' => $hebergement->getImageh(),
                'description' => $hebergement->getDescriptionh(),
                'url' => $this->generateUrl('app_hebergement_show_carousel', ['id_hebergement' => $hebergement->getId()])
            ];
        }
    
        return $this->json([
            'items' => $results,
            'pagination' => [
                'current_page' => $pagination->getCurrentPageNumber(),
                'total_pages' => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()),
                'total_items' => $pagination->getTotalItemCount(),
            ]
        ]);
    }

    #[Route('/carousel', name: 'app_hebergement_carousel', methods: ['GET'])]
    public function carousel(HebergementRepository $hebergementRepository): Response
    {
        return $this->render('hebergement/carousel.html.twig', [
            'hebergements' => $hebergementRepository->findAll(),
        ]);
    }

    #[Route('/{id_hebergement}', name: 'app_hebergement_show', methods: ['GET'])]
    public function show(Hebergement $hebergement): Response
    {
        return $this->render('hebergement/show.html.twig', [
            'hebergement' => $hebergement,
        ]);
    }

    #[Route('/{id_hebergement}/edit', name: 'app_hebergement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hebergement $hebergement, EntityManagerInterface $entityManager): Response
    {
 
        $form = $this->createForm(HebergementType::class, $hebergement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageh')->getData();
        
            if ($imageFile) {
                try {
                    // Delete old image if it exists
                    $oldImage = $hebergement->getImageh();
                    if ($oldImage && file_exists($this->getParameter('kernel.project_dir') . '/public/img/' . $oldImage)) {
                        unlink($this->getParameter('kernel.project_dir') . '/public/img/' . $oldImage);
                    }

                    $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/img/',
                        $newFilename
                    );
                    $hebergement->setImageh($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
                    return $this->render('hebergement/edit.html.twig', [
                        'hebergement' => $hebergement,
                        'form' => $form->createView(),
                    ]);
                }
            }
        
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Hébergement modifié avec succès!');
                return $this->redirectToRoute('app_hebergement_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification : ' . $e->getMessage());
            }
        }

        return $this->render('hebergement/edit.html.twig', [
            'hebergement' => $hebergement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id_hebergement}/delete', name: 'app_hebergement_delete', methods: ['POST'])]
    public function delete(Request $request, Hebergement $hebergement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $hebergement->getId(), $request->getPayload()->getString('_token'))) {
            // Delete image file if it exists
            $image = $hebergement->getImageh();
            if ($image && file_exists($this->getParameter('kernel.project_dir') . '/public/img/' . $image)) {
                unlink($this->getParameter('kernel.project_dir') . '/public/img/' . $image);
            }

            $entityManager->remove($hebergement);
            $entityManager->flush();
            $this->addFlash('success', 'Hébergement supprimé avec succès!');
        }

        return $this->redirectToRoute('app_hebergement_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/carousel/{id_hebergement}', name: 'app_hebergement_show_carousel', methods: ['GET'])]
    public function showFromCarousel(Hebergement $hebergement): Response
    {
        return $this->render('hebergement/show_carousel.html.twig', [
            'hebergement' => $hebergement,
        ]);
    }

    #[Route('/qr/hebergement/{id_hebergement}', name: 'app_hebergement_qr_show', methods: ['GET'])]
    public function showQrDetails(Hebergement $hebergement): Response
    {
        return $this->render('hebergement/qr_show.html.twig', [
            'hebergement' => $hebergement,
        ]);
    }
    #[Route('/admin/hebergement/export-excel', name: 'app_hebergement_admin_export_excel')]
public function exportExcelAdmin(
    HebergementRepository $hebergementRepository, 
    HebergementExcelExporter $exporter
): Response {
    $hebergements = $hebergementRepository->findAllWithReservations();
    return $exporter->export($hebergements);
}
}