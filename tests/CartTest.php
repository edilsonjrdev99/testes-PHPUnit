<?php

use App\Address;
use App\Cart;
use App\Customer;
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
    $cart    = new Cart('cart-1');
    $product = new Product(1, 'Teclado', 100.0);

    $cart->addProduct($product);
    $this->assertEquals(100.0, $cart->getSubtotalCart());

    $result = $cart->removeProduct(1);

    $this->assertSame($cart, $result);
    $this->assertEquals(0, $cart->getSubtotalCart());
  }

  /**
   * O teste deve adicionar um produto ao carrinho corretamente
   */
  public function testShouldAddProductToCart(): void {
    $cart    = new Cart('cart-1');
    $product = new Product(1, 'Teclado', 100.0);

    $this->assertEquals(0, $cart->getSubtotalCart());

    $result = $cart->addProduct($product);

    $this->assertSame($cart, $result);
    $this->assertEquals(100.0, $cart->getSubtotalCart());
  }

  /**
   * O teste deve adicionar um cliente no carrinho quando não existir um
   */
  public function testShoildAddCustomerToTheCart(): void {
    $address  = new Address(12345678, 'São Paulo', 'Av. Paulista', 100, 'Centro');
    $customer = new Customer('João', 27, $address);

    $cart = new Cart('cart-1', null, []);

    $this->assertSame($cart, $cart->addCustomer($customer));
  }

  /**
   * O teste deve retornar erro ao tentar adicionar um cliente em um carrinho com cliente existente
   */
  public function testShoildReturnErrorToAddCustomerToACartContainingTheCustomer(): void {
    $address   = new Address(12345678, 'São Paulo', 'Av. Paulista', 100, 'Centro');
    $customer1 = new Customer('João', 27, $address);
    $customer2 = new Customer('Maria', 27, $address);

    $cart = new Cart('cart-1', $customer1, []);
    
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('O carrinho já possui usuário, para adicionar esse você deve remover o atual');
    
    $cart->addCustomer($customer2);
  }
}