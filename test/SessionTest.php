<?php
use Coroq\Session;

class SessionTest extends PHPUnit_Framework_TestCase {
  protected function setUp() {
    @session_destroy();
  }

  public function testConstruction() {
    new Session("test");
    $this->assertEquals(["test" => []], $_SESSION);
  }

  public function testMultipleInstances() {
    new Session("test1");
    new Session("test2");
    $this->assertEquals(["test1" => [], "test2" => []], $_SESSION);
  }

  public function testConstructionWithEmptyNamespace() {
    new Session("");
    $this->assertEquals(["" => []], $_SESSION);
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
    new Session("+1");
    new Session("0.1");
    new Session("01");
    new Session("0x1");
    new Session("0b1");
    $this->assertEquals(
      array_fill_keys(["+1", "0.1", "01", "0x1", "0b1"], []),
      $_SESSION
    );
  }
}
