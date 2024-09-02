<?php
declare(strict_types=1);
namespace Coroq\Session;

use DomainException;
use InvalidArgumentException;

class Session {
  /** @var string */
  protected $namespace;

  public function __construct(string $namespace) {
    if (!is_string($namespace)) {
      throw new InvalidArgumentException("Namespace must be string");
    }
    if ((string)(int)$namespace === $namespace) {
      throw new DomainException("String looks like decimal integer cannot be used as a namespace");
    }
    $this->namespace = $namespace;
  }

  /**
   * @return mixed
   */
  public function get() {
    $this->start();
    return $_SESSION[$this->namespace] ?? null;
  }

  /**
   * @param mixed $value
   */
  public function set($value): void {
    $this->start();
    $_SESSION[$this->namespace] = $value;
  }

  public function merge(array $value): array {
    $this->start();
    $_SESSION[$this->namespace] = array_merge((array)$_SESSION[$this->namespace], $value);
    return $_SESSION[$this->namespace];
  }

  public function mergeDefault(array $value): array {
    $this->start();
    $_SESSION[$this->namespace] = (array)$_SESSION[$this->namespace] + $value;
    return $_SESSION[$this->namespace];
  }

  public function clear(): void {
    $this->start();
    unset($_SESSION[$this->namespace]);
  }

  public function start(): void {
    $status = session_status();
    if ($status == PHP_SESSION_DISABLED) {
      throw new DomainException('Session is disabled.');
    }
    if ($status == PHP_SESSION_NONE) {
      session_start();
    }
  }
}
