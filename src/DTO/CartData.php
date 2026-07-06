<?php

namespace App\DTO;

class CartData {
  public function __construct(
    public readonly string $id,
    public readonly ?string $customerName,
    public readonly ?string $customerEmail,
    public readonly ?int $customerAge
  ) {}
}