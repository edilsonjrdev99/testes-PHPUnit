<?php

namespace App;

class Customer {
  public function __construct(
    private string $name,
    private int $age,
    private Address $address
  ) {}

  public function getCustomer(): self {
    return $this;
  }

  public function updateAge(int $age): self {
    $this->age = $age;

    return $this;
  }

  public function updateAddress(): self {
    return $this;
  }
}