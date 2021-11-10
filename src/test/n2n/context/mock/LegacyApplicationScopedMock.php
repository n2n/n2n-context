<?php

namespace n2n\context\mock;

use n2n\context\annotation\AnnoApplicationScoped;
use n2n\context\ApplicationScoped;
use n2n\reflection\annotation\AnnoInit;

class LegacyApplicationScopedMock implements ApplicationScoped {
	private static function _annos(AnnoInit $ai) {
		$ai->p('applicationScopedStr', new AnnoApplicationScoped());
	}

	private string $applicationScopedStr;

	/**
	 * @return string
	 */
	public function getApplicationScopedStr() {
		return $this->applicationScopedStr;
	}

	/**
	 * @param mixed $applicationScopedStr
	 */
	public function setApplicationScopedStr($applicationScopedStr): void {
		$this->applicationScopedStr = $applicationScopedStr;
	}
}