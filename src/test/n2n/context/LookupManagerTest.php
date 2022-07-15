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
use Psr\Container\NotFoundExceptionInterface;
use n2n\context\mock\SimpleClassMock;
use n2n\context\mock\InjectMock;
use n2n\context\mock\InvalidInjectMock;
use n2n\context\mock\InfiniteInjectionMock;
use n2n\context\mock\InvalidInjectTypeMock;

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
		$this->cacheStore = new FileCacheStore(__DIR__ . DIRECTORY_SEPARATOR . 'tmp', '0777', '0777');
		$this->magicContext = $this->createStub(MagicContext::class);

    	$this->lookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
	}

	function testLookupLookupableAttribute() {
		$attrLookupableMock = $this->lookupManager->lookup(AttributeLookupableMock::class);
		$this->assertTrue($attrLookupableMock instanceof AttributeLookupableMock);
	}

    function testLookupLookupableInterface() {
    	$annoLookupableMock = $this->lookupManager->lookup(LookupableMock::class);
		$this->assertTrue($annoLookupableMock instanceof LookupableMock);
    }

	function testLookupRequestScopedAttribute() {
		$attrRequestScopedMock = $this->lookupManager->lookup(AttributeRequestScopedMock::class);
		$this->assertTrue($attrRequestScopedMock instanceof AttributeRequestScopedMock);
	}

	function testLookupThreadScopedAttribute() {
		$attrThreadScopedMock = $this->lookupManager->lookup(AttributeThreadScopedMock::class);
		$this->assertTrue($attrThreadScopedMock instanceof AttributeThreadScopedMock);
	}

	function testLookupRequestScopedInterface() {
		$interfaceRequestScopedMock = $this->lookupManager->lookup(InterfaceRequestScopedMock::class);
		$this->assertTrue($interfaceRequestScopedMock instanceof InterfaceRequestScopedMock);
	}

	function testLookupThreadScopedInterface() {
		$interfaceThreadScopedMock = $this->lookupManager->lookup(InterfaceThreadScopedMock::class);
		$this->assertTrue($interfaceThreadScopedMock instanceof InterfaceThreadScopedMock);
	}

	function testLookupApplicationScopedAttribute() {
		$applicationScopedStr = 'test';
		$sessionScopedStr = 'session test';

		$attrLookupableMock = $this->lookupManager->lookup(AttributeApplicationScopedMock::class);
		$attrLookupableMock->setApplicationScopedStr($applicationScopedStr);
		$attrLookupableMock->setSessionScopedStr($sessionScopedStr);
		$this->lookupManager->shutdown();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$attrLookupableMock = $anotherLookupManager->lookup(AttributeApplicationScopedMock::class);
		$this->assertEquals($applicationScopedStr, $attrLookupableMock->getApplicationScopedStr());
		$this->assertEquals($sessionScopedStr, $attrLookupableMock->getSessionScopedStr());
	}

	function testLookupApplicationScopedLegacyAnno() {
		$applicationScopedStr = 'test';

		$annoLookupableMock = $this->lookupManager->lookup(LegacyApplicationScopedMock::class);
		$annoLookupableMock->setApplicationScopedStr($applicationScopedStr);
		$this->lookupManager->shutdown();

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
		$this->lookupManager->shutdown();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$sessionScopedMock = $anotherLookupManager->lookup(AttributeSessionScopedMock::class);
		$this->assertEquals($sessionScopedStr, $sessionScopedMock->getSessionScopedStr());
		$this->assertEquals($appScopedStr, $sessionScopedMock->getApplicationScopedStr());
	}

	function testLookupSessionScopedLegacyAnno() {
		$sessionScopedStr = 'test';

		$sessionScopedMock = $this->lookupManager->lookup(LegacySessionScopedMock::class);
		$sessionScopedMock->setSessionScopedStr($sessionScopedStr);
		$this->lookupManager->shutdown();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$sessionScopedMock = $anotherLookupManager->lookup(LegacySessionScopedMock::class);
		$this->assertEquals($sessionScopedStr, $sessionScopedMock->getSessionScopedStr());
	}

	function testLookupSessionScopedInterface() {
		$sessionScopedStr = 'test';

		$sessionScopedMock = $this->lookupManager->lookup(InterfaceSessionScopedMock::class);
		$sessionScopedMock->setSessionScopedStr($sessionScopedStr);
		$this->lookupManager->shutdown();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$sessionScopedMock = $anotherLookupManager->lookup(InterfaceSessionScopedMock::class);
		$this->assertEquals($sessionScopedStr, $sessionScopedMock->getSessionScopedStr());
	}

	function testLookupApplicationScopedInterface() {
		$applicationScoped = 'test';

		$applicationScopedMock = $this->lookupManager->lookup(InterfaceApplicationScopedMock::class);
		$applicationScopedMock->setApplicationScopedStr($applicationScoped);
		$this->lookupManager->shutdown();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$applicationScopedMock = $anotherLookupManager->lookup(InterfaceApplicationScopedMock::class);
		$this->assertEquals($applicationScoped, $applicationScopedMock->getApplicationScopedStr());
	}

	function testLookupableError() {
		$this->expectException(ModelErrorException::class);
		$this->lookupManager->get(InvalidLookupableMock::class);
	}

	function testLegacyLookupableError() {
		$this->expectException(ModelErrorException::class);
		$this->lookupManager->get(InvalidLegacyLookupableMock::class);
	}

	function testPsr11CompatibilityForNonExistingClassLookupable() {
		$this->expectException(NotFoundExceptionInterface::class);
		$this->lookupManager->get('asdf');
	}

	function testPsr11CompatibilityForNonLookupable() {
		$this->expectException(NotFoundExceptionInterface::class);
		$this->lookupManager->get(SimpleClassMock::class);
	}

	function testPsr11CompatibilityImplements() {
		$this->assertNotFalse($this->lookupManager->has(LookupableMock::class));
		$this->assertNotNull($this->lookupManager->get(LookupableMock::class));
	}

	function testInjectMock() {
		/**
		 * @var InjectMock $injectMock
		 */
		$this->assertTrue($this->lookupManager->has(InjectMock::class));
		$injectMock = $this->lookupManager->get(InjectMock::class);
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
		$this->expectException(ModelErrorException::class);
		$this->lookupManager->get(InvalidInjectMock::class);
	}

	/**
	 * @throws ModelErrorException
	 */
	function testInfiniteInjectionWorks() {
		$mock = $this->lookupManager->get(InfiniteInjectionMock::class);
		$this->assertInstanceOf(InfiniteInjectionMock::class, $mock->infiniteInjectionMock);
	}

	/**
	 * @throws ModelErrorException
	 */
	function testCouldNotInjectPropertyException() {
		$this->expectException(LookupFailedException::class);
		$this->expectExceptionMessage('Could not inject property value: n2n\context\mock\InvalidInjectTypeMock::$simpleClassMock');
		$this->lookupManager->get(InvalidInjectTypeMock::class);
	}
}