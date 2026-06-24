<?php

namespace App;

use App\Interface\RepositoryInterface;
use Exception;

class Cart {
  public function __construct(
    private string $id,
    private RepositoryInterface $repository,
    private array $products = [],
    private ?Customer $customer = null,
  ) {}

  /**
   * Responsável por adicionar um cliente no carrinho 
   */
  public function addCustomer(Customer $customer): self {
    if($this->customer)
      throw new Exception('O carrinho já possui usuário, para adicionar esse você deve remover o atual');

    $this->customer = $customer;
    $this->repository->save($this);

    return $this;
  }

  /**
   * Responsável por remover um usuário do carrinho
   */
  public function removeCustomer(): self {
    if(!$this->customer) return $this;

    $this->customer = null;
    $this->repository->save($this);

    return $this;
  }

  /**
   * Responsável por adicionar um produto ao carrinho
   */
  public function addProduct(Product $product): self {
    $this->products[] = $product;
    $this->repository->save($this);

    return $this;
  }

  /**
   * Responsável por remover um produto do carrinho
   */
  public function removeProduct(int $id): self {
    foreach($this->products as $key => $product) {
      if($product->getId() === $id) {
        unset($this->products[$key]);
        $this->repository->save($this);
        break;
      }
    }

    return $this;
  }

  /**
   * Responsável por calcular e retornar o subtotal do carrinho
   */
  public function getSubtotalCart(): float {
    if(empty($this->products)) return 0;

    $products = $this->products;

    $productsValues = array_map(
      fn(Product $product) => $product->getValue(),
      $products
    );
    
    return array_sum($productsValues);
  }

  /**
   * Responsável por retornar o carrinho
   */
  public function getCart(): self {
    return $this;
  }

  /**
   * Responsável por retornar o checkout
   */
  public function checkout(): float {
    if(empty($this->products)) throw new Exception('O carrinho não pode estar vazio!');

    if(!$this->customer) throw new Exception('O carrinho deve conter um cliente!');

    $this->repository->save($this);

    return $this->getSubtotalCart();
  }

  /**
   * Responsável por retornar os produtos do carrinho
   */
  public function getProducts(): array {
    return $this->products;
  }

  /**
   * Responsável por retornar o id do carrinho
   */
  public function getCartId(): string {
    return $this->id;
  }
}