<?php
namespace Coroq;

class Session {
  /** @var string */
  protected $namespace;

  /**
   * @param string $namespace
   */
  public function __construct($namespace) {
    if (!is_string($namespace)) {
      throw new \InvalidArgumentException("Namespace must be string");
    }
    if ((string)(int)$namespace === $namespace) {
      throw new \DomainException("String looks like decimal integer cannot be used as a namespace");
    }
    $this->namespace = $namespace;
  }

  /**
   * @return mixed
   */
  public function get() {
    return @$_SESSION[$this->namespace];
  }

  /**
  * @param mixed $value
   * @return void
   */
  public function set($value) {
    $_SESSION[$this->namespace] = $value;
  }

  /**
   * @return void
   */
  public function clear() {
    unset($_SESSION[$this->namespace]);
  }
}
