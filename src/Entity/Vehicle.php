<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "Регистрационный номер не может быть пустым.")]
    #[Assert\Regex(
        pattern: "/^[A-Za-zА-Яа-я0-9\s]+$/u",  // До 1994 года в Кыргызстане использовались регистрационные номера с кириллицей
        message: "Неверный формат регистрационного номера. Примеры: 'АА 1234 АВ', 'A123BCD', '1234 ABC', 'А123АВС'"
    )]
    private ?string $registrationNumber = null; // Это значение должно быть задано перед сохранением. Примеры: 'АА 1234 АВ', 'A123BCD', '1234 ABC', 'А123АВС'

    #[ORM\Column(length: 255)]
    private ?string $make = null;

    #[ORM\Column(length: 255)]
    private ?string $model = null;

    #[ORM\ManyToOne(inversedBy: 'vehicles')]
    #[ORM\JoinColumn(nullable: false)]
    private Owner $owner;

    /**
     * @var Collection<int, VehicleOwnership>
     */
    #[ORM\OneToMany(targetEntity: VehicleOwnership::class, mappedBy: 'vehicle')]
    private Collection $vehicleOwnerships;

    public function __construct()
    {
        $this->vehicleOwnerships = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(string $registrationNumber): static
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getMake(): ?string
    {
        return $this->make;
    }

    public function setMake(string $make): static
    {
        $this->make = $make;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getOwner(): ?Owner
    {
        return $this->owner;
    }

    public function setOwner(?Owner $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, VehicleOwnership>
     */
    public function getVehicleOwnerships(): Collection
    {
        return $this->vehicleOwnerships;
    }

    public function addVehicleOwnership(VehicleOwnership $vehicleOwnership): static
    {
        if (!$this->vehicleOwnerships->contains($vehicleOwnership)) {
            $this->vehicleOwnerships->add($vehicleOwnership);
            $vehicleOwnership->setVehicle($this);
        }

        return $this;
    }

    public function removeVehicleOwnership(VehicleOwnership $vehicleOwnership): static
    {
        if ($this->vehicleOwnerships->removeElement($vehicleOwnership)) {
            // set the owning side to null (unless already changed)
            if ($vehicleOwnership->getVehicle() === $this) {
                $vehicleOwnership->setVehicle(null);
            }
        }

        return $this;
    }
    
}
