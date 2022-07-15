<?php

namespace n2n\context\mock;

use n2n\context\attribute\Inject;
use n2n\context\attribute\ThreadScoped;

#[ThreadScoped]
class InvalidInjectTypeMock {
	#[Inject]
	protected SimpleClassMock $simpleClassMock;
}