<?php

namespace App;

class Customer {
  public function __construct(
    private string $name,
    private int $age,
    private Address $address,
    private string $email
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

  public function getEmail(): string {
    return $this->email;
  }
}