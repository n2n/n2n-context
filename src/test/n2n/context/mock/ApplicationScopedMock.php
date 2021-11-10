<?php
namespace n2n\context\mock;

use n2n\context\ApplicationScoped;

class ApplicationScopedMock implements ApplicationScoped {
	#[\n2n\context\attribute\ApplicationScoped]
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