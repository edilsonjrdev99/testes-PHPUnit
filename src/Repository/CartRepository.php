<?php

namespace App\Repository;

use App\Cart;
use App\DTO\CartData;
use App\Interface\RepositoryInterface;
use Override;

class CartRepository implements RepositoryInterface {
  /**
   * Responsável por salvar os carrinhos no banco (Fictício)
   */
  public function save(Cart $cart): void {
    return;
  }

  #[Override]
  public function list(): array {
    return [];
  }

  public function detail(string $cartId): ?CartData {
    return null;
  }
}