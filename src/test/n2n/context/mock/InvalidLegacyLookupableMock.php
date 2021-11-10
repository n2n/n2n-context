<?php

namespace n2n\context\mock;

use n2n\context\annotation\AnnoApplicationScoped;
use n2n\context\annotation\AnnoSessionScoped;
use n2n\context\Lookupable;
use n2n\reflection\annotation\AnnoInit;

class InvalidLegacyLookupableMock implements Lookupable {
	private static function _annos(AnnoInit $ai) {
		$ai->p('sessionScopedStr', new AnnoSessionScoped());
		$ai->p('applicationScopedStr', new AnnoApplicationScoped());
	}

	private $sessionScopedStr;
	private $applicationScopedStr;

	/**
	 * @return mixed
	 */
	public function getSessionScopedStr() {
		return $this->sessionScopedStr;
	}

	/**
	 * @param mixed $sessionScopedStr
	 */
	public function setSessionScopedStr($sessionScopedStr): void {
		$this->sessionScopedStr = $sessionScopedStr;
	}

	/**
	 * @return mixed
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