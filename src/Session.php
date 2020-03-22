<?php
namespace Coroq;

class Session implements \ArrayAccess {
  /** @var string */
  protected $ns;

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
    @session_start();
    $this->ns = $namespace;
  }

  /**
   * @return array
   */
  public function get() {
    return @$_SESSION[$this->ns];
  }

  /**
   * @param array|null $data
   * @return Session
   */
  public function set($data) {
    $_SESSION[$this->ns] = $data;
    return $this;
  }

  /**
   * @return Session
   */
  public function clear() {
    unset($_SESSION[$this->ns]);
  }

  /**
   * @param mixed $offset
   * @return bool
   */
  public function offsetExists($offset) {
    return isset($_SESSION[$this->ns][$offset]);
  }

  /**
   * @param mixed $offset
   * @return &mixed
   */
  public function &offsetGet($offset) {
    return @$_SESSION[$this->ns][$offset];
  }

  /**
   * @param mixed $offset
   * @param mixed $value
   */
  public function offsetSet($offset, $value) {
    if (is_null($offset)) {
      $_SESSION[$this->ns][] = $value;
    }
    else {
      $_SESSION[$this->ns][$offset] = $value;
    }
  }

  /**
   * @param mixed $offset
   */
  public function offsetUnset($offset) {
    unset($_SESSION[$this->ns][$offset]);
  }
}
