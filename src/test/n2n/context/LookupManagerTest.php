<?php
namespace n2n\context;

use n2n\context\mock\AttributeApplicationScopedMock;
use n2n\context\mock\AttributeLookupableMock;
use n2n\context\mock\AttributeRequestScopedMock;
use n2n\context\mock\AttributeSessionScopedMock;
use n2n\context\mock\InterfaceApplicationScopedMock;
use n2n\context\mock\InterfaceRequestScopedMock;
use n2n\context\mock\InvalidLegacyLookupableMock;
use n2n\context\mock\InvalidLookupableMock;
use n2n\context\mock\LegacyApplicationScopedMock;
use n2n\context\mock\LegacySessionScopedMock;
use n2n\context\mock\LookupableMock;
use n2n\context\mock\InterfaceSessionScopedMock;
use PHPUnit\Framework\TestCase;
use n2n\context\config\SimpleLookupSession;
use n2n\util\magic\MagicContext;
use n2n\util\cache\impl\FileCacheStore;
use n2n\context\mock\AttributeThreadScopedMock;
use n2n\context\mock\InterfaceThreadScopedMock;
use n2n\context\mock\SimpleClassMock;
use n2n\context\mock\InjectMock;
use n2n\context\mock\InvalidInjectMock;
use n2n\context\mock\InfiniteInjectionMock;
use n2n\context\mock\InvalidInjectTypeMock;
use n2n\util\magic\MagicObjectUnavailableException;
use n2n\util\cache\impl\EphemeralCacheStore;

class LookupManagerTest extends TestCase {
	/**
	 * @var LookupManager $lookupManager
	 */
    private $lookupManager;

	private $session;
	private $cacheStore;
	private $magicContext;

