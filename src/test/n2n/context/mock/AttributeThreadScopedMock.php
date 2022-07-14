<?php
namespace n2n\context\mock;

use n2n\context\attribute\ThreadScoped;
use n2n\context\attribute\Inject;

#[ThreadScoped]
class AttributeThreadScopedMock {
	#[Inject]
	public AttributeRequestScopedMock $requestScoped;
}