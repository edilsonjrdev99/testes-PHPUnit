<?php

namespace App\Repository;

use App\Cart;
use App\Interface\RepositoryInterface;

class CartRepository implements RepositoryInterface {
  /**
   * Responsável por salvar os carrinhos no banco (Fictício)
   */
  public function save(Cart $cart): void {
    return;
  }
}