<?php

use App\Address;
use App\Cart;
use App\Customer;
use App\Interface\RepositoryInterface;
use App\Product;
use PHPUnit\Framework\TestCase;

class CartRepositoryFakeSpy implements RepositoryInterface {
  private int $saveCount = 0;

  private array $args = [];

  public function save(Cart $cart): void {
    $this->saveCount++;

    // Simalação de que salvou no banco

    $this->args[] = $cart->getCartId() . '-' . $this->saveCount;
  }

  public function list(): array {
    return [];
  }

  public function detail(string $cartId): ?Cart {
    return null;
  }

  public function getSaveCount(): int {
    return $this->saveCount;
  }

  public function getArgs(): array {
    return $this->args;
  }
}

class CartRepositoryFake implements RepositoryInterface {
  private array $carts = [];

  public function list(): array {
    return $this->carts;
  }

  public function detail(string $cartId): ?Cart {
    $carts = $this->carts;

    return $carts[$cartId] ?? null;
  }

  public function save(Cart $cart): void {
    $this->carts[$cart->getCartId()] = $cart;
  }
}

class CartTest extends TestCase {

  /**
   * Carrinho
   */
  private Cart $cart;

  private RepositoryInterface $repository;

  /**
   * Setup para cada teste
   */
  protected function setUp(): void {
    $this->repository = $this->createStub(RepositoryInterface::class);

    $this->cart = new Cart('cart-1', $this->repository, [], null);
  }

  /**
   * O teste deve somar os produtos corretamente
   */
  public function test_should_sum_products_correctly(): void {
    $cart = $this->cart;

    $cart->addProduct(new Product(1, 'Teclado', 100));
    $cart->addProduct(new Product(2, 'fone', 50));
    $cart->addProduct(new Product(3, 'ssd', 150));
    $cart->removeProduct(3);

    $this->assertEquals(150, $cart->getSubtotalCart());
  }

