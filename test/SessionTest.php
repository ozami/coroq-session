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

  public function testGetReturnsValue() {
    session_start();
    $_SESSION['test'] = 'a';
    $session = new Session('test');
    $this->assertSame('a', $session->get());
  }

  public function testGetReturnsNullIfTheValueWasNotSet() {
    $session = new Session('test');
    $this->assertNull($session->get('default'));
  }

  public function testGetReturnsNullIfTheValueWasNull() {
    session_start();
    $_SESSION['test'] = null;
    $session = new Session('test');
    $this->assertNull($session->get());
  }

  public function testGetInReturnsCorrectValueForValidPath() {
    $session = new Session('test');
    $session->set([
      'a' => [
        'b' => 2,
        'c' => 3,
        'd' => [
          'e' => 4,
        ],
      ],
      'f' => 5,
    ]);
    $this->assertSame(3, $session->getIn('a/c'));
    $this->assertSame(5, $session->getIn('f'));
    $this->assertSame(4, $session->getIn('a/d/e'));
    $this->assertSame(['e' => 4], $session->getIn('a/d'));
  }

  public function testGetInThrowsDomainExceptionForNonArrayValue() {
    $session = new Session('test');
    $session->set([
      'a' => [
        'b' => 2,
      ],
    ]);
    $this->expectException(DomainException::class);
    $this->expectExceptionMessage('Trying to access array offset on integer while traversing path "a/b/c"');
    $session->getIn('a/b/c');
  }

  public function testGetInThrowsOutOfRangeExceptionForUndefinedSegment() {
    $session = new Session('test');
    $session->set([
      'a' => [
      ],
    ]);
    $this->expectException(OutOfRangeException::class);
    $this->expectExceptionMessage('Undefined array segment "b" in path "a/b"');
    $session->getIn('a/b');
  }

  public function testSetInCreatesNewNestedArrayIfPathDoesNotExist() {
    $session = new Session('test');
    $session->setIn('a/b/c', 'ok');
    $this->assertSame('ok', $_SESSION['test']['a']['b']['c']);
  }

  public function testSetInOverwritesExistingValue() {
    $session = new Session('test');
    $session->set([
      'a' => [
        'b' => 'ng',
      ],
    ]);
    $session->setIn('a/b', 'ok');
    $this->assertSame('ok', $_SESSION['test']['a']['b']);
  }

  public function testSetInThrowsDomainExceptionForNonArraySegment() {
    $session = new Session('test');
    $session->set([
      'a' => 'A',
    ]);
    $this->expectException(DomainException::class);
    $this->expectExceptionMessage('Cannot set array offset on type "string" while traversing the path "a/b" at segment "b".');
    $session->setIn('a/b', 'ok');
  }

  public function testMergeForArray() {
    $session = new Session('test');
    $session->set(['a' => 1, 'b' => 2, 'c']);
    $this->assertSame(['a' => 1, 'b' => 'B', 'c', 'd'], $session->merge(['b' => 'B', 'd']));
  }

  public function testMergeForScalarValue() {
    $session = new Session('test');
    $session->set('string');
    $this->assertSame(['string', 'a' => 1, 'b'], $session->merge(['a' => 1, 'b']));
  }

  public function testMergeDefaultForArray() {
    $session = new Session('test');
    $session->set(['a' => 1, 'b' => 2, 'c']);
    $this->assertSame(['a' => 1, 'b' => 2, 'c', 'e'], $session->mergeDefault(['b' => 'B', 'd', 'e']));
  }

  public function testMergeDefaultForScalarValue() {
    $session = new Session('test');
    $session->set(99);
    $this->assertSame([99, 'b' => 'B', 'e'], $session->mergeDefault(['b' => 'B', 'd', 'e']));
  }
}
