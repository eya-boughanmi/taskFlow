<?php

namespace App\Entity;

use App\Repository\TacheRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
#[ApiResource(
    normalizationContext: ['groups' => ['tache:read']],
    denormalizationContext: ['groups' => ['tache:write']],
)]

#[ORM\Entity(repositoryClass: TacheRepository::class)]
class Tache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
#[Assert\Length(min: 5, max: 255)]
#[Groups(['tache:read', 'tache:write', 'projet:read'])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(choices: ['basse', 'moyenne', 'haute', 'urgente'])]

    private ?string $priorite = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['a_faire', 'en_cours', 'terminee'])]
#[Groups(['tache:read', 'tache:write', 'projet:read'])]
    private ?string $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateEcheance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pieceJointeName = null;

    #[ORM\ManyToOne(inversedBy: 'taches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Projet $projet = null;

    #[ORM\ManyToOne(inversedBy: 'taches')]
    private ?User $assigneA = null;

    #[ORM\ManyToMany(targetEntity: Etiquette::class, inversedBy: 'taches')]
private Collection $etiquettes;
public function __construct()
{
     $this->dateCreation = new \DateTimeImmutable();
    $this->etiquettes = new ArrayCollection();

}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(string $priorite): static
    {
        $this->priorite = $priorite;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateEcheance(): ?\DateTime
    {
        return $this->dateEcheance;
    }

    public function setDateEcheance(?\DateTime $dateEcheance): static
    {
        $this->dateEcheance = $dateEcheance;

        return $this;
    }

    public function getPieceJointeName(): ?string
    {
        return $this->pieceJointeName;
    }

    public function setPieceJointeName(?string $pieceJointeName): static
    {
        $this->pieceJointeName = $pieceJointeName;

        return $this;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): static
    {
        $this->projet = $projet;

        return $this;
    }

    public function getAssigneA(): ?User
    {
        return $this->assigneA;
    }

    public function setAssigneA(?User $assigneA): static
    {
        $this->assigneA = $assigneA;

        return $this;
    }

    public function getEttiquettes(): ?Etiquette
    {
        return $this->ettiquettes;
    }

    public function setEttiquettes(?Etiquette $ettiquettes): static
    {
        $this->ettiquettes = $ettiquettes;

        return $this;
    }

    /**
     * @return Collection<int, Etiquette>
     */
    public function getEtiquettes(): Collection
    {
        return $this->etiquettes;
    }

    public function addEtiquette(Etiquette $etiquette): static
    {
        if (!$this->etiquettes->contains($etiquette)) {
            $this->etiquettes->add($etiquette);
        }

        return $this;
    }

    public function removeEtiquette(Etiquette $etiquette): static
    {
        $this->etiquettes->removeElement($etiquette);

        return $this;
    }
}