  /**
   * O teste deve remover os produtos corretamente
   */
  public function test_should_remove_the_product_correctly(): void {
    $cart = $this->cart;
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
  public function test_should_add_product_to_cart(): void {
    $cart = $this->cart;
    $product = new Product(1, 'Teclado', 100.0);

    $this->assertEquals(0, $cart->getSubtotalCart());

    $result = $cart->addProduct($product);

    $this->assertSame($cart, $result);
    $this->assertEquals(100.0, $cart->getSubtotalCart());
  }

  /**
   * O teste deve adicionar um cliente no carrinho quando não existir um
   */
  public function test_should_add_customer_to_the_cart(): void {
    $address  = new Address(12345678, 'São Paulo', 'Av. Paulista', 100, 'Centro');
    $customer = new Customer('João', 27, $address);

    $cart = new Cart('cart-1', $this->repository, [], null);

    $this->assertSame($cart, $cart->addCustomer($customer));
  }

  /**
   * O teste deve retornar erro ao tentar adicionar um cliente em um carrinho com cliente existente
   */
  public function test_should_return_error_when_adding_customer_to_a_cart_containing_the_customer(): void {
    $address   = new Address(12345678, 'São Paulo', 'Av. Paulista', 100, 'Centro');
    $customer1 = new Customer('João', 27, $address);
    $customer2 = new Customer('Maria', 27, $address);

    $cart = new Cart('cart-1', $this->repository, [], $customer1);
    
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('O carrinho já possui usuário, para adicionar esse você deve remover o atual');
    
    $cart->addCustomer($customer2);
  }

  /**
   * O teste deve retornar o valor igual ao getSubtotalCart e garantir que esse método seja chamado uma única vez
   */
  public function test_should_return_the_subtotal_value_when_checking_out(): void {
    $customer   = $this->createStub(Customer::class);
    $product    = $this->createStub(Product::class);
    $repository = $this->createStub(RepositoryInterface::class);

    $cartMock = $this->getMockBuilder(Cart::class)
      ->setConstructorArgs(['cart-1', $repository, [$product], $customer])
      ->onlyMethods(['getSubtotalCart'])
      ->getMock();

    $cartMock->expects($this->once())
      ->method('getSubtotalCart')
      ->willReturn(150.0);

    $result = $cartMock->checkout();
    $this->assertEquals(150.0, $result);
  }

  /**
   * O teste deve validar se o método save do repository está sendo chamado ao adicionar um produto no carrinho
   */
  public function test_should_persist_cart_when_adding_product_in_the_cart(): void {
    $repository = $this->getMockBuilder(RepositoryInterface::class)
      ->getMock();
      
    $repository->expects($this->once())
      ->method('save');

    $product = new Product(1, 'Fone', 100);
    $cart = new Cart('cart-1', $repository, [], null);
    $cart->addProduct($product);
  }

  /**
   * O teste deve verificar se o método save é chamado duas vezes ao adicionar 2 produtos no carrinho
   */
  public function test_should_persist_cart_twice_when_adding_two_products(): void {
    // Dummy
    $product1 = new Product(1, 'Fone', 100);
    $product2 = new Product(2, 'Cabe usb', 20);

    $repositoryFakeSpy = new CartRepositoryFakeSpy();

    $cart = new Cart('cart-1', $repositoryFakeSpy, [], null);
    $cart->addProduct($product1);
    $cart->addProduct($product2);

    $this->assertEquals(2, $repositoryFakeSpy->getSaveCount());
    $this->assertEquals(['cart-1-1', 'cart-1-2'], $repositoryFakeSpy->getArgs());
  }

  /**
   * O teste deve verificar se o método save está salvando os carrinhos corretamente e os gets (list e detail) funcionam corretamente
   */
  public function test_should_list_carts(): void {
    $repositoryFake = new CartRepositoryFake();
    $productDumy    = new Product(1, 'Fone', 100);

    $cart1 = new Cart('cart-1', $repositoryFake, [], null);
    $cart2 = new Cart('cart-2', $repositoryFake, [], null);

    $cart1->addProduct($productDumy);
    $cart2->addProduct($productDumy);

    $this->assertEquals(
      [
        'cart-1' => $cart1, 
        'cart-2' => $cart2
      ], 
      $repositoryFake->list()
    );
  }

  /**
   * O teste deve verificar que o detail está funcionando corretamente
   */
  public function test_should_return_cart_by_id(): void {
    $repositoryFake = new CartRepositoryFake();
    $productDumy    = new Product(1, 'Fone', 100);

    $cart1 = new Cart('cart-1', $repositoryFake, [], null);
    $cart2 = new Cart('cart-2', $repositoryFake, [], null);

    $cart1->addProduct($productDumy);
    $cart2->addProduct($productDumy);

    $this->assertEquals($cart1, $repositoryFake->detail('cart-1'));
  }

  /**
   * O teste deve validar se ocorre a persistência do carrinho ao remover um cliente
   */
  public function test_should_persist_cart_when_removing_customer(): void {
    $address    = new Address(12345678, 'São Paulo', 'Av Paulista', 10, 'SP');
    $customer   = new Customer('Ana', 26, $address);
    $repository = $this->createMock(RepositoryInterface::class);

    $repository->expects($this->once())->method('save');

    $cart = new Cart('cart-1', $repository, [], $customer);
    $cart->removeCustomer();
  }

  /**
   * O teste deve validar se não ocorre a persistência do carrinho quando tentar remover um cliente em um carrinho sem cliente
   */
  public function test_should_not_persist_cart_when_removing_non_existent_customer_in_the_cart(): void {
    $repository = $this->createMock(RepositoryInterface::class);
    $repository->expects($this->never())->method('save');

    $cart = new Cart('cart-customer-2', $repository, []);
    $cart->removeCustomer();
  }

  /**
   * O teste deve validar se ocorre a persistência do carrinho ao remover um produto
   */
  public function test_should_persist_cart_when_removing_product(): void {
    $product    = new Product(1, 'Fone', 100);
    $repository = $this->createMock(RepositoryInterface::class);

    $repository->expects($this->once())->method('save');

    $cart = new Cart('cart-product', $repository, [$product], null);
    $cart->removeProduct(1);
  }

  /**
   * O teste deve validar se não ocorre a persistência do carrinho ao remover um produto que não existe
   */
  public function test_should_not_persist_cart_when_removing_non_existent_product_in_the_cart(): void {
    $repository = $this->createMock(RepositoryInterface::class);
    $product    = new Product(1, 'Fone', 100);
    $cart       = new Cart('cart-test-2', $repository, [$product]);

    $repository->expects($this->never())->method('save');

    $cart->removeProduct(2);
  }

  /**
   * O teste deve validar se ocorre a persistencia do carrinho ao adicionar um cliente
   */
  public function test_should_persist_cart_when_adding_customer(): void {
    $repository = $this->createMock(RepositoryInterface::class);
    $customer   = $this->createStub(Customer::class);

    $repository->expects($this->once())->method('save');

    $cart = new Cart('cart-c', $repository, [], null);
    $cart->addCustomer($customer);
  }

  /**
   * O teste deve retornar um erro quando tentar criar um checkout com o carrinho vazio
   */
  public function test_should_return_error_if_empty_cart_when_creating_checkout(): void {
    $repository = $this->createStub(RepositoryInterface::class);
    $cart       = new Cart('cart-1', $repository, []);

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('O carrinho não pode estar vazio!');

    $cart->checkout();
  }

  /**
   * O teste deve retornar erro se o carrinho não possuir cliente ao criar o checkout
   */
  public function test_should_return_error_if_cart_not_contain_customer_when_creating_checkout(): void {
    $repository = $this->createStub(RepositoryInterface::class);
    $product    = $this->createStub(Product::class);
    $cart       = new Cart('cart-error-1', $repository, [$product]);
    
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('O carrinho deve conter um cliente!');
    
    $cart->checkout();
  }

  /**
   * O teste deve persistir o carrinho quando o checkout for criado
   */
  public function test_should_persist_cart_when_creating_checkout(): void {
    $repository = $this->createMock(RepositoryInterface::class);
    $product    = $this->createStub(Product::class);
    $customer   = $this->createStub(Customer::class);

    $repository->expects($this->once())->method('save');

    $cart = new Cart('cart-id-1', $repository, [$product], $customer);
    $cart->checkout();
  }
}