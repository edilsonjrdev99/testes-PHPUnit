<?php

use App\Cart;
use App\Customer;
use App\Database\Schema;
use App\Errors\DuplicateCartException;
use App\Interface\NotifierInterface;
use App\Product;
use App\Repository\CartPDORepository;
use PHPUnit\Framework\TestCase;


class CartPDORepositoryTest extends TestCase {
  private static PDO $pdo;

  public static function setUpBeforeClass(): void {
    self::$pdo = new PDO('sqlite::memory:');
    
    Schema::create(self::$pdo);
  }

  public function setUp(): void {
    self::$pdo->beginTransaction();
  }

  public function tearDown(): void {
    self::$pdo->rollBack();
  }

  /**
   * O teste deve retornar um carrinho quando um carrinho for salvo no banco
   */
  public function test_should_return_one_cart_when_saving_cart(): void {
    $repository = new CartPDORepository(self::$pdo);
    $notifier   = $this->createStub(NotifierInterface::class);
    $customer   = $this->createStub(Customer::class);
    $cart       = new Cart('cart-1', $repository, $notifier, [], $customer);
    $repository->save($cart);
    $carts      = $repository->list();
    
    $this->assertCount(1, $carts);
  }

  /**
   * O teste deve retornar um carrinho pelo id quando um carrinho for persistido
   */
  public function test_should_return_cart_by_id_when_persisting_cart(): void {
    // Arrange
    $repository = new CartPDORepository(self::$pdo);
    $notifier   = $this->createStub(NotifierInterface::class);
    $customer   = $this->createStub(Customer::class);
    $product    = $this->createStub(Product::class);
    $cart       = new Cart('cart-2', $repository, $notifier, [$product], $customer);

    // Act
    $repository->save($cart);
    $cartById = $repository->detail('cart-2');

    // Assert
    $this->assertEquals('cart-2', $cartById->id);
  }

  /**
   * O teste deve validar se uma exceção é lançada ao tentar salvar um carrinho com id duplicado
   */
  public function test_should_throw_exception_when_saving_duplicated_cart_id(): void {
    $repository = new CartPDORepository(self::$pdo);
    $notifier   = $this->createStub(NotifierInterface::class);
    $customer   = $this->createStub(Customer::class);
    $product    = $this->createStub(Product::class);
    $cart       = new Cart('cart-1', $repository, $notifier, [$product], $customer);

    $this->expectException(DuplicateCartException::class);
    $this->expectExceptionMessage('Já existe um carrinho com o id cart-1 cadastrado!');
    
    $repository->save($cart);
    $repository->save($cart);
  }

  /**
   * O teste deve retornar null quando o carrinho não existir
   */
  public function test_should_return_null_when_cart_does_not_exist(): void {
    // Arrange
    $repository = new CartPDORepository(self::$pdo);

    // act
    $cartById = $repository->detail('cart-2');

    // assert
    $this->assertNull($cartById);
  }

  /**
   * O teste deve retornar 2 carrinhos quando salvar dois carrinhos
   */
  public function test_should_return_two_carts_when_saving_two_carts(): void {
    // Arrange
    $repository = new CartPDORepository(self::$pdo);
    $notifier   = $this->createStub(NotifierInterface::class);
    $customer   = $this->createStub(Customer::class);
    $product    = $this->createStub(Product::class);
    $cart       = new Cart('cart-1', $repository, $notifier, [$product], $customer);
    $cart2      = new Cart('cart-2', $repository, $notifier, [$product], $customer);

    // act
    $repository->save($cart);
    $repository->save($cart2);
    $result = $repository->list();
    $ids    = array_column($result, 'id');

    // assert
    $this->assertCount(2, $result);
    $this->assertContains('cart-1', $ids);
    $this->assertContains('cart-2', $ids);
  }
}