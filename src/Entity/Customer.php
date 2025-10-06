<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\Table(name: 'customer')]
#[ORM\UniqueConstraint(name: 'uniq_customer_sirene', columns: ['sirene'])]
#[ORM\UniqueConstraint(name: 'uniq_customer_email', columns: ['email'])]
#[ORM\UniqueConstraint(name: 'uniq_customer_vat', columns: ['vat_number'])]
#[UniqueEntity('sirene')]
#[UniqueEntity('email')]
#[UniqueEntity('vatNumber')]
class Customer
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $companyName = null;

    #[ORM\Column(length: 9, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d{9}$/')]
    private ?string $sirene = null;

    #[ORM\Column(length: 5)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d{4}[A-Z]$/')]
    private ?string $ape = null;

    #[ORM\Column(name: 'vat_number', length: 20, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^FR[0-9A-Z]{2}\d{9}$/')]
    private ?string $vatNumber = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 25)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^(?:\+33|0)[1-9]\d{8}$|^\+\d{6,20}$/',
        message: 'Numéro de téléphone invalide.'
    )]
    private ?string $phone = null;

    /** @var Collection<int, Quote> */
    #[ORM\OneToMany(targetEntity: Quote::class, mappedBy: 'customer')]
    private Collection $quotes;

    public function __construct()
    {
        $this->quotes = new ArrayCollection();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }
    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getSirene(): ?string
    {
        return $this->sirene;
    }
    public function setSirene(string $sirene): static
    {
        $this->sirene = $sirene;
        return $this;
    }

    public function getApe(): ?string
    {
        return $this->ape;
    }
    public function setApe(string $ape): static
    {
        $this->ape = $ape;
        return $this;
    }

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }
    public function setVatNumber(string $vatNumber): static
    {
        $this->vatNumber = $vatNumber;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }
    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    /** @return Collection<int, Quote> */
    public function getQuotes(): Collection
    {
        return $this->quotes;
    }

    public function addQuote(Quote $quote): static
    {
        if (!$this->quotes->contains($quote)) {
            $this->quotes->add($quote);
            $quote->setCustomer($this);
        }
        return $this;
    }

    public function removeQuote(Quote $quote): static
    {
        if ($this->quotes->removeElement($quote)) {
            if ($quote->getCustomer() === $this) {
                $quote->setCustomer(null);
            }
        }
        return $this;
    }
}
