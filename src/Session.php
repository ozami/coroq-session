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
  public function __get($name) {
    return @$_SESSION[$this->namespace][$name];
  }

  /**
   * @param string $name
   * @param mixed $value
   * @return void
   */
  public function __set($name, $value) {
    $_SESSION[$this->namespace][$name] = $value;
  }

  /**
   * @param string $name
   * @return bool
   */
  public function __isset($name) {
    return isset($_SESSION[$this->namespace][$name]);
  }

  /**
   * @param string $name
   * @return void
   */
  public function __unset($name) {
    unset($_SESSION[$this->namespace][$name]);
  }

  /**
   * @return null
   */
  public function clear() {
    unset($_SESSION[$this->namespace]);
  }

  /**
   * @return array|null
   */
  public function toArray() {
    return @$_SESSION[$this->namespace];
  }
}
