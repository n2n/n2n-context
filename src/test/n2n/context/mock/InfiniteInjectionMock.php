<?php

namespace n2n\context\mock;

use n2n\context\attribute\RequestScoped;
use n2n\context\attribute\Inject;

#[RequestScoped]
class InfiniteInjectionMock {
	#[Inject]
	public InfiniteInjectionMock $infiniteInjectionMock;
}