<?php
namespace n2n\context;

use n2n\context\mock\AttributeApplicationScopedMock;
use n2n\context\mock\AttributeSessionScopedMock;
use n2n\context\mock\InterfaceApplicationScopedMock;
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

class LookupManagerTest extends TestCase {
    private $lookupManager;

	private $session;
	private $cacheStore;
	private $magicContext;

    protected function setUp(): void {
//     	$this->magicMethodInvoker = new SimpleMagicContext();
    	$this->session = new SimpleLookupSession();
		$this->cacheStore = new FileCacheStore(__DIR__ . DIRECTORY_SEPARATOR . 'tmp', '0777', '0777');
		$this->magicContext = $this->createStub(MagicContext::class);

    	$this->lookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
	}
	
    function testLookupLookupableInterface() {
    	$annoLookupableMock = $this->lookupManager->lookup(LookupableMock::class);
		$this->assertTrue($annoLookupableMock instanceof LookupableMock);
    }

	function testLookupSessionScopedInterface() {
		$annoLookupableMock = $this->lookupManager->lookup(InterfaceSessionScopedMock::class);
		$this->assertTrue($annoLookupableMock instanceof InterfaceSessionScopedMock);
	}

	function testLookupApplicationScopedInterface() {
		$annoLookupableMock = $this->lookupManager->lookup(InterfaceApplicationScopedMock::class);
		$this->assertTrue($annoLookupableMock instanceof InterfaceApplicationScopedMock);
	}

	function testLookupApplicationScopedAttribute() {
		$applicationScopedStr = 'test';

		$attrLookupableMock = $this->lookupManager->lookup(InterfaceApplicationScopedMock::class);
		$attrLookupableMock->setApplicationScopedStr($applicationScopedStr);
		$this->lookupManager->shutdown();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$attrLookupableMock = $anotherLookupManager->lookup(InterfaceApplicationScopedMock::class);
		$this->assertEquals($applicationScopedStr, $attrLookupableMock->getApplicationScopedStr());
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

		$sessionScopedMock = $this->lookupManager->lookup(InterfaceSessionScopedMock::class);
		$sessionScopedMock->setSessionScopedStr($sessionScopedStr);
		$this->lookupManager->shutdown();

		$anotherLookupManager = new LookupManager($this->session, $this->cacheStore, $this->magicContext);
		$sessionScopedMock = $anotherLookupManager->lookup(InterfaceSessionScopedMock::class);
		$this->assertEquals($sessionScopedStr, $sessionScopedMock->getSessionScopedStr());
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

	function testAttributeSessionScoped() {
		$sessionScopedMock = $this->lookupManager->lookup(AttributeSessionScopedMock::class);
		$this->assertNotNull($sessionScopedMock);
	}

	function testAttributeApplicationScoped() {
		$sessionScopedMock = $this->lookupManager->lookup(AttributeApplicationScopedMock::class);
		$this->assertNotNull($sessionScopedMock);
	}

	function testLookupableError() {
		$this->expectException(ModelErrorException::class);
		$this->lookupManager->lookup(InvalidLookupableMock::class);
	}

	function testLegacyLookupableError() {
		$this->expectException(ModelErrorException::class);
		$this->lookupManager->lookup(InvalidLegacyLookupableMock::class);
	}
}