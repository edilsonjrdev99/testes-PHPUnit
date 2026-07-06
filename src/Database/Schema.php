<?php

namespace App\Database;

use PDO;

class Schema {
  public static function create(PDO $pdo): void {
    $queries = [
      'CREATE TABLE IF NOT EXISTS carts (
        id TEXT PRIMARY KEY,
        customer_name TEXT NULL,
        customer_email TEXT NULL,
        customer_age INTEGER NULL
      )',
      'CREATE TABLE IF NOT EXISTS cart_products (
        cart_id TEXT NOT NULL,
        product_id INTEGER NOT NULL,
        product_name TEXT NOT NULL,
        product_value REAL NOT NULL,
        PRIMARY KEY (cart_id, product_id)
      )',
    ];

    foreach ($queries as $query) {
      $pdo->exec($query);
    }
  }
}
