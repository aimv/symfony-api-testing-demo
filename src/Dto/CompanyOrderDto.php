<?php

namespace App\Dto;

use Symfony\Component\Uid\Uuid;

final readonly class CompanyOrderDto
{
    public function __construct(
        public Uuid $order_id,
        public float $amount_rub,
        public float $rate_eur,
        public float $amount_eur,
        public string $company_name,
        public string $owner_email
    ) {}
}

