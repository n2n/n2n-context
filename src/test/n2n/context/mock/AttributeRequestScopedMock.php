<?php

namespace n2n\context\mock;

use n2n\context\attribute\RequestScoped;
use n2n\context\attribute\Inject;

#[RequestScoped]
class AttributeRequestScopedMock {
	#[Inject]
	private AttributeApplicationScopedMock $applicationScoped;

	public function getApplicationScoped(): AttributeApplicationScopedMock {
		return $this->applicationScoped;
	}

	public int $initTimes = 0;
	public int $terminateTimes = 0;

	private function _init() {
		$this->initTimes++;
	}

	private function _terminate() {
		$this->terminateTimes++;
	}
}