<?php
namespace n2n\context\mock;

use n2n\context\SessionScoped;

class SessionScopedMock implements SessionScoped {
	#[\n2n\context\attribute\SessionScoped]
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