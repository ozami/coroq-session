<?php
use Coroq\Session;

/**
 * @runTestsInSeparateProcesses
 */
class SessionTest extends PHPUnit_Framework_TestCase {
  public function testConstruction() {
    new Session("test");
    $this->assertEquals([], $_SESSION);
  }

  public function testMultipleInstances() {
    $session1 = new Session("test1");
    $session2 = new Session("test2");
    $session1["value"] = 1;
    $session2["value"] = 2;
    $this->assertEquals([
      "test1" => ["value" => 1],
      "test2" => ["value" => 2],
    ], $_SESSION);
  }

  public function testConstructionWithEmptyNamespace() {
    $session = new Session("");
    $session["value"] = 1;
    $this->assertEquals(["" => ["value" => 1]], $_SESSION);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testConstructionWithNullNamespaceWillFail() {
    new Session(null);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testConstructionWithBoolNamespaceWillFail() {
    new Session(false);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testConstructionWithFloadNamespaceWillFail() {
    new Session(3.1415);
  }

  /**
   * @expectedException DomainException
   */
  public function testConstructionWithIntegerLikeStringNamespaceWillFail() {
    new Session("1000");
  }

  /**
   * @expectedException DomainException
   */
  public function testConstructionWithNegativeIntegerLikeStringNamespaceWillFail() {
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
