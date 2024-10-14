<?php
declare(strict_types=1);
namespace Coroq\Session;

use DomainException;
use InvalidArgumentException;
use OutOfRangeException;

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

  public function getIn(string $path) {
    $pathSegments = explode('/', $path);
    $value = $this->get();
    $target = &$value;
    foreach ($pathSegments as $pathSegment) {
      if (!is_array($target)) {
        throw new DomainException(sprintf(
          'Trying to access array offset on %s while traversing path "%s".',
          gettype($target),
          $path,
        ));
      }
      if (!array_key_exists($pathSegment, $target)) {
        throw new OutOfRangeException(sprintf(
          'Undefined array segment "%s" in path "%s".',
          $pathSegment,
          $path,
        ));
      }
      $target = &$target[$pathSegment];
    }
    return $target;
  }

  /**
   * @param mixed $value
   */
  public function set($value): void {
    $this->start();
    $_SESSION[$this->namespace] = $value;
  }

  public function setIn(string $path, $value): void {
    $this->start();
    $pathSegments = explode('/', $path);
    $target = &$_SESSION[$this->namespace];
    foreach ($pathSegments as $pathSegment) {
      if (!is_array($target) && !is_null($target)) {
        throw new DomainException(sprintf(
          'Cannot set array offset on type "%s" while traversing the path "%s" at segment "%s".',
          gettype($target),
          $path,
          $pathSegment,
        ));
      }
      $target = &$target[$pathSegment];
    }
    $target = $value;
  }

  public function merge(array $value): array {
    $_SESSION[$this->namespace] = array_merge((array)$this->get(), $value);
    return $_SESSION[$this->namespace];
  }

  public function mergeDefault(array $value): array {
    $_SESSION[$this->namespace] = (array)$this->get() + $value;
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
