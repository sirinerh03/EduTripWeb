<?php

namespace App\Controller;

use App\Entity\Pack_agence;
use App\Form\PackAgenceType;
use App\Repository\Pack_agenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twilio\Rest\Client;

final class PackAgenceController extends AbstractController
{
    #[Route('/pack/agence', name: 'app_pack_agence')]
    public function index(Pack_agenceRepository $packAgenceRepository): Response
    {
        $packAgences = $packAgenceRepository->findAll();

        return $this->render('pack_agence/index.html.twig', [
            'packAgences' => $packAgences,
        ]);
    }

    #[Route('/pack/agence/liste', name: 'app_pack_agence_liste')]
    public function simpleList(Request $request, Pack_agenceRepository $packAgenceRepository): Response
    {
        $nom = $request->query->get('nom');
        $prixMin = $request->query->get('prix_min', 100);
        $prixMax = $request->query->get('prix_max', 10000);
        $dureeMin = $request->query->get('duree_min', 1);
        $dureeMax = $request->query->get('duree_max', 30);
        
        $queryBuilder = $packAgenceRepository->createQueryBuilder('p')
            ->leftJoin('p.id_agence', 'a')
            ->addSelect('a');
    
        $queryBuilder->andWhere('p.prix >= :prixMin')
                    ->andWhere('p.prix <= :prixMax')
                    ->setParameter('prixMin', (float)$prixMin)
                    ->setParameter('prixMax', (float)$prixMax);
    
        $queryBuilder->andWhere('p.duree >= :dureeMin')
                    ->andWhere('p.duree <= :dureeMax')
                    ->setParameter('dureeMin', (int)$dureeMin)
                    ->setParameter('dureeMax', (int)$dureeMax);
    
        if ($nom) {
            $queryBuilder->andWhere('p.nom_pk LIKE :nom')
                        ->setParameter('nom', '%'.$nom.'%');
        }
        
        $packAgences = $queryBuilder->getQuery()->getResult();
        
        if ($request->isXmlHttpRequest()) {
            return $this->render('pack_agence/_pack_cards.html.twig', [
                'packAgences' => $packAgences
            ]);
        }
        
        return $this->render('pack_agence/index2.html.twig', [
            'packAgences' => $packAgences,
            'prix_min' => $prixMin,
            'prix_max' => $prixMax,
            'duree_min' => $dureeMin,
            'duree_max' => $dureeMax
        ]);
    }

    #[Route('/pack/agence/new', name: 'app_pack_agence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $packAgence = new Pack_agence();
        $form = $this->createForm(PackAgenceType::class, $packAgence);
        $form->handleRequest($request);
    
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($packAgence);
                $entityManager->flush();
    
                $sid = 'ACd5334ae5c51a60ae4edcc9f70183af74';
                $token = '4fe5ad9caff826127be7e0f6bcefe0ca';
                $twilioNumber = '+13613176383';
                $destinationNumber = '+21625096025';
    
                try {
                    $twilio = new Client($sid, $token);
                    $twilio->messages->create(
                        $destinationNumber,
                        [
                            'from' => $twilioNumber,
                            'body' => 'Un nouveau pack a été créé avec succès ! 🎉'
                        ]
                    );
                    $this->addFlash('success', 'Le pack d\'agence a été créé avec succès. 📩 SMS envoyé.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Le pack a été créé mais l\'envoi du SMS a échoué : ' . $e->getMessage());
                }
    
                return $this->redirectToRoute('app_pack_agence');
            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez les corriger.');
            }
        }
    
        return $this->render('pack_agence/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/pack/agence/{id_pack}', name: 'app_pack_agence_show', requirements: ['id_pack' => '\d+'])]
    public function show(int $id_pack, Pack_agenceRepository $packAgenceRepository): Response
    {
        $packAgence = $packAgenceRepository->find($id_pack);
    
        // Vérifier si le pack existe
        if (!$packAgence) {
            throw $this->createNotFoundException('Pack d\'agence non trouvé');
        }
    
        return $this->render('pack_agence/show.html.twig', [
            'packAgence' => $packAgence,
        ]);
    }
    

    #[Route('/pack/agence/{id}/edit', name: 'app_pack_agence_edit')]
    public function edit(Request $request, Pack_agence $packAgence, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PackAgenceType::class, $packAgence);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Pack d\'agence mis à jour avec succès !');
            return $this->redirectToRoute('app_pack_agence', ['id' => $packAgence->getIdPack()]);
        }
    
        return $this->render('pack_agence/edit.html.twig', [
            'form' => $form->createView(),
            'packAgence' => $packAgence,
        ]);
    }

    #[Route('/pack/agence/{id}/delete', name: 'app_pack_agence_delete')]
    public function delete(Request $request, Pack_agence $packAgence, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $packAgence->getIdPack(), $request->request->get('_token'))) {
            $entityManager->remove($packAgence);
            $entityManager->flush();
            $this->addFlash('success', 'Pack d\'agence supprimé avec succès !');
        }
    
        return $this->redirectToRoute('app_pack_agence');
    }

    #[Route('/pack/agence/stats', name: 'app_pack_agence_stats', methods: ['GET'])]
    public function stats(Pack_agenceRepository $packAgenceRepository, EntityManagerInterface $entityManager): Response
    {
        try {
            $connection = $entityManager->getConnection();
    
            // Nouvelle requête : groupement par DATE d'ajout
            $sql = 'SELECT DATE_FORMAT(date_ajout, "%Y-%m-%d") AS ajoutDate, COUNT(id_pack) AS packCount
                    FROM pack_agence
                    GROUP BY ajoutDate
                    ORDER BY ajoutDate ASC';
    
            $stmt = $connection->prepare($sql);
            $resultSet = $stmt->executeQuery();
            $stats = $resultSet->fetchAllAssociative();
    
            // Préparer les données pour Google Charts
            $chartData = [['Date d\'ajout', 'Nombre de packs']];
            foreach ($stats as $stat) {
                $chartData[] = [$stat['ajoutDate'], (int) $stat['packCount']];
            }
    
            return $this->render('pack_agence/stats.html.twig', [
                'chartData' => json_encode($chartData),
            ]);
    
        } catch (\Exception $e) {
            return $this->render('pack_agence/stats.html.twig', [
                'chartData' => json_encode([['Erreur', 'Erreur lors du chargement des données']]),
            ]);
        }
    }
    

}
