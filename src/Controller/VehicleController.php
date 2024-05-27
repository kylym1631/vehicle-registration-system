<?php

namespace App\Controller;

use App\Entity\Owner;
use App\Entity\Vehicle;
use App\Entity\VehicleOwnership;
use App\Repository\OwnerRepository;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class VehicleController extends AbstractController
{
    #[Route('/vehicles', name: 'vehicle_list', methods: ['GET'])]
    public function listVehicles(VehicleRepository $vehicleRepository): Response
    {
        $vehicles = $vehicleRepository->findAll();
        return $this->render('vehicle/list.html.twig', ['vehicles' => $vehicles]);
    }


    #[Route('/register', name: 'vehicle_register_form', methods: ['GET'])]
    public function registerForm(): Response
    {
        return $this->render('vehicle/register.html.twig');
    }


    #[Route('/register', name: 'vehicle_register_submit', methods: ['POST'])]
    public function register(Request $request, VehicleRepository $vehicleRepository, OwnerRepository $ownerRepository, EntityManagerInterface $entityManager): Response
    {
        // Retrieve form data directly from request
        $ownerFullName = $request->request->get('ownerFullName');
        $make = $request->request->get('make');
        $model = $request->request->get('model');


        // Validate incoming data
        if (empty($ownerFullName) || empty($make) || empty($model)) {
            return new Response('Missing data', Response::HTTP_BAD_REQUEST);
        }

        // Always create a new Owner, even if one with the same name already exists
        $owner = new Owner();
        $owner->setFullName($ownerFullName);
        $entityManager->persist($owner);

        // Create the vehicle with a unique registration number
        $vehicle = new Vehicle();
        $vehicle->setMake($make);
        $vehicle->setModel($model);
        $vehicle->setOwner($owner);
        $vehicle->setRegistrationNumber($this->generateUniqueRegistrationNumber($entityManager)); // Assuming this method generates a unique number
        $entityManager->persist($vehicle);

        // Create the initial VehicleOwnership entry
        $vehicleOwnership = new VehicleOwnership();
        $vehicleOwnership->setVehicle($vehicle);
        $vehicleOwnership->setOwner($owner);
        $vehicleOwnership->setStartDate(new \DateTime());
        $entityManager->persist($vehicleOwnership);

        $entityManager->flush();

        $this->addFlash(
            'success',
            'Регистрация успешно завершена!'
        );
        return $this->redirectToRoute('vehicle_history', ['registrationNumber' => $vehicle->getRegistrationNumber()]);
    }

    #[Route('/transfer-ownership/{registrationNumber}', name: 'vehicle_transfer_ownership_form', methods: ['GET'])]
    public function transferOwnershipForm(string $registrationNumber, VehicleRepository $vehicleRepository): Response
    {
        $vehicle = $vehicleRepository->findOneBy(['registrationNumber' => $registrationNumber]);
        if (!$vehicle) {
            throw new NotFoundHttpException('Vehicle not found');
        }

        return $this->render('vehicle/transfer_ownership.html.twig', [
            'vehicle' => $vehicle,
            'ownerName' => $vehicle->getOwner()->getFullName()  // assuming Owner relationship exists
        ]);
    }

    #[Route('/transfer-ownership/{registrationNumber}', name: 'vehicle_transfer_ownership', methods: ['POST'])]
    public function transferOwnership(string $registrationNumber, Request $request, VehicleRepository $vehicleRepository, EntityManagerInterface $entityManager): Response
    {
        // Access form data directly
        $newOwnerFullName = $request->request->get('newOwnerFullName');

        // Find the existing vehicle
        $vehicle = $vehicleRepository->findOneBy(['registrationNumber' => $registrationNumber]);
        if (!$vehicle) {
            return new Response('Vehicle not found.', Response::HTTP_NOT_FOUND);
        }

        // Find or create new owner
        $newOwner = $entityManager->getRepository(Owner::class)->findOneBy(['fullName' => $newOwnerFullName]);
        if (!$newOwner) {
            $newOwner = new Owner();
            $newOwner->setFullName($newOwnerFullName);
            $entityManager->persist($newOwner);
        }

        // End the current ownership record
        $currentOwnership = $entityManager->getRepository(VehicleOwnership::class)->findOneBy([
            'vehicle' => $vehicle,
            'endDate' => null
        ]);
        if ($currentOwnership) {
            $currentOwnership->setEndDate(new \DateTime());
            $entityManager->persist($currentOwnership);
        }

        // Start a new ownership record with the new owner
        $newOwnership = new VehicleOwnership();
        $newOwnership->setVehicle($vehicle);
        $newOwnership->setOwner($newOwner);
        $newOwnership->setStartDate(new \DateTime());
        $entityManager->persist($newOwnership);

        $entityManager->flush();

        $this->addFlash(
            'success',
            'Владелец успешно сменился!'
        );
        return $this->redirectToRoute('vehicle_history', ['registrationNumber' => $vehicle->getRegistrationNumber()]);
    }

    #[Route('/change-registration/{registrationNumber}', name: 'vehicle_change_registration_form', methods: ['GET'])]
    public function changeRegistrationForm(string $registrationNumber, VehicleRepository $vehicleRepository): Response
    {
        $vehicle = $vehicleRepository->findOneBy(['registrationNumber' => $registrationNumber]);
        if (!$vehicle) {
            return new Response('Транспортное средство не найдено.', Response::HTTP_NOT_FOUND);
        }

        return $this->render('vehicle/change_registration.html.twig', ['vehicle' => $vehicle]);
    }



    #[Route('/change-registration/{oldRegistrationNumber}', name: 'vehicle_change_registration', methods: ['POST'])]
    public function changeRegistration(string $oldRegistrationNumber, Request $request, VehicleRepository $vehicleRepository, EntityManagerInterface $entityManager): Response
    {
        $newRegistrationNumber = $request->request->get('newRegistrationNumber');

        // Check if the new registration number is already in use
        if ($vehicleRepository->findOneBy(['registrationNumber' => $newRegistrationNumber])) {
            $this->addFlash('error', 'Новый регистрационный номер уже используется.');
            return $this->redirectToRoute('vehicle_history', ['registrationNumber' => $oldRegistrationNumber]);
        }

        // Find the existing vehicle
        $vehicle = $vehicleRepository->findOneBy(['registrationNumber' => $oldRegistrationNumber]);
        if (!$vehicle) {
            return new Response('Транспортное средство не найдено.', Response::HTTP_NOT_FOUND);
        }

        // Update the vehicle's registration number
        $vehicle->setRegistrationNumber($newRegistrationNumber);
        $entityManager->persist($vehicle);
        $entityManager->flush();

        $this->addFlash('success', 'Номер успешно сменился!');
        return $this->redirectToRoute('vehicle_history', ['registrationNumber' => $vehicle->getRegistrationNumber()]);
    }



    #[Route('/vehicle/{registrationNumber}/history', name: 'vehicle_history', methods: ['GET'])]
    public function history(string $registrationNumber, VehicleRepository $vehicleRepository): Response
    {
        $vehicle = $vehicleRepository->findOneBy(['registrationNumber' => $registrationNumber]);
        if (!$vehicle) {
            return $this->render('error.html.twig', ['message' => 'Vehicle not found']);
        }

        $history = [];
        foreach ($vehicle->getVehicleOwnerships() as $ownership) {
            $history[] = [
                'owner' => $ownership->getOwner()->getFullName(),
                'start_date' => $ownership->getStartDate()->format('Y-m-d'),
                'end_date' => $ownership->getEndDate() ? $ownership->getEndDate()->format('Y-m-d') : null
            ];
        }

        return $this->render('vehicle/history.html.twig', ['history' => $history, 'vehicle' => $vehicle]);
    }



    #[Route('/models', name: 'vehicle_models', methods: ['GET'])]
    public function models(VehicleRepository $vehicleRepository): Response
    {
        $models = $vehicleRepository->findAllModelsAndCounts();
        return $this->render('vehicle/models.html.twig', [
            'models' => $models
        ]);
    }


    public function generateUniqueRegistrationNumber(EntityManagerInterface $entityManager): string
    {
        do {
            $provinceCode = '01'; // Example province code for Bishkek
            $number = rand(1000, 9999);
            $letters = 'BI'; // Example static letters for Bishkek
            $registrationNumber = "{$provinceCode}{$number}{$letters}";

            // Check if this registration number already exists
            $exists = $entityManager->getRepository(Vehicle::class)->findOneBy(['registrationNumber' => $registrationNumber]);
        } while ($exists);

        return $registrationNumber;
    }
}
