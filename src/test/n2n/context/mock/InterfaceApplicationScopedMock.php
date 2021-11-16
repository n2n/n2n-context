<?php
namespace n2n\context\mock;

use n2n\context\ApplicationScoped;
use n2n\context\AutoSerializable;

class InterfaceApplicationScopedMock implements ApplicationScoped, AutoSerializable {
	private string $applicationScopedStr;

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