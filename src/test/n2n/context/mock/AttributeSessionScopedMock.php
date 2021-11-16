<?php

namespace n2n\context\mock;

use n2n\context\attribute\ApplicationScoped;
use n2n\context\attribute\SessionScoped;

#[SessionScoped]
class AttributeSessionScopedMock {
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
}