<?php
declare(strict_types=1);
use Coroq\Session\Session;
use PHPUnit\Framework\TestCase;

class TestUserSession extends Session {
  public string $name = '';
  public int $age = 0;
  public array $tags = [];
}

class TestSessionWithNullable extends Session {
  public ?string $email = null;
}

class TestSessionWithStatic extends Session {
  public static string $staticProp = 'static';
  public string $instanceProp = 'instance';
}

class TestSessionWithTypeError extends Session {
  public int $count = 0;
  public string $status = 'pending';
  public ?\TypeError $lastError = null;
  public ?array $lastStoredValues = null;

  protected function onTypeError(array $storedValues, \TypeError $error): void {
    $this->lastStoredValues = $storedValues;
    $this->lastError = $error;
  }
}

/**
 * @runTestsInSeparateProcesses
 */
class SessionTest extends TestCase {
  public function setUp(): void {
    $_SESSION = [];
  }

  public function testConstructionWithIntegerLikeNamespaceThrows(): void {
    $this->expectException(DomainException::class);
    new TestUserSession("123");
  }

  public function testConstructionWithNegativeIntegerLikeNamespaceThrows(): void {
    $this->expectException(DomainException::class);
    new TestUserSession("-1");
  }

  public function testConstructionWithNonIntegerNumericNamespaceSucceeds(): void {
    foreach (["+1", "0.1", "01", "0x1", "0b1", "1e5"] as $namespace) {
      $_SESSION = [];
      $session = new TestUserSession($namespace);
      unset($session);
      $this->assertArrayHasKey($namespace, $_SESSION);
    }
  }

  public function testPropertiesHaveDefaultValuesOnConstruction(): void {
    $session = new TestUserSession('test');
    $this->assertSame('', $session->name);
    $this->assertSame(0, $session->age);
    $this->assertSame([], $session->tags);
  }

  public function testPropertiesLoadFromSession(): void {
    session_start();
    $_SESSION['test'] = [
      'name' => 'John',
      'age' => 30,
      'tags' => ['admin'],
    ];

    $session = new TestUserSession('test');
    $this->assertSame('John', $session->name);
    $this->assertSame(30, $session->age);
    $this->assertSame(['admin'], $session->tags);
  }

  public function testPropertiesSaveToSessionOnDestruct(): void {
    $session = new TestUserSession('test');
    $session->name = 'Jane';
    $session->age = 25;
    $session->tags = ['editor'];
    unset($session);

    $this->assertSame([
      'name' => 'Jane',
      'age' => 25,
      'tags' => ['editor'],
    ], $_SESSION['test']);
  }

  public function testMultipleNamespacesAreIndependent(): void {
    $session1 = new TestUserSession('user1');
    $session2 = new TestUserSession('user2');

    $session1->name = 'Alice';
    $session2->name = 'Bob';

    unset($session1, $session2);

    $this->assertSame('Alice', $_SESSION['user1']['name']);
    $this->assertSame('Bob', $_SESSION['user2']['name']);
  }

  public function testNestedArrayModificationWorks(): void {
    $session = new TestUserSession('test');
    $session->tags['nested'] = ['deep' => 'value'];
    $session->tags['nested']['deep'] = 'modified';
    unset($session);

    $this->assertSame('modified', $_SESSION['test']['tags']['nested']['deep']);
  }

  public function testOnlyExistingPropertiesAreLoaded(): void {
    session_start();
    $_SESSION['test'] = [
      'name' => 'John',
      'unknownProperty' => 'ignored',
    ];

    $session = new TestUserSession('test');
    $this->assertSame('John', $session->name);
    $this->assertSame(0, $session->age); // Default, not from session
  }

  public function testNullablePropertyWorks(): void {
    $session = new TestSessionWithNullable('test');
    $this->assertNull($session->email);

    $session->email = 'test@example.com';
    unset($session);

    $this->assertSame('test@example.com', $_SESSION['test']['email']);
  }

  public function testStaticPropertiesAreIgnored(): void {
    $session = new TestSessionWithStatic('test');
    $session->instanceProp = 'modified';
    unset($session);

    $this->assertArrayHasKey('instanceProp', $_SESSION['test']);
    $this->assertArrayNotHasKey('staticProp', $_SESSION['test']);
  }

  public function testPartialSessionDataLoadsCorrectly(): void {
    session_start();
    $_SESSION['test'] = [
      'name' => 'Partial',
      // 'age' missing
      // 'tags' missing
    ];

    $session = new TestUserSession('test');
    $this->assertSame('Partial', $session->name);
    $this->assertSame(0, $session->age); // Default
    $this->assertSame([], $session->tags); // Default
  }

  public function testEmptyNamespaceWorks(): void {
    $session = new TestUserSession('');
    $session->name = 'Empty';
    unset($session);

    $this->assertSame('Empty', $_SESSION['']['name']);
  }

  public function testTypeMismatchResetsToDefaults(): void {
    session_start();
    $_SESSION['test'] = [
      'count' => 'not an int',  // Type mismatch
      'status' => 'active',
    ];

    $session = new TestSessionWithTypeError('test');

    // All properties reset to defaults
    $this->assertSame(0, $session->count);
    $this->assertSame('pending', $session->status);

    // Error info captured
    $this->assertInstanceOf(\TypeError::class, $session->lastError);
    $this->assertSame('not an int', $session->lastStoredValues['count']);
  }

  public function testTypeMismatchMidLoopResetsAll(): void {
    session_start();
    $_SESSION['test'] = [
      'count' => 10,            // Valid
      'status' => ['invalid'],  // Type mismatch (array instead of string)
    ];

    $session = new TestSessionWithTypeError('test');

    // All properties reset, even the one that loaded successfully
    $this->assertSame(0, $session->count);
    $this->assertSame('pending', $session->status);
  }
}
