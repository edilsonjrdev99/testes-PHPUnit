<?php

namespace App;

class Product {
  public function __construct(
    private int $id,
    private string $name,
    private float $value
  ) {}

  /**
   * Responsável por retornar o id do produto
   */
  public function getId(): int {
    return $this->id;
  }

  /**
   * Responsável por retornar o valor do produto
   */
  public function getValue(): float {
    return $this->value;
  }

  /**
   * Responsável por retornar o produto
   */
  public function getProduct(): self {
    return $this;
  }
}