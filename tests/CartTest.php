<?php

use App\Cart;
use App\Product;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase {
  public function testShouldSumProductsCorrectly(): void {
    $cart = new Cart('cart-1');

    $cart->addProduct(new Product(1, 'Teclado', 100));
    $cart->addProduct(new Product(2, 'fone', 50));
    $testCart = $cart->getCart();

    $this->assertEquals(150, $testCart->getSubtotalCart());
  }
}