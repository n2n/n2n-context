<?php

namespace n2n\context\mock;

use n2n\context\annotation\AnnoSessionScoped;
use n2n\context\SerDataReader;
use n2n\context\SerDataWriter;
use n2n\context\SessionScoped;
use n2n\reflection\annotation\AnnoInit;

class LegacySessionScopedMock implements SessionScoped {
	private static function _annos(AnnoInit $ai) {
		$ai->p('sessionScopedStr', new AnnoSessionScoped());
	}

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

	private function _onSerialize(SerDataWriter $dataWriter) {
		$dataWriter->set('sessionScopedStr', $this->sessionScopedStr);
	}

	private function _onUnserialize(SerDataReader $dataReader) {
		$this->sessionScopedStr = $dataReader->getString('sessionScopedStr');
	}
}