<?php
namespace n2n\context\mock;

use n2n\context\attribute\Lookupable;
use n2n\context\attribute\Inject;

#[Lookupable]
class AttributeLookupableMock {
	#[Inject]
	public AttributeRequestScopedMock $requestScoped;

	public int $initTimes = 0;
	public int $terminateTimes = 0;

	private function _init() {
		$this->initTimes++;
	}

	private function _terminate() {
		$this->terminateTimes++;
	}
}