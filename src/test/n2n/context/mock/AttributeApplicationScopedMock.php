<?php

namespace n2n\context\mock;

use n2n\context\attribute\ApplicationScoped;
use n2n\context\attribute\AutoSerializable;
use n2n\context\attribute\SessionScoped;

#[ApplicationScoped, AutoSerializable]
class AttributeApplicationScopedMock {
	private string $applicationScopedStr;

	#[SessionScoped]
	private $sessionScopedStr;

	/**
	 * @return string
	 */
	public function getApplicationScopedStr() {
		return $this->applicationScopedStr;
	}

	/**
	 * @param string $applicationScopedStr
	 */
	public function setApplicationScopedStr($applicationScopedStr) {
		$this->applicationScopedStr = $applicationScopedStr;
	}

	/**
	 * @return string
	 */
	public function getSessionScopedStr() {
		return $this->sessionScopedStr;
	}

	/**
	 * @param string $sessionScopedStr
	 */
	public function setSessionScopedStr($sessionScopedStr) {
		$this->sessionScopedStr = $sessionScopedStr;
	}
}