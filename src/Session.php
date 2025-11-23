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
    $test = clone $this;
    $propertyNames = $this->getSessionPropertyNames();

    try {
      foreach ($propertyNames as $name) {
        if (array_key_exists($name, $stored)) {
          $test->$name = $stored[$name];
        }
      }
    } catch (\TypeError $e) {
      $this->onTypeError($stored, $e);
      return;
    }

    foreach ($propertyNames as $name) {
      $this->$name = $test->$name;
    }
  }

  /**
   * Called when session data cannot be loaded due to type mismatch.
   * Override to log or handle differently.
   *
   * @param array<string, mixed> $storedValues The values that were in session
   * @param \TypeError $error The error that occurred
   */
  protected function onTypeError(array $storedValues, \TypeError $error): void {
    // Default: do nothing. Properties retain their default values.
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
