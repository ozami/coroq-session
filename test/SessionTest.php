<?php
use Coroq\Session\Session;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class SessionTest extends TestCase {
  public function setUp():void {
    $_SESSION = [];
  }

  public function testConstruction() {
    new Session("test");
    $this->assertEquals([], $_SESSION);
  }

  public function testMultipleInstances() {
    $session1 = new Session("test1");
    $session2 = new Session("test2");
    $session1->set(1);
    $session2->set(2);
    $this->assertEquals([
      "test1" => 1,
      "test2" => 2,
    ], $_SESSION);
  }

  public function testConstructionWithEmptyNamespace() {
    $session = new Session("");
    $session->set(1);
    $this->assertEquals(["" => 1], $_SESSION);
  }

  public function testConstructionWithNullNamespaceWillFail() {
    $this->expectException(InvalidArgumentException::class);
    new Session(null);
  }

  public function testConstructionWithBoolNamespaceWillFail() {
    $this->expectException(InvalidArgumentException::class);
    new Session(false);
  }

  public function testConstructionWithFloadNamespaceWillFail() {
    $this->expectException(InvalidArgumentException::class);
    new Session(3.1415);
  }

  public function testConstructionWithIntegerLikeStringNamespaceWillFail() {
    $this->expectException(DomainException::class);
    new Session("1000");
  }

  public function testConstructionWithNegativeIntegerLikeStringNamespaceWillFail() {
    $this->expectException(DomainException::class);
    new Session("-1000");
  }

  public function testConstructionWithNumericalStringNamespace() {
    foreach (["+1", "0.1", "01", "0x1", "0b1"] as $namespace) {
      $_SESSION = [];
      $session = new Session($namespace);
      $session->set($namespace);
      $this->assertEquals([$namespace => $namespace], $_SESSION);
    }
  }
}
