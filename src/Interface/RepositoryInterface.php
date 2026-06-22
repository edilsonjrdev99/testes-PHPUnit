<?php

namespace App\Interface;

use App\Cart;

interface RepositoryInterface {
  /**
   * Responsável por salvar os dados do carrinho no banco
   */
  public function save(Cart $cart): void;
}