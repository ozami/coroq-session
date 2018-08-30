<?php
namespace Coroq;

class Session implements \ArrayAccess {
  /** @var string */
  protected $ns;

  /**
   * @param string $namespace
   */
  public function __construct($namespace) {
    if (is_string($namespace)) {
      throw new \LogicException("Namespace must be string");
    }
    if ((string)(int)$namespace === $namespace) {
      throw new \LogicException("String looks like decimal integer cannot be used as a namespace");
    }
    @session_start();
    $this->ns = $namespace;
    if (!isset($_SESSION[$this->ns]) || !is_array($_SESSION[$this->ns])) {
      $_SESSION[$this->ns] = [];
    }
  }

  /**
   * @return array
   */
  public function getData() {
    return $_SESSION[$this->ns];
  }

  /**
   * @param array $data
   * @return Session
   */
  public function setData(array $data) {
    $_SESSION[$this->ns] = $data;
    return $this;
  }

  /**
   * @return Session
   */
  public function clear() {
    return $this->setData([]);
  }

  /**
   * @param mixed $name
   * @return mixed
   */
  public function get($name) {
    return $_SESSION[$this->ns][$name];
  }

  /**
   * @param mixed $name
   * @param mixed $value
   * @return Session
   */
  public function set($name, $value) {
    $_SESSION[$this->ns][$name] = $value;
    return $this;
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
    return $_SESSION[$this->ns][$offset];
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
