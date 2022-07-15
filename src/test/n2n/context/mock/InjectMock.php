<?php

namespace n2n\context\mock;

use n2n\context\attribute\Inject;
use n2n\context\attribute\RequestScoped;

#[RequestScoped]
class InjectMock {
	#[Inject]
	public AttributeLookupableMock $attributeLookupableMock;
	#[Inject]
	protected AttributeRequestScopedMock $attrRequestScopedMock;
	#[Inject]
	private InterfaceRequestScopedMock $interfaceRequestScopedMock;
	#[Inject]
	private AttributeSessionScopedMock $attributeSessionScopedMock;
	#[Inject]
	private InterfaceSessionScopedMock $interfaceSessionScopedMock;
	#[Inject]
	private AttributeThreadScopedMock $attributeThreadScopedMock;
	#[Inject]
	private InterfaceThreadScopedMock $interfaceThreadScopedMock;
	#[Inject]
	private AttributeApplicationScopedMock $attributeApplicationScopedMock;
	#[Inject]
	private InterfaceApplicationScopedMock $interfaceApplicationScopedMock;

	/**
	 * @return AttributeLookupableMock
	 */
	public function getAttributeLookupableMock(): AttributeLookupableMock {
		return $this->attributeLookupableMock;
	}

	/**
	 * @param AttributeLookupableMock $attributeLookupableMock
	 */
	public function setAttributeLookupableMock(AttributeLookupableMock $attributeLookupableMock): void {
		$this->attributeLookupableMock = $attributeLookupableMock;
	}

	/**
	 * @return AttributeRequestScopedMock
	 */
	public function getAttrRequestScopedMock(): AttributeRequestScopedMock {
		return $this->attrRequestScopedMock;
	}

	/**
	 * @param AttributeRequestScopedMock $attrRequestScopedMock
	 */
	public function setAttrRequestScopedMock(AttributeRequestScopedMock $attrRequestScopedMock): void {
		$this->attrRequestScopedMock = $attrRequestScopedMock;
	}

	/**
	 * @return InterfaceRequestScopedMock
	 */
	public function getInterfaceRequestScopedMock(): InterfaceRequestScopedMock {
		return $this->interfaceRequestScopedMock;
	}

	/**
	 * @param InterfaceRequestScopedMock $interfaceRequestScopedMock
	 */
	public function setInterfaceRequestScopedMock(InterfaceRequestScopedMock $interfaceRequestScopedMock): void {
		$this->interfaceRequestScopedMock = $interfaceRequestScopedMock;
	}

	/**
	 * @return AttributeSessionScopedMock
	 */
	public function getAttributeSessionScopedMock(): AttributeSessionScopedMock {
		return $this->attributeSessionScopedMock;
	}

	/**
	 * @param AttributeSessionScopedMock $attributeSessionScopedMock
	 */
	public function setAttributeSessionScopedMock(AttributeSessionScopedMock $attributeSessionScopedMock): void {
		$this->attributeSessionScopedMock = $attributeSessionScopedMock;
	}

	/**
	 * @return InterfaceSessionScopedMock
	 */
	public function getInterfaceSessionScopedMock(): InterfaceSessionScopedMock {
		return $this->interfaceSessionScopedMock;
	}

	/**
	 * @param InterfaceSessionScopedMock $interfaceSessionScopedMock
	 */
	public function setInterfaceSessionScopedMock(InterfaceSessionScopedMock $interfaceSessionScopedMock): void {
		$this->interfaceSessionScopedMock = $interfaceSessionScopedMock;
	}

	/**
	 * @return AttributeThreadScopedMock
	 */
	public function getAttributeThreadScopedMock(): AttributeThreadScopedMock {
		return $this->attributeThreadScopedMock;
	}

	/**
	 * @param AttributeThreadScopedMock $attributeThreadScopedMock
	 */
	public function setAttributeThreadScopedMock(AttributeThreadScopedMock $attributeThreadScopedMock): void {
		$this->attributeThreadScopedMock = $attributeThreadScopedMock;
	}

	/**
	 * @return InterfaceThreadScopedMock
	 */
	public function getInterfaceThreadScopedMock(): InterfaceThreadScopedMock {
		return $this->interfaceThreadScopedMock;
	}

	/**
	 * @param InterfaceThreadScopedMock $interfaceThreadScopedMock
	 */
	public function setInterfaceThreadScopedMock(InterfaceThreadScopedMock $interfaceThreadScopedMock): void {
		$this->interfaceThreadScopedMock = $interfaceThreadScopedMock;
	}

	/**
	 * @return AttributeApplicationScopedMock
	 */
	public function getAttributeApplicationScopedMock(): AttributeApplicationScopedMock {
		return $this->attributeApplicationScopedMock;
	}

	/**
	 * @param AttributeApplicationScopedMock $attributeApplicationScopedMock
	 */
	public function setAttributeApplicationScopedMock(AttributeApplicationScopedMock $attributeApplicationScopedMock): void {
		$this->attributeApplicationScopedMock = $attributeApplicationScopedMock;
	}

	/**
	 * @return InterfaceApplicationScopedMock
	 */
	public function getInterfaceApplicationScopedMock(): InterfaceApplicationScopedMock {
		return $this->interfaceApplicationScopedMock;
	}

	/**
	 * @param InterfaceApplicationScopedMock $interfaceApplicationScopedMock
	 */
	public function setInterfaceApplicationScopedMock(InterfaceApplicationScopedMock $interfaceApplicationScopedMock): void {
		$this->interfaceApplicationScopedMock = $interfaceApplicationScopedMock;
	}
}