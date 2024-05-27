<?php

namespace App\Entity;

use App\Repository\OwnerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OwnerRepository::class)]
class Owner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Имя владельца не может быть пустым.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Имя владельца должно быть не менее 3 символов.",
        maxMessage: "Имя владельца не может превышать 255 символов."
    )]
    private ?string $fullName = null;

    /**
     * @var Collection<int, Vehicle>
     */
    #[ORM\OneToMany(targetEntity: Vehicle::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $vehicles;

    /**
     * @var Collection<int, VehicleOwnership>
     */
    #[ORM\OneToMany(targetEntity: VehicleOwnership::class, mappedBy: 'owner')]
    private Collection $vehicleOwnerships;

    public function __construct()
    {
        $this->vehicles = new ArrayCollection();
        $this->vehicleOwnerships = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return Collection<int, Vehicle>
     */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    public function addVehicle(Vehicle $vehicle): static
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles->add($vehicle);
            $vehicle->setOwner($this);
        }

        return $this;
    }

    public function removeVehicle(Vehicle $vehicle): static
    {
        if ($this->vehicles->removeElement($vehicle)) {
            // set the owning side to null (unless already changed)
            if ($vehicle->getOwner() === $this) {
                $vehicle->setOwner(null);
            }
        }

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
            $vehicleOwnership->setOwner($this);
        }

        return $this;
    }

    public function removeVehicleOwnership(VehicleOwnership $vehicleOwnership): static
    {
        if ($this->vehicleOwnerships->removeElement($vehicleOwnership)) {
            // set the owning side to null (unless already changed)
            if ($vehicleOwnership->getOwner() === $this) {
                $vehicleOwnership->setOwner(null);
            }
        }

        return $this;
    }
}
