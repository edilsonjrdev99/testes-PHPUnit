<?php

namespace App;

use App\Interface\NotifierInterface;

class EmailNotifier implements NotifierInterface {
  public function send(string $to, string $subject, string $text): void {
    mail($to, $subject, $text);
  }
}