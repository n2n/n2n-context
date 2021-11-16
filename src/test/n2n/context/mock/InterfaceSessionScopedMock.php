<?php
namespace n2n\context\mock;

use n2n\context\AutoSerializable;
use n2n\context\SessionScoped;

class InterfaceSessionScopedMock implements SessionScoped, AutoSerializable {
	private string $sessionScopedStr;

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
}