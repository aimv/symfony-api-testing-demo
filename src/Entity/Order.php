<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: '`order`')] // Экранируем, так как order — зарезервированное слово в SQL!
class Order
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.ulid_generator')]
    public ?Ulid $id = null {
        get => $this->id;
    }

    // Привязываем заказ к компании
    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Company $company = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $amountRub = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4)]
    private ?string $rateEur = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $amountEur = null;

    public function getCompany(): ?Company { return $this->company; }
    public function setCompany(?Company $company): self { $this->company = $company; return $this; }

    public function getAmountRub(): float { return (float) $this->amountRub; }
    public function setAmountRub(float $amountRub): self { $this->amountRub = (string) $amountRub; return $this; }

    public function getRateEur(): float { return (float) $this->rateEur; }
    public function setRateEur(float $rateEur): self { $this->rateEur = (string) $rateEur; return $this; }

    public function getAmountEur(): float { return (float) $this->amountEur; }
    public function setAmountEur(float $amountEur): self { $this->amountEur = (string) $amountEur; return $this; }
}
