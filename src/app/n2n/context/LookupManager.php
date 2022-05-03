<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\context;

use n2n\util\UnserializationFailedException;
use n2n\reflection\ReflectionUtils;
use n2n\reflection\ReflectionContext;
use n2n\util\cache\CacheStore;
use n2n\util\StringUtils;
use n2n\reflection\attribute\PropertyAttribute;
use n2n\reflection\magic\MagicUtils;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\context\config\LookupSession;
use n2n\util\magic\MagicContext;
use n2n\context\attribute\ApplicationScoped;
use n2n\context\attribute\SessionScoped;
use n2n\util\ex\IllegalStateException;

class LookupManager {
	const SESSION_KEY_PREFIX = 'lookupManager.sessionScoped.';
	const SESSION_CLASS_PROPERTY_KEY_SEPARATOR = '.';
	const ON_SERIALIZE_METHOD = '_onSerialize';
	const ON_UNSERIALIZE_METHOD = '_onUnserialize';
	
	private $session;
	protected $shutdownClosures = array();
	
	protected $requestScope = array();
	protected $sessionScope = array();
	protected $applicationScope = array();
	private $magicContext;

	/**
	 * @param LookupSession $lookupSession
	 * @param CacheStore $applicationCacheStore
	 */
	public function __construct(LookupSession $lookupSession, CacheStore $applicationCacheStore,
			MagicContext $magicContext) {
		$this->session = $lookupSession;
		$this->applicationCacheStore = $applicationCacheStore;
		$this->magicContext = $magicContext;
	}

	/**
	 * @return LookupSession
	 */
	function getLookupSession() {
		return $this->session;
	}

	/**
	 * @return CacheStore
	 */
	function getApplicationCacheStore() {
		return $this->applicationCacheStore;
	}

	/**
	 * @return MagicContext
	 */
	function getMagicContext() {
		return $this->magicContext;
	}

	/**
	 * @return LookupManager
	 */
	function copy(bool $contentsIncluded = false) {
		$lookupManager = new LookupManager($this->session, $this->applicationCacheStore, $this->magicContext);

		if ($contentsIncluded) {
			$lookupManager->requestScope = $this->requestScope;
			$lookupManager->sessionScope = $this->sessionScope;
			$lookupManager->applicationScope = $this->applicationScope;
			$lookupManager->shutdownClosures = $this->shutdownClosures;
		}

		return $lookupManager;
	}

	public function clear() {
		$this->terminateScope($this->requestScope);
		$this->terminateScope($this->sessionScope);
		$this->terminateScope($this->applicationScope);
		$this->shutdownClosures = array();

		IllegalStateException::assertTrue(empty($this->requestScope) && empty($this->sessionScope)
				&& empty($this->applicationScope));
	}

	private function terminateScope(&$objs) {
		while (null !== ($obj = array_pop($objs))) {
			MagicUtils::terminate($obj);
		}
	}

	/**
	 * @param string $className
	 * @return bool
	 */
	function contains(string $className) {
		return isset($this->requestScope[$className]) || isset($this->sessionScope[$className])
				|| isset($this->applicationScope[$className]);
	}

