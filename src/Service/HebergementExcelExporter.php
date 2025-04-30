<?php

namespace App\Service;

use App\Entity\Hebergement;
use App\Repository\ReservationHebergementRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HebergementExcelExporter
{
    private $logger;
    private $reservationRepository;

    public function __construct(LoggerInterface $logger, ReservationHebergementRepository $reservationRepository)
    {
        $this->logger = $logger;
        $this->reservationRepository = $reservationRepository;
    }

    public function export(array $hebergements): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers for Hebergement sheet
        $sheet->setTitle('Hébergements');
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Nom');
        $sheet->setCellValue('C1', 'Type');
        $sheet->setCellValue('D1', 'Adresse');
        $sheet->setCellValue('E1', 'Capacité');
        $sheet->setCellValue('F1', 'Prix');
        $sheet->setCellValue('G1', 'Disponibilité');
        $sheet->setCellValue('H1', 'Description');

        // Add Hebergement data
        $row = 2;
        foreach ($hebergements as $hebergement) {
            $sheet->setCellValue('A' . $row, $hebergement->getId());
            $sheet->setCellValue('B' . $row, $hebergement->getNomh());
            $sheet->setCellValue('C' . $row, $hebergement->getTypeh());
            $sheet->setCellValue('D' . $row, $hebergement->getAdressh());
            $sheet->setCellValue('E' . $row, $hebergement->getCapaciteh());
            $sheet->setCellValue('F' . $row, $hebergement->getPrixh());
            $sheet->setCellValue('G' . $row, $hebergement->getDisponibleh());
            $sheet->setCellValue('H' . $row, $hebergement->getDescriptionh());
            $row++;
        }

        // Create a second sheet for Reservations
        $reservationsSheet = $spreadsheet->createSheet();
        $reservationsSheet->setTitle('Réservations');

        // Set headers for Reservations sheet
        $reservationsSheet->setCellValue('A1', 'ID Réservation');
        $reservationsSheet->setCellValue('B1', 'ID Hébergement');
        $reservationsSheet->setCellValue('C1', 'Nom Hébergement');
        $reservationsSheet->setCellValue('D1', 'Date Début');
        $reservationsSheet->setCellValue('E1', 'Date Fin');
        $reservationsSheet->setCellValue('F1', 'Statut');

        // Add Reservations data
        $resRow = 2;
        $hasReservations = false;

        // Try fetching reservations via Hebergement relationship
        foreach ($hebergements as $hebergement) {
            $reservations = $hebergement->getReservations();
            $this->logger->debug(sprintf(
                'Hebergement %s has %d reservations',
                $hebergement->getNomh(),
                $reservations->count()
            ));

            if ($reservations->count() > 0) {
                $hasReservations = true;
                foreach ($reservations as $reservation) {
                    $reservationsSheet->setCellValue('A' . $resRow, $reservation->getIdReservationh());
                    $reservationsSheet->setCellValue('B' . $resRow, $hebergement->getId());
                    $reservationsSheet->setCellValue('C' . $resRow, $hebergement->getNomh());
                    $reservationsSheet->setCellValue('D' . $resRow, $reservation->getDateD() ? $reservation->getDateD()->format('Y-m-d') : 'N/A');
                    $reservationsSheet->setCellValue('E' . $resRow, $reservation->getDateF() ? $reservation->getDateF()->format('Y-m-d') : 'N/A');
                    $reservationsSheet->setCellValue('F' . $resRow, $reservation->getStatus() ?? 'N/A');
                    $resRow++;
                }
            }
        }

        // Fallback: Fetch all reservations directly from ReservationHebergementRepository
        if (!$hasReservations) {
            $allReservations = $this->reservationRepository->findAll();
            $this->logger->debug(sprintf('Found %d reservations directly from repository', count($allReservations)));

            foreach ($allReservations as $reservation) {
                $hebergement = $reservation->getHebergement();
                if ($hebergement) {
                    $hasReservations = true;
                    $reservationsSheet->setCellValue('A' . $resRow, $reservation->getIdReservationh());
                    $reservationsSheet->setCellValue('B' . $resRow, $hebergement->getId());
                    $reservationsSheet->setCellValue('C' . $resRow, $hebergement->getNomh());
                    $reservationsSheet->setCellValue('D' . $resRow, $reservation->getDateD() ? $reservation->getDateD()->format('Y-m-d') : 'N/A');
                    $reservationsSheet->setCellValue('E' . $resRow, $reservation->getDateF() ? $reservation->getDateF()->format('Y-m-d') : 'N/A');
                    $reservationsSheet->setCellValue('F' . $resRow, $reservation->getStatus() ?? 'N/A');
                    $resRow++;
                }
            }
        }

        // Add a message if no reservations are found
        if (!$hasReservations) {
            $reservationsSheet->setCellValue('A2', 'Aucune réservation trouvée.');
        }

        // Auto-size columns for both sheets
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        foreach (range('A', 'F') as $columnID) {
            $reservationsSheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Create the response
        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="hebergements_et_reservations.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
    
}