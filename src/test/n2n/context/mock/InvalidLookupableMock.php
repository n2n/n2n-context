<?php

namespace n2n\context\mock;

use n2n\context\attribute\ApplicationScoped;
use n2n\context\attribute\SessionScoped;
use n2n\context\Lookupable;

class InvalidLookupableMock implements Lookupable {
	#[SessionScoped]
	private string $sessionScopedStr;
	#[ApplicationScoped]
	private string $applicationScopedStr;
}