	/**
	 * @param string $className
	 * @throws LookupFailedException
	 * @return Lookupable
	 */
	public function lookup(string $className) {
		if (empty($className)) {
			throw new LookupFailedException('Name is empty.');
		}
		$class = null;
		if ($className instanceof \ReflectionClass) {
			$class = $className;
		} else {
			$class = ReflectionUtils::createReflectionClass($className);
		}

		return $this->lookupByClass($class);
	}
	/**
	 * @param \ReflectionClass $class
	 * @throws LookupFailedException
	 * @throws ModelErrorException
	 */
	public function lookupByClass(\ReflectionClass $class) {
		if ($this->isRequestScoped($class)) {
			return $this->checkoutRequestScoped($class);
		}

		if ($this->isSessionScoped($class)) {
			return $this->checkoutSessionModel($class, $this->isAutoSerializable($class));
		}

		if ($this->isApplicationScoped($class)) {
			return $this->checkoutApplicationModel($class, $this->isAutoSerializable($class));
		}
		
		if ($this->isLookupable($class)) {
			return $this->checkoutLookupable($class);
		}
		
		throw new LookupFailedException('Class is not marked as lookupable: ' . $class->getName());
	}
	/**
	 * @param PropertyAttribute $attribute
	 * @return ModelErrorException
	 */
	private function createErrorException(PropertyAttribute $attribute) {
		return new ModelErrorException('Attribute disallowed for simple Lookupables',
				$attribute->getFile(), $attribute->getLine());
	}
	/**
	 * @param \ReflectionClass $class
	 * @throws ModelErrorException
	 * @return Lookupable
	 */
	private function checkoutLookupable(\ReflectionClass $class) {
		$attributeSet = ReflectionContext::getAttributeSet($class);

		foreach ($attributeSet->getPropertyAttributesByName(SessionScoped::class) as $attribute) {
			throw $this->createErrorException($attribute);
		}

		foreach ($attributeSet->getPropertyAttributesByName(ApplicationScoped::class) as $attribute) {
			throw $this->createErrorException($attribute);
		}
		
		$obj = ReflectionUtils::createObject($class);
		MagicUtils::init($obj, $this->magicContext);
		return $obj;
	}
	/**
	 * @param \ReflectionClass $class
	 * @throws LookupFailedException
	 * @return RequestScoped
	 */
	private function checkoutRequestScoped(\ReflectionClass $class) {
		if (isset($this->requestScope[$class->getName()])) {
			return $this->requestScope[$class->getName()];
		}
		
		$obj = ReflectionUtils::createObject($class);
		$this->checkForSessionProperties($class, $obj);
		$this->checkForApplicationProperties($class, $obj);
		$this->requestScope[$class->getName()] = $obj;
		MagicUtils::init($obj, $this->magicContext);
		return $obj;
	}

	/**
	 * @param \ReflectionClass $class
	 * @throws LookupFailedException
	 */
	private function checkForSessionProperties(\ReflectionClass $class, $obj) {
		$attributeSet = ReflectionContext::getAttributeSet($class);
		if (!$attributeSet->containsPropertyAttributeName(SessionScoped::class)) {
			return;
		}

		foreach ($attributeSet->getPropertyAttributesByName(SessionScoped::class) as $sessionScopedAttr) {
			$property = $sessionScopedAttr->getProperty();
			$propertyName = $property->getName();
			
			$property->setAccessible(true);
			
			$key = self::SESSION_KEY_PREFIX 
					. $class->getName() . self::SESSION_CLASS_PROPERTY_KEY_SEPARATOR . $propertyName;
			if ($this->session->has(LookupManager::class, $key)) {
				try {
					$property->setValue($obj, StringUtils::unserialize($this->session->get(LookupManager::class, $key)));
				} catch (UnserializationFailedException $e) {
					$this->session->remove(LookupManager::class, $key);
				}
			}

			$session = $this->session;
			$this->shutdownClosures[] = function () use ($key, $session, $property, $obj) {
				$session->set(LookupManager::class, $key, serialize($property->getValue($obj)));
			};
		}
	}
	/**
	 * @param \ReflectionClass $class
	 * @throws LookupFailedException
	 * @return SessionScoped
	 */
	private function checkoutSessionModel(\ReflectionClass $class, $autoSerializable) {
		if (isset($this->sessionScope[$class->getName()])) {
			return $this->sessionScope[$class->getName()];
		}

		$key = self::SESSION_KEY_PREFIX . $class->getName();
		$obj = $this->readSessionModel($key, $class, $autoSerializable);
		if ($obj === null) {
			$obj = ReflectionUtils::createObject($class);
			$this->checkForApplicationProperties($class, $obj);
			MagicUtils::init($obj, $this->magicContext);
		}

		$this->shutdownClosures[] = function () use ($key, $class, $obj, $autoSerializable) {
			$this->writeSessionModel($key, $obj, $class, $autoSerializable);
		};
		return $this->sessionScope[$class->getName()] = $obj;
	}
	
