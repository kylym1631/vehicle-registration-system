<?php

namespace App\DataFixtures;

use App\Entity\Owner;
use App\Entity\Vehicle;
use App\Entity\VehicleOwnership;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private $makesAndModels = [
        ['Toyota', 'Camry'],
        ['Honda', 'Accord'],
        ['Nissan', 'Altima'],
        ['Hyundai', 'Sonata'],
        ['Mercedes-Benz', 'E-Class']
    ];

    private function getRandomRegistrationNumber() {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        return $letters[rand(0, 25)] . $letters[rand(0, 25)] . $numbers[rand(0, 9)] . $numbers[rand(0, 9)] . $numbers[rand(0, 9)] . 'KG';
    }

    public function load(ObjectManager $manager)
    {
        // Create owners
        $owners = [];
        for ($i = 0; $i < 10; $i++) {
            $owner = new Owner();
            $owner->setFullName('Хозяин ' . $i);
            $manager->persist($owner);
            $owners[] = $owner;
        }
        $manager->flush(); // Ensure owners are persisted and managed before assigning to vehicles

        // Create vehicles with ownership history
        foreach ($this->makesAndModels as $makeModel) {
            for ($j = 0; $j < 4; $j++) {  // Create 4 vehicles per make/model
                $vehicle = new Vehicle();
                $vehicle->setRegistrationNumber($this->getRandomRegistrationNumber());
                $vehicle->setMake($makeModel[0]);
                $vehicle->setModel($makeModel[1]);

                $randomOwner = $owners[array_rand($owners)];
                $vehicle->setOwner($randomOwner);  // Assign an owner immediately before persisting
                $manager->persist($vehicle);

                // Create ownership history
                $startDate = new \DateTime('-1 year');
                for ($k = 0; $k < 3; $k++) {
                    $ownership = new VehicleOwnership();
                    $ownership->setVehicle($vehicle);
                    $ownership->setOwner($randomOwner);
                    $ownership->setStartDate($startDate);
                    $startDate = (clone $startDate)->modify('+'.rand(60, 120).' days');
                    $ownership->setEndDate($startDate);
                    $manager->persist($ownership);
                    $randomOwner = $owners[array_rand($owners)];  // Change owner for the next period
                }
            }
        }
        $manager->flush();  // Persist all data at once
    }
}
