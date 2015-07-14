<?php

use Dormilich\WebService\RIPE\Attribute;
use Dormilich\WebService\RIPE\FixedAttribute;

class FixedAttributeTest extends PHPUnit_Framework_TestCase
{
	public function testAttributeInterfaceIsImplemented()
	{
		$attr = new FixedAttribute('foo', true, []);
		$this->assertInstanceOf('\Dormilich\WebService\RIPE\AttributeInterface', $attr);
	}

	public function testAttributeClassIsExtended()
	{
		$attr = new FixedAttribute('foo', true, []);
		$this->assertInstanceOf('\Dormilich\WebService\RIPE\Attribute', $attr);
	}

	public function testAttributeIsSingle()
	{
		$attr = new FixedAttribute('foo', true, []);
		$this->assertFalse($attr->isMultiple());
	}

	public function testAttributeRequiredness()
	{
		$attr = new FixedAttribute('foo', Attribute::REQUIRED, []);
		$this->assertTrue($attr->isRequired());

		$attr = new FixedAttribute('foo', Attribute::OPTIONAL, []);
		$this->assertFalse($attr->isRequired());
	}

	public function testAttributeAcceptsDefinedValues()
	{
		$attr = new FixedAttribute('foo', Attribute::REQUIRED, ['a', 'b', 'c']);

		$attr->setValue('a');
		$this->assertSame('a', $attr->getValue());

		$attr->setValue('b');
		$this->assertSame('b', $attr->getValue());

		$attr->setValue('c');
		$this->assertSame('c', $attr->getValue());
	}

	/**
	 * @expectedException \Dormilich\WebService\RIPE\InvalidValueException
	 * @expectedExceptionMessageRegExp # \[bar\] #
	 */
	public function testAttributeDoesNotAcceptUndefinedValue()
	{
		$attr = new FixedAttribute('bar', Attribute::REQUIRED, ['a', 'b', 'c']);
		$attr->setValue('x');
	}
}
