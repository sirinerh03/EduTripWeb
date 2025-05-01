<?php

namespace App\Controller;

use App\Entity\University;
use App\Form\UniversityType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/university')]
final class UniversityController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)//Injects the HttpClient used to fetch uni data from an external API.


    {
        $this->httpClient = $httpClient;
    }

    #[Route(name: 'app_university_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('search');// accept query param
        $page = $request->query->getInt('page', 1);
        $limit = 5; // Number of universities per page

        $queryBuilder = $entityManager->getRepository(University::class)->createQueryBuilder('u');//query to fetch uni from db
        
        // Get filter parameters
        $cityFilter = $request->query->get('city');
        $emailDomainFilter = $request->query->get('email_domain');
        
        // Start with a base where clause for search( store sql conditions)
        $whereClauses = [];
        $parameters = [];
        
        if ($searchTerm) {//search condition
            $whereClauses[] = '(LOWER(u.nom) LIKE :search OR LOWER(u.ville) LIKE :search)';
            $parameters['search'] = '%' . strtolower($searchTerm) . '%';
        }
        
        // Add city filter
        if ($cityFilter) {
            $whereClauses[] = 'u.ville = :city';
            $parameters['city'] = $cityFilter;
        }
        
        // Add email domain filter
        if ($emailDomainFilter) {
            $whereClauses[] = 'u.email LIKE :emailDomain';
            $parameters['emailDomain'] = '%' . $emailDomainFilter;
        }
        
        // Combine all where clauses
        if (!empty($whereClauses)) {
            $queryBuilder->where(implode(' AND ', $whereClauses));// joins all individual condition
            foreach ($parameters as $key => $value) {
                $queryBuilder->setParameter($key, $value);
            }
        }
        
        // Get unique cities for filter dropdown
        $citiesQuery = $entityManager->createQuery(
            'SELECT DISTINCT u.ville FROM App\\Entity\\University u WHERE u.ville IS NOT NULL ORDER BY u.ville ASC'
        );
        $cities = array_map(function($result) {
            return ['ville' => $result['ville']];
        }, $citiesQuery->getResult());
        
        // Get unique email domains for filter dropdown
        $emailDomainsQuery = $entityManager->createQuery(
            "SELECT DISTINCT SUBSTRING(u.email, LOCATE('@', u.email)) as domain FROM App\\Entity\\University u"
        );
        $emailDomains = array_map(function($result) {
            return $result['domain'];
        }, $emailDomainsQuery->getResult());
        
        // Default sorting by name
        $queryBuilder->orderBy('u.nom', 'ASC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $page,
            $limit
        );

        // Fetch real universities from external API if searching
        $staticUniversities = [
            [
                'name' => 'Harvard University',
                'country' => 'United States',
                'web_pages' => ['https://www.harvard.edu/'],
            ],
            [
                'name' => 'University of Oxford',
                'country' => 'United Kingdom',
                'web_pages' => ['https://www.ox.ac.uk/'],
            ],
            [
                'name' => 'University of Cambridge',
                'country' => 'United Kingdom',
                'web_pages' => ['https://www.cam.ac.uk/'],
            ],
            [
                'name' => 'Stanford University',
                'country' => 'United States',
                'web_pages' => ['https://www.stanford.edu/'],
            ],
            [
                'name' => 'Sorbonne University',
                'country' => 'France',
                'web_pages' => ['https://www.sorbonne-universite.fr/'],
            ],
            [
                'name' => 'University of Tokyo',
                'country' => 'Japan',
                'web_pages' => ['https://www.u-tokyo.ac.jp/'],
            ],
        ];
        $externalUniversities = [];
        if ($searchTerm) {
            try {
                $response = $this->httpClient->request('GET', 'https://universitiesapi.onrender.com/v1/api/universities', [
                    'query' => ['name' => $searchTerm]
                ]);
                if ($response->getStatusCode() === 200) {
                    $externalUniversities = $response->toArray();
                }
            } catch (\Exception $e) {
                // Fallback: filter static list by search term
                $externalUniversities = array_filter($staticUniversities, function($uni) use ($searchTerm) {
                    return stripos($uni['name'], $searchTerm) !== false;
                });
            }
            // If API returns nothing, fallback to static list
            if (empty($externalUniversities)) {
                $externalUniversities = array_filter($staticUniversities, function($uni) use ($searchTerm) {
                    return stripos($uni['name'], $searchTerm) !== false;
                });
            }
        }

        // Pass sort, direction, and search to template for UI state
        return $this->render('university/index.html.twig', [
            'universities' => $pagination,
            'search' => $searchTerm,
            'external_universities' => $externalUniversities,
            'cities' => $cities,
            'emailDomains' => $emailDomains,
            'selectedCity' => $cityFilter,
            'selectedEmailDomain' => $emailDomainFilter,
        ]);
    }

    #[Route('/back', name: 'app_university_back', methods: ['GET'])]
    public function index2(Request $request, EntityManagerInterface $entityManager): Response
    {
        $searchTerm = $request->query->get('search');//retrieve sort and search from url
        $sort = $request->query->get('sort', 'nom');
        $direction = strtolower($request->query->get('direction', 'asc')) === 'desc' ? 'DESC' : 'ASC';

        $allowedSortFields = ['nom', 'ville', 'email'];
        $sortField = in_array($sort, $allowedSortFields) ? $sort : 'nom';

        $queryBuilder = $entityManager->getRepository(University::class)->createQueryBuilder('u');
        if ($searchTerm) {
            $queryBuilder->where('LOWER(u.nom) LIKE :search OR LOWER(u.ville) LIKE :search')
                ->setParameter('search', '%' . strtolower($searchTerm) . '%');
        }
        $queryBuilder->orderBy('u.' . $sortField, $direction);
        $universities = $queryBuilder->getQuery()->getResult();

        return $this->render('university/index2.html.twig', [
            'universities' => $universities,
            'search' => $searchTerm,
            'sort' => $sortField,
            'direction' => $direction,
        ]);
    }//render the new ui with the searched item sorted ..

    #[Route('/new', name: 'app_university_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $university = new University();
        $form = $this->createForm(UniversityType::class, $university);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($university);
            $entityManager->flush();

            return $this->redirectToRoute('app_university_back', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('university/new.html.twig', [
            'university' => $university,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_university_show', methods: ['GET'])]
    public function show(University $university): Response
    {
        return $this->render('university/show.html.twig', [
            'university' => $university,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_university_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, University $university, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UniversityType::class, $university);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_university_back', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('university/edit.html.twig', [
            'university' => $university,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_university_delete', methods: ['POST'])]
    public function delete(Request $request, University $university, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $university->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($university);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_university_back', [], Response::HTTP_SEE_OTHER);
    }
}
