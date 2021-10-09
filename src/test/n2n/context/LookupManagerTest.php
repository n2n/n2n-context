<?php
namespace n2n\context;

use PHPUnit\Framework\TestCase;
use n2n\context\mock\AnnoLookupableMock;
use n2n\context\config\SimpleLookupSession;
use n2n\util\magic\SimpleMagicContext;
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
	
    function testLookupAnnoLookupable() {
    	$annoLookupableMock = $this->lookupManager->lookup(AnnoLookupableMock::class);
    	
    	$this->assertTrue($annoLookupableMock instanceof AnnoLookupableMock);
    }
}