<?php

namespace n2n\context;

use Psr\Container\NotFoundExceptionInterface;

class LookupableNotFoundException extends LookupFailedException implements NotFoundExceptionInterface {

}