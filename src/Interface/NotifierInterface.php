<?php

namespace App\Interface;

interface NotifierInterface {
  public function send(string $to, string $subject, string $text): void;
}