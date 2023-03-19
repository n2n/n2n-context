<?php
namespace n2n\context\mock;

use n2n\context\attribute\ThreadScoped;
use n2n\context\attribute\Inject;

#[ThreadScoped]
class AttributeThreadScopedMock {
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