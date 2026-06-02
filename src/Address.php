<?php

namespace App;

class Address {
  public function __construct(
    private int $zipcode,
    private string $city,
    private string $street,
    private int $number,
    private string $district
  ) {}

  public function getAddress(): self {
    return $this;
  }
}