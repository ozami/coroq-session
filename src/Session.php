<?php
declare(strict_types=1);
namespace Coroq\Session;

use DomainException;
use ReflectionClass;
use ReflectionProperty;

class Session {
  protected string $sessionNamespace;

  public function __construct(string $namespace) {
    if ((string)(int)$namespace === $namespace) {
      throw new DomainException("String looks like decimal integer cannot be used as a namespace");
    }
    $this->sessionNamespace = $namespace;
    $this->startSession();
    $this->loadFromSession();
  }

  /**
   * @return string[]
   */
  private function getSessionPropertyNames(): array {
    $names = [];
    foreach ((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
      if (!$property->isStatic()) {
        $names[] = $property->getName();
      }
    }
    return $names;
  }

  private function loadFromSession(): void {
    $stored = $_SESSION[$this->sessionNamespace] ?? [];

    foreach ($this->getSessionPropertyNames() as $name) {
      if (array_key_exists($name, $stored)) {
        $this->$name = $stored[$name];
      }
    }
  }

  public function __destruct() {
    $this->saveToSession();
  }

  private function saveToSession(): void {
    $data = [];
    foreach ($this->getSessionPropertyNames() as $name) {
      $data[$name] = $this->$name;
    }
    $_SESSION[$this->sessionNamespace] = $data;
  }

  private function startSession(): void {
    $status = session_status();
    if ($status == PHP_SESSION_DISABLED) {
      throw new DomainException('Session is disabled.');
    }
    if ($status == PHP_SESSION_NONE) {
      session_start();
    }
  }
}
