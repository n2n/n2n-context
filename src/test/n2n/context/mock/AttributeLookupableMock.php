<?php
namespace n2n\context\mock;

use n2n\context\attribute\Lookupable;
use n2n\context\attribute\Inject;

#[Lookupable]
class AttributeLookupableMock {
	#[Inject]
	public AttributeRequestScopedMock $requestScoped;
}