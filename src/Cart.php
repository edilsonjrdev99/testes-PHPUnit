<?php

namespace App;

use Exception;

class Cart {
  private float $subTotalCart = 0;

  public function __construct(
    private string $id,
    private ?Customer $customer = null,
    private ?array $products = []
  ) {}

  /**
   * Responsável por adicionar um cliente no carrinho 
   */
  public function addCustomer(Customer $customer): self {
    if($this->customer)
      throw new Exception('O carrinho já possui usuário, para adicionar esse você deve remover o atual');

    $this->customer = $customer;

    return $this;
  }

  /**
   * Responsável por remover um usuário do carrinho
   */
  public function removeCustomer(): self {
    $this->customer = null;

    return $this;
  }

  /**
   * Responsável por adicionar um produto ao carrinho
   */
  public function addProduct(Product $product): self {
    $this->products[] = $product;

    return $this;
  }

  /**
   * Responsável por remover um produto do carrinho
   */
  public function removeProduct(int $id): self {
    foreach($this->products as $key => $product) {
      if($product->getId() === $id) {
        unset($this->products[$key]);
        break;
      }
    }

    return $this;
  }

  /**
   * Responsável por calcular o subtotal do carrinho
   */
  private function calculateSubTotal(): self {
    if(empty($this->products)) return $this;

    $products = $this->products;

    $productsValues = array_map(
      fn(Product $product) => $product->getValue(),
      $products
    );
    
    $this->subTotalCart = array_sum($productsValues);

    return $this;
  }

  public function getSubtotalCart(): float {
    return $this->subTotalCart;
  }

  /**
   * Responsável por retornar o carrinho
   */
  public function getCart(): self {
    $this->calculateSubTotal();

    return $this;
  }
}