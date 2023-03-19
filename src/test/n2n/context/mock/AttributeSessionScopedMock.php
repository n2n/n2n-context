<?php

namespace n2n\context\mock;

use n2n\context\attribute\ApplicationScoped;
use n2n\context\attribute\AutoSerializable;
use n2n\context\attribute\SessionScoped;
use n2n\context\attribute\Inject;

#[SessionScoped, AutoSerializable]
class AttributeSessionScopedMock {
	#[Inject]
	public AttributeLookupableMock $lookupable;

	private string $sessionScopedStr;

	#[ApplicationScoped]
	private string $applicationScopedStr;

	/**
	 * @return string
	 */
	public function getSessionScopedStr(): string {
		return $this->sessionScopedStr;
	}

	/**
	 * @param string $sessionScopedStr
	 */
	public function setSessionScopedStr(string $sessionScopedStr): void {
		$this->sessionScopedStr = $sessionScopedStr;
	}

	/**
	 * @return string
	 */
	public function getApplicationScopedStr(): string {
		return $this->applicationScopedStr;
	}

	/**
	 * @param string $applicationScopedStr
	 */
	public function setApplicationScopedStr(string $applicationScopedStr): void {
		$this->applicationScopedStr = $applicationScopedStr;
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