    protected function setUp(): void {
    	$this->session = new SimpleLookupSession();
		$this->cacheStore = new EphemeralCacheStore();

		$this->magicContext = $this->createStub(MagicContext::class);
		$this->magicContext->method('lookup')->willReturnCallback(function ($id) {
			try {
				return $this->lookupManager->lookup($id);
			} catch (LookupFailedException $e) {
				throw new MagicObjectUnavailableException('mock', 0, $e);
			}
		});

    	$this->lookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);


	}

	function testLookupLookupableAttribute() {
		$attrLookupableMock = $this->lookupManager->lookup(AttributeLookupableMock::class);
		$this->assertTrue($attrLookupableMock instanceof AttributeLookupableMock);

		$this->assertTrue($attrLookupableMock
				!== $this->lookupManager->lookup(AttributeLookupableMock::class));
	}

    function testLookupLookupableInterface() {
    	$annoLookupableMock = $this->lookupManager->lookup(LookupableMock::class);
		$this->assertTrue($annoLookupableMock instanceof LookupableMock);

		$this->assertTrue($annoLookupableMock
				!== $this->lookupManager->lookup(LookupableMock::class));
    }

	function testLookupRequestScopedAttribute() {
		$attrRequestScopedMock = $this->lookupManager->lookup(AttributeRequestScopedMock::class);
		$this->assertTrue($attrRequestScopedMock instanceof AttributeRequestScopedMock);

		$this->assertTrue($attrRequestScopedMock
				=== $this->lookupManager->lookup(AttributeRequestScopedMock::class));
	}

	function testLookupByClassRequestScopedAttribute() {
		$class = new \ReflectionClass(AttributeRequestScopedMock::class);

		$attrRequestScopedMock = $this->lookupManager->lookupByClass($class);
		$this->assertTrue($attrRequestScopedMock instanceof AttributeRequestScopedMock);

		$this->assertTrue($attrRequestScopedMock
				=== $this->lookupManager->lookupByClass($class));
	}

	function testLookupThreadScopedAttribute() {
		$attrThreadScopedMock = $this->lookupManager->lookup(AttributeThreadScopedMock::class);
		$this->assertTrue($attrThreadScopedMock instanceof AttributeThreadScopedMock);

		$this->assertTrue($attrThreadScopedMock
				=== $this->lookupManager->lookup(AttributeThreadScopedMock::class));
	}

	function testLookupByClassThreadScopedAttribute() {
		$class = new \ReflectionClass(AttributeThreadScopedMock::class);

		$attrRequestScopedMock = $this->lookupManager->lookupByClass($class);
		$this->assertTrue($attrRequestScopedMock instanceof AttributeThreadScopedMock);

		$this->assertTrue($attrRequestScopedMock
				=== $this->lookupManager->lookupByClass($class));
	}

	function testLookupRequestScopedInterface() {
		$interfaceRequestScopedMock = $this->lookupManager->lookup(InterfaceRequestScopedMock::class);
		$this->assertTrue($interfaceRequestScopedMock instanceof InterfaceRequestScopedMock);

		$this->assertTrue($interfaceRequestScopedMock
				=== $this->lookupManager->lookup(InterfaceRequestScopedMock::class));
	}

	function testLookupByClassRequestSScopedInterface() {
		$class = new \ReflectionClass(InterfaceRequestScopedMock::class);

		$obj = $this->lookupManager->lookupByClass($class);
		$this->assertTrue($obj instanceof InterfaceRequestScopedMock);

		$this->assertTrue($obj
				=== $this->lookupManager->lookupByClass($class));
	}

	function testLookupThreadScopedInterface() {
		$obj = $this->lookupManager->lookup(InterfaceThreadScopedMock::class);
		$this->assertTrue($obj instanceof InterfaceThreadScopedMock);

		$this->assertTrue($obj
				=== $this->lookupManager->lookup(InterfaceThreadScopedMock::class));
	}

	function testLookupByClassThreadScopedInterface() {
		$class = new \ReflectionClass(InterfaceThreadScopedMock::class);

		$obj = $this->lookupManager->lookupByClass($class);
		$this->assertTrue($obj instanceof InterfaceThreadScopedMock);

		$this->assertTrue($obj
				=== $this->lookupManager->lookupByClass($class));
	}

	function testLookupApplicationScopedAttribute() {
		$applicationScopedStr = 'test';
		$sessionScopedStr = 'session test';

		$attrLookupableMock = $this->lookupManager->lookup(AttributeApplicationScopedMock::class);
		$attrLookupableMock->setApplicationScopedStr($applicationScopedStr);
		$attrLookupableMock->setSessionScopedStr($sessionScopedStr);
		$this->lookupManager->flush();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$attrLookupableMock = $anotherLookupManager->lookup(AttributeApplicationScopedMock::class);
		$this->assertEquals($applicationScopedStr, $attrLookupableMock->getApplicationScopedStr());
		$this->assertEquals($sessionScopedStr, $attrLookupableMock->getSessionScopedStr());
	}

	function testLookupApplicationScopedLegacyAnno() {
		$applicationScopedStr = 'test';

		$annoLookupableMock = $this->lookupManager->lookup(LegacyApplicationScopedMock::class);
		$annoLookupableMock->setApplicationScopedStr($applicationScopedStr);
		$this->lookupManager->flush();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$applicationScopedMock = $anotherLookupManager->lookup(LegacyApplicationScopedMock::class);
		$this->assertEquals($applicationScopedStr, $applicationScopedMock->getApplicationScopedStr());
	}

	function testLookupSessionScopedAttribute() {
		$sessionScopedStr = 'test';
		$appScopedStr = 'apptest';

		$sessionScopedMock = $this->lookupManager->lookup(AttributeSessionScopedMock::class);
		$sessionScopedMock->setSessionScopedStr($sessionScopedStr);
		$sessionScopedMock->setApplicationScopedStr($appScopedStr);
		$this->lookupManager->flush();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$sessionScopedMock = $anotherLookupManager->lookup(AttributeSessionScopedMock::class);
		$this->assertEquals($sessionScopedStr, $sessionScopedMock->getSessionScopedStr());
		$this->assertEquals($appScopedStr, $sessionScopedMock->getApplicationScopedStr());
	}

	function testLookupSessionScopedLegacyAnno() {
		$sessionScopedStr = 'test';

		$sessionScopedMock = $this->lookupManager->lookup(LegacySessionScopedMock::class);
		$sessionScopedMock->setSessionScopedStr($sessionScopedStr);
		$this->lookupManager->flush();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$sessionScopedMock = $anotherLookupManager->lookup(LegacySessionScopedMock::class);
		$this->assertEquals($sessionScopedStr, $sessionScopedMock->getSessionScopedStr());
	}

	function testLookupSessionScopedInterface() {
		$sessionScopedStr = 'test';

		$sessionScopedMock = $this->lookupManager->lookup(InterfaceSessionScopedMock::class);
		$sessionScopedMock->setSessionScopedStr($sessionScopedStr);
		$this->lookupManager->flush();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$sessionScopedMock = $anotherLookupManager->lookup(InterfaceSessionScopedMock::class);
		$this->assertEquals($sessionScopedStr, $sessionScopedMock->getSessionScopedStr());
	}

	function testLookupByClassSessionScopedInterface() {
		$sessionScopedStr = 'test';
		$class = new \ReflectionClass(InterfaceSessionScopedMock::class);

		$sessionScopedMock = $this->lookupManager->lookupByClass($class);
		$sessionScopedMock->setSessionScopedStr($sessionScopedStr);
		$this->lookupManager->flush();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$sessionScopedMock = $anotherLookupManager->lookupByClass($class);
		$this->assertEquals($sessionScopedStr, $sessionScopedMock->getSessionScopedStr());
	}

	function testLookupApplicationScopedInterface() {
		$applicationScoped = 'test';

		$applicationScopedMock = $this->lookupManager->lookup(InterfaceApplicationScopedMock::class);
		$applicationScopedMock->setApplicationScopedStr($applicationScoped);
		$this->lookupManager->flush();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$applicationScopedMock = $anotherLookupManager->lookup(InterfaceApplicationScopedMock::class);
		$this->assertEquals($applicationScoped, $applicationScopedMock->getApplicationScopedStr());
	}

	function testLookupableError() {
		$this->expectException(ModelError::class);
		$this->lookupManager->lookup(InvalidLookupableMock::class);
	}

	function testLegacyLookupableError() {
		$this->expectException(ModelError::class);
		$this->lookupManager->lookup(InvalidLegacyLookupableMock::class);
	}

	function testExceptionForNonExistingClassLookupable() {
		$this->expectException(LookupableNotFoundException::class);
		$this->lookupManager->lookup('asdf');
	}

	function testExceptionForNonLookupable() {
		$this->expectException(LookupableNotFoundException::class);
		$this->lookupManager->lookup(SimpleClassMock::class);
	}

	function testImplements() {
		$this->assertNotFalse($this->lookupManager->has(LookupableMock::class));
		$this->assertNotNull($this->lookupManager->lookup(LookupableMock::class));
	}

	function testInjectMock() {
		/**
		 * @var InjectMock $injectMock
		 */
		$this->assertTrue($this->lookupManager->has(InjectMock::class));
		$injectMock = $this->lookupManager->lookup(InjectMock::class);
		$this->assertInstanceOf(AttributeLookupableMock::class, $injectMock->getAttributeLookupableMock());
		$this->assertInstanceOf(AttributeRequestScopedMock::class, $injectMock->getAttributeLookupableMock()->requestScoped);

		$this->assertInstanceOf(AttributeRequestScopedMock::class, $injectMock->getAttrRequestScopedMock());
		$this->assertInstanceOf(AttributeApplicationScopedMock::class, $injectMock->getAttrRequestScopedMock()->getApplicationScoped());
		$this->assertInstanceOf(InterfaceRequestScopedMock::class, $injectMock->getInterfaceRequestScopedMock());

		$this->assertInstanceOf(AttributeSessionScopedMock::class, $injectMock->getAttributeSessionScopedMock());
		$this->assertInstanceOf(AttributeLookupableMock::class, $injectMock->getAttributeSessionScopedMock()->lookupable);
		$this->assertInstanceOf(InterfaceSessionScopedMock::class, $injectMock->getInterfaceSessionScopedMock());

		$this->assertInstanceOf(AttributeApplicationScopedMock::class, $injectMock->getAttributeApplicationScopedMock());
		$this->assertInstanceOf(InterfaceApplicationScopedMock::class, $injectMock->getInterfaceApplicationScopedMock());
		$this->assertInstanceOf(LookupableMock::class, $injectMock->getAttributeApplicationScopedMock()->lookupable);

		$this->assertInstanceOf(AttributeThreadScopedMock::class, $injectMock->getAttributeThreadScopedMock());
		$this->assertInstanceOf(InterfaceThreadScopedMock::class, $injectMock->getInterfaceThreadScopedMock());
		$this->assertInstanceOf(AttributeRequestScopedMock::class, $injectMock->getAttributeThreadScopedMock()->requestScoped);
	}

	function testInjectWithoutType() {
		$this->expectException(ModelError::class);
		$this->lookupManager->lookup(InvalidInjectMock::class);
	}

	/**
	 * @throws ModelError
	 */
	function testInfiniteInjectionWorks() {
		$mock = $this->lookupManager->lookup(InfiniteInjectionMock::class);
		$this->assertInstanceOf(InfiniteInjectionMock::class, $mock->infiniteInjectionMock);
	}

	/**
	 * @throws ModelError
	 */
	function testCouldNotInjectPropertyException() {
		$this->expectException(LookupFailedException::class);
		$this->expectExceptionMessage('Could not inject property value: n2n\context\mock\InvalidInjectTypeMock::$simpleClassMock');
		$this->lookupManager->lookup(InvalidInjectTypeMock::class);
	}

	function testCallbacks() {
		$lookupableMock = $this->lookupManager->lookup(AttributeLookupableMock::class);
		$threadScopedMock = $this->lookupManager->lookup(AttributeThreadScopedMock::class);
		$requestScopedMock = $this->lookupManager->lookup(AttributeRequestScopedMock::class);
		$sessionScopedMock = $this->lookupManager->lookup(AttributeSessionScopedMock::class);
		$applicationScopedMock = $this->lookupManager->lookup(AttributeApplicationScopedMock::class);

		$this->assertEquals(1, $lookupableMock->initTimes);
		$this->assertEquals(1, $threadScopedMock->initTimes);
		$this->assertEquals(1, $requestScopedMock->initTimes);
		$this->assertEquals(1, $sessionScopedMock->initTimes);
		$this->assertEquals(1, $applicationScopedMock->initTimes);

		$this->assertEquals(0, $lookupableMock->terminateTimes);
		$this->assertEquals(0, $threadScopedMock->terminateTimes);
		$this->assertEquals(0, $requestScopedMock->terminateTimes);
		$this->assertEquals(0, $sessionScopedMock->terminateTimes);
		$this->assertEquals(0, $applicationScopedMock->terminateTimes);

		$this->lookupManager->flush();

		$this->assertEquals(1, $lookupableMock->initTimes);
		$this->assertEquals(1, $threadScopedMock->initTimes);
		$this->assertEquals(1, $requestScopedMock->initTimes);
		$this->assertEquals(1, $sessionScopedMock->initTimes);
		$this->assertEquals(1, $applicationScopedMock->initTimes);

		$this->assertEquals(0, $lookupableMock->terminateTimes);
		$this->assertEquals(0, $threadScopedMock->terminateTimes);
		$this->assertEquals(0, $requestScopedMock->terminateTimes);
		$this->assertEquals(0, $sessionScopedMock->terminateTimes);
		$this->assertEquals(0, $applicationScopedMock->terminateTimes);

		$this->lookupManager->clear();

		$this->assertEquals(1, $lookupableMock->terminateTimes);
		$this->assertEquals(1, $threadScopedMock->terminateTimes);
		$this->assertEquals(1, $requestScopedMock->terminateTimes);
		$this->assertEquals(1, $sessionScopedMock->terminateTimes);
		$this->assertEquals(1, $applicationScopedMock->terminateTimes);
	}
}