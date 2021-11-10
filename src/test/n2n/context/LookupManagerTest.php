<?php
namespace n2n\context;

use n2n\context\mock\ApplicationScopedMock;
use n2n\context\mock\InvalidLegacyLookupableMock;
use n2n\context\mock\InvalidLookupableMock;
use n2n\context\mock\LegacyApplicationScopedMock;
use n2n\context\mock\LegacySessionScopedMock;
use n2n\context\mock\LookupableMock;
use n2n\context\mock\SessionScopedMock;
use PHPUnit\Framework\TestCase;
use n2n\context\config\SimpleLookupSession;
use n2n\util\magic\MagicContext;
use n2n\util\cache\impl\FileCacheStore;

class LookupManagerTest extends TestCase {
    private $lookupManager;
    
    protected function setUp(): void {
//     	$this->magicMethodInvoker = new SimpleMagicContext();
    	
    	$magicContext = $this->createStub(MagicContext::class);
    	
    	$this->lookupManager = new LookupManager(new SimpleLookupSession(), 
    			new FileCacheStore(__DIR__ . DIRECTORY_SEPARATOR . 'tmp'), $magicContext);
	}
	
    function testLookupLookupableInterface() {
    	$annoLookupableMock = $this->lookupManager->lookup(LookupableMock::class);
		$this->assertTrue($annoLookupableMock instanceof LookupableMock);
    }

	function testLookupSessionScopedInterface() {
		$annoLookupableMock = $this->lookupManager->lookup(SessionScopedMock::class);
		$this->assertTrue($annoLookupableMock instanceof SessionScopedMock);
	}

	function testLookupApplicationScopedInterface() {
		$annoLookupableMock = $this->lookupManager->lookup(ApplicationScopedMock::class);
		$this->assertTrue($annoLookupableMock instanceof ApplicationScopedMock);
	}

	function testLookupApplicationScopedAttribute() {
		$applicationScopedStr = 'test';

		$attrLookupableMock = $this->lookupManager->lookup(ApplicationScopedMock::class);
		$attrLookupableMock->setApplicationScopedStr($applicationScopedStr);

		$attrLookupableMock = $this->lookupManager->lookup(ApplicationScopedMock::class);
		$this->assertEquals($applicationScopedStr, $attrLookupableMock->getApplicationScopedStr());
	}

	function testLookupApplicationScopedLegacyAnno() {
		$applicationScopedStr = 'test';

		$annoLookupableMock = $this->lookupManager->lookup(LegacyApplicationScopedMock::class);
		$annoLookupableMock->setApplicationScopedStr($applicationScopedStr);

		$annoLookupableMock = $this->lookupManager->lookup(LegacyApplicationScopedMock::class);
		$this->assertEquals($applicationScopedStr, $annoLookupableMock->getApplicationScopedStr());
	}

	function testLookupSessionScopedAttribute() {
		$sessionScopedStr = 'test';

		$sessionScopedMock = $this->lookupManager->lookup(SessionScopedMock::class);
		$sessionScopedMock->setSessionScopedStr($sessionScopedStr);

		$sessionScopedMock = $this->lookupManager->lookup(SessionScopedMock::class);
		$this->assertEquals($sessionScopedStr, $sessionScopedMock->getSessionScopedStr());
	}

	function testLookupSessionScopedLegacyAnno() {
		$sessionScopedStr = 'test';

		$sessionScopedMock = $this->lookupManager->lookup(LegacySessionScopedMock::class);
		$sessionScopedMock->setSessionScopedStr($sessionScopedStr);

		$sessionScopedMock = $this->lookupManager->lookup(LegacySessionScopedMock::class);
		$this->assertEquals($sessionScopedStr, $sessionScopedMock->getSessionScopedStr());
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