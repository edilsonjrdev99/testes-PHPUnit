<?php

use App\Address;
use App\Cart;
use App\Customer;
use App\EmailNotifier;
use App\Product;
use App\Repository\CartRepository;

require_once __DIR__ . '/vendor/autoload.php';

// Endereço
$address = new Address(12345678, 'São Paulo', 'Av. Paulista', 100, 'Centro');

// Cliente
$customer = new Customer('João', 27, $address, '');

// Produtos
$product1 = new Product(1, 'Teclado', 200);
$product2 = new Product(2, 'Fone', 100);

$repository = new CartRepository();

$notifier   = new EmailNotifier();

// Carrinho
$cart = new Cart('cart-1', $repository, $notifier , [], $customer);
$cart->addProduct($product1);
$cart->addProduct($product2);
$cart->removeProduct(1);

print_r($cart->getCart());