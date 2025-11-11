<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Enum\StatutCommande;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(type: 'boolean')]
    private bool $modePanier = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $dateCmde = null;

    #[ORM\Column(nullable: true, enumType: StatutCommande::class)]
    private ?StatutCommande $statutCmde = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $modifiedAt = null;

    #[ORM\OneToMany(targetEntity: LignePanier::class, mappedBy: 'panier', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lignePaniers;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;


    public function __construct()
    {
        $this->lignePaniers = new ArrayCollection();
        // Initialise automatiquement la date de création à "maintenant"
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // modePanier
    public function isModePanier(): bool
    {
        return $this->modePanier;
    }

    public function setModePanier(bool $modePanier): self
    {
        $this->modePanier = $modePanier;
        return $this;
    }

    //  dateCmde
    public function getDateCmde(): ?DateTimeImmutable
    {
        return $this->dateCmde;
    }

    public function setDateCmde(?DateTimeImmutable $dateCmde): self
    {
        $this->dateCmde = $dateCmde;
        return $this;
    }

    //  statutCmde
    public function getStatutCmde(): ?StatutCommande
    {
        return $this->statutCmde;
    }

    public function setStatutCmde(?StatutCommande $statutCmde): self
    {
        $this->statutCmde = $statutCmde;
        return $this;
    }


    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModifiedAt(): ?DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(?DateTimeImmutable $modifiedAt): static
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
    /**
     * @return Collection<int, LignePanier>
     */
    public function getLignePaniers(): Collection
    {
        return $this->lignePaniers;
    }
    public function addLignePanier(LignePanier $lignePanier): static
    {
        if (!$this->lignePaniers->contains($lignePanier)) {
            $this->lignePaniers->add($lignePanier);
            $lignePanier->setPanier($this);
        }

        return $this;
    }

    public function removeLignePanier(LignePanier $lignePanier): static
    {
        if ($this->lignePaniers->removeElement($lignePanier)) {
            if ($lignePanier->getPanier() === $this) {
                $lignePanier->setPanier(null);
            }
        }

        return $this;
    }
}
