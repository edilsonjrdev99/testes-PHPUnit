<?php

namespace App\Repository;

use App\Cart;
use App\DTO\CartData;
use App\Errors\DuplicateCartException;
use App\Interface\RepositoryInterface;
use PDO;
use PDOException;

class CartPDORepository implements RepositoryInterface {
  public function __construct(private PDO $pdo) {}

  public function save(Cart $cart): void {
    $statment = $this->pdo->prepare(
      'INSERT INTO carts (id, customer_name, customer_email, customer_age)
      VALUES (:id, :customer_name, :customer_email, :customer_age)'
    );

    $cartId = $cart->getCartId();

    try {
      $statment->execute([
        'id'             => $cartId,
        'customer_name'  => $cart->getCustomer()->getName() ?? null,
        'customer_email' => $cart->getCustomer()->getEmail() ?? null,
        'customer_age'   => $cart->getCustomer()->getAge() ?? null
      ]);
    } catch(PDOException $e) {
      throw new DuplicateCartException(
        "Já existe um carrinho com o id $cartId cadastrado!",
        0,
        $e
      );
    }
  }

  public function list(): array {
    $statment = $this->pdo->query('SELECT * FROM carts;');

    return $statment->fetchAll(PDO::FETCH_ASSOC);
  }

  public function detail(string $cartId): ?CartData {
    $statment = $this->pdo->prepare("SELECT * FROM carts WHERE id = :id");
    $statment->execute(['id' => $cartId]);

    $cart = $statment->fetch(PDO::FETCH_ASSOC);
    return $cart 
      ? new CartData($cart['id'], $cart['customer_name'], $cart['customer_email'], $cart['customer_age'])
      : null;;
  }
}