	private function readSessionModel($key, \ReflectionClass $class, $autoSerializable) {
		if (!$this->session->has(LookupManager::class, $key)) return null;

		$serData = $this->session->get(LookupManager::class, $key);

		if ($autoSerializable) {
			try {
				$obj = StringUtils::unserialize($serData);
				if (ReflectionUtils::isObjectA($obj, $class)) { 
					$this->checkForApplicationProperties($class, $obj);
					return $obj;
				}
			} catch (UnserializationFailedException $e) {}

			$this->session->remove(LookupManager::class, $key);
			return null;
		}

		$obj = ReflectionUtils::createObject($class);
		$this->checkForApplicationProperties($class, $obj);
		try {
			$this->callOnUnserialize($class, $obj, SerDataReader::createFromSerializedStr($serData));
		} catch (UnserializationFailedException $e) {
			$this->session->remove(LookupManager::class, $key);
			throw new LookupFailedException('Falied to unserialize session model: ' . $class->getName(), 0, $e);
		}
		
		return $obj;
	}

	private function writeSessionModel($key, $obj, \ReflectionClass $class, $autoSerializable) {
		if ($autoSerializable) {
			$this->session->set(LookupManager::class, $key, serialize($obj));
			return;
		}

		$serDataWriter = new SerDataWriter();
		$serDataWriter->set($key, $obj);
		$this->callOnSerialize($class, $obj, $serDataWriter);

		$this->session->set(LookupManager::class, $key, $serDataWriter->serialize());
	}
	/**
	 * @param \ReflectionClass $class
	 * @param Lookupable $obj
	 * @throws LookupFailedException
	 */
	private function checkForApplicationProperties(\ReflectionClass $class, $obj) {
		$attributeSet = ReflectionContext::getAttributeSet($class);
		if (!$attributeSet->containsPropertyAttributeName(ApplicationScoped::class))  {
			return;
		}

		$className = $class->getName();
		foreach ($attributeSet->getPropertyAttributesByName(ApplicationScoped::class) as $applicationScopedAttr) {
			$property = $applicationScopedAttr->getProperty();
			$characteristics = array('prop' => $property->getName());
				
			$property->setAccessible(true);
				
			$propValueSer = null;
			if (null !== ($cacheItem = $this->applicationCacheStore->get($className, $characteristics))) {
				$propValueSer = $cacheItem->getData();
				try {
					$property->setValue($obj, StringUtils::unserialize($propValueSer));	
				} catch (UnserializationFailedException $e) {
					$this->applicationCacheStore->remove($className, $characteristics);
				}
			}

			$this->shutdownClosures[] = function () use ($className, $characteristics, $propValueSer, $property, $obj) {
				$newPropValueSer = serialize($property->getValue($obj));
				if ($newPropValueSer != $propValueSer) {
					$this->applicationCacheStore->store($className, $characteristics, $newPropValueSer);
				}
			};
		}
	}
	/**
	 * @param \ReflectionClass $class
	 * @throws LookupFailedException
	 * @return ApplicationScoped
	 */
	private function checkoutApplicationModel(\ReflectionClass $class, $autoSerializable) {
		$className = $class->getName();
		if (isset($this->applicationScope[$className])) {
			return $this->applicationScope[$className];
		}

		$serData = null;
		$obj = $this->readApplicationModel($class, $autoSerializable, $serData);
		
		if ($obj === null) {
			$obj = ReflectionUtils::createObject($class);
			$this->checkForSessionProperties($class, $obj);
			MagicUtils::init($obj, $this->magicContext);
		}
		
		$this->shutdownClosures[] = function () use ($class, $obj, $autoSerializable, $serData) {
			$this->writeApplicationModel($obj, $class, $autoSerializable, $serData);
		};
		
		return $this->applicationScope[$class->getName()] = $obj;
	}
	

	private function readApplicationModel(\ReflectionClass $class, $autoSerializable, &$serData) {
		$className = $class->getName();
		
		$cacheItem = $this->applicationCacheStore->get($className, array());
		if (null === $cacheItem) return null;
		
		$serData = $cacheItem->getData();
		
		if ($autoSerializable) {
			try {
				$obj = StringUtils::unserialize($serData);
					
				if (ReflectionUtils::isObjectA($obj, $class)) {
					$this->checkForSessionProperties($class, $obj);
					return $obj;	
				}
			} catch (UnserializationFailedException $e) {}
			
			$this->applicationCacheStore->remove($className, array());
			return null;
		}	
	
		$obj = ReflectionUtils::createObject($class);
		$this->checkForSessionProperties($class, $obj);
	
		try {
			$this->callOnUnserialize($class, $obj, SerDataReader::createFromSerializedStr($serData));
		} catch (UnserializationFailedException $e) {
			$this->applicationCacheStore->remove($className, array());
			throw new LookupFailedException('Falied to unserialize application model: ' . $class->getName(), 0, $e);
		}
	
		return $obj;
	}
	
	private function writeApplicationModel($obj, \ReflectionClass $class, $autoSerializable, $oldSerData) {
		$className = $class->getName();
		
		$serData = null;
		if ($autoSerializable) {
			$serData = serialize($obj);
		} else {
			$serDataWriter = new SerDataWriter();
			$this->callOnSerialize($class, $obj, $serDataWriter);
			$serData = $serDataWriter->serialize();
		}
		
		if ($serData != $oldSerData) {
			$this->applicationCacheStore->store($className, array(), $serData);
		}
	}
	
	private function callOnUnserialize(\ReflectionClass $class, $obj, SerDataReader $serDataReader) {
		$magicMethodInvoker = new MagicMethodInvoker($this->magicContext);
		$magicMethodInvoker->setClassParamObject(get_class($serDataReader), $serDataReader);
		$this->callMagcMethods($class, self::ON_UNSERIALIZE_METHOD, $obj, $magicMethodInvoker);
	}
	
	private function callOnSerialize(\ReflectionClass $class, $obj, SerDataWriter $serDataWriter) {
		$magicMethodInvoker = new MagicMethodInvoker($this->magicContext);
		$magicMethodInvoker->setClassParamObject(get_class($serDataWriter), $serDataWriter);
		$this->callMagcMethods($class, self::ON_SERIALIZE_METHOD, $obj, $magicMethodInvoker);
	}
		
	private function callMagcMethods(\ReflectionClass $class, $methodName, $obj, MagicMethodInvoker $magicMethodInvoker) {
		$methods = ReflectionUtils::extractMethodHierarchy($class, $methodName);
		
		if (0 == count($methods)) {
			throw new ModelErrorException('Magic method missing: ' . $class->getName() . '::'
					. $methodName . '()', $class->getFileName(), $class->getStartLine());
		}
		
		foreach ($methods as $method) {
			MagicUtils::validateMagicMethodSignature($method);

			$method->setAccessible(true);
			$magicMethodInvoker->invoke($obj, $method);
		}
	}
	/* (non-PHPdoc)
	 * @see \n2n\core\ShutdownListener::onShutdown()
	 */
	public function shutdown() {
		foreach ($this->shutdownClosures as $shutdownClosure) {
			$shutdownClosure();
		}
	}

	private function isApplicationScoped(\ReflectionClass $class) {
		return !empty($class->getAttributes(ApplicationScoped::class))
				|| $class->implementsInterface(\n2n\context\ApplicationScoped::class);
	}

	private function isSessionScoped(\ReflectionClass $class) {
		return !empty($class->getAttributes(SessionScoped::class))
				|| $class->implementsInterface(\n2n\context\SessionScoped::class);
	}

	private function isAutoSerializable(\ReflectionClass $class) {
		return !empty($class->getAttributes(\n2n\context\attribute\AutoSerializable::class))
				|| $class->implementsInterface(AutoSerializable::class);
	}

	private function isLookupable($class) {
		return !empty($class->getAttributes(\n2n\context\attribute\Lookupable::class))
				|| $class->implementsInterface(Lookupable::class);
	}

	private function isRequestScoped($class) {
		return !empty($class->getAttributes(\n2n\context\attribute\RequestScoped::class))
				|| $class->implementsInterface(RequestScoped::class);
	}
}
