<?php

use App\Cart;
use App\Product;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase {
  /**
   * O teste deve somar os produtos corretamente
   */
  public function testShouldSumProductsCorrectly(): void {
    $cart = new Cart('cart-1');

    $cart->addProduct(new Product(1, 'Teclado', 100));
    $cart->addProduct(new Product(2, 'fone', 50));
    $cart->addProduct(new Product(3, 'ssd', 150));
    $cart->removeProduct(3);

    $this->assertEquals(150, $cart->getSubtotalCart());
  }

  /**
   * O teste deve remover os produtos corretamente
   */
  public function testMustRemoveTheProductCorrectly(): void {
    $cart = new Cart('cart-1');

    $cart->removeProduct(1);

    $this->assertInstanceOf(Cart::class, $cart);
  }
}