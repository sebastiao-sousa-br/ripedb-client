<?php

use Dormilich\WebService\RIPE\Attribute;
use Dormilich\WebService\RIPE\AttributeInterface as Attr;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
	public function testAttributeInterfaceIsImplemented()
	{
		$attr = new Attribute('foo', true, true);
		$this->assertInstanceOf('\Dormilich\WebService\RIPE\AttributeInterface', $attr);
	}

	public function testAttributeHasCorrectName()
	{
		$attr = new Attribute('foo', true, true);
		$this->assertSame('foo', $attr->getName());

		$attr = new Attribute(1.8, true, true);
		$this->assertSame('1.8', $attr->getName());
	}

	public function testAttributeIsEmptyByDefault()
	{
		$attr = new Attribute('foo', true, true);
		$this->assertFalse($attr->isDefined());
		$this->assertNull($attr->getValue());
	}

	public function constructorPropertyProvider()
	{
		return [
			[true,  true, true,  true], [true,  false, true,  false], 
			[false, true, false, true], [false, false, false, false], 
			[0,     1,    false, true], ['x',   NULL,  true,  false],
			[Attr::REQUIRED, Attr::SINGLE,   true,  false],
			[Attr::OPTIONAL, Attr::MULTIPLE, false, true],
		];
	}

	/**
	 * @dataProvider constructorPropertyProvider
	 */
	public function testAttributeHasCorrectPropertiesSet($required, $multiple, $expect_required, $expect_multiple)
	{
		$attr = new Attribute('foo', $required, $multiple);

		$this->assertSame($expect_required, $attr->isRequired());
		$this->assertSame($expect_multiple, $attr->isMultiple());
	}

	public function testAttributeConvertsInputToStrings()
	{
		$attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);

		$attr->setValue(1);
		$this->assertSame('1', $attr->getValue());

		$attr->setValue(2.718);
		$this->assertSame('2.718', $attr->getValue());

		$attr->setValue('bar');
		$this->assertSame('bar', $attr->getValue());

		$test = new Test\StringObject;
		$attr->setValue($test);
		$this->assertSame('test', $attr->getValue());

		// I am not aware that the RIPE DB uses booleans somewhere…
		$attr->setValue(true);
		$this->assertSame('true', $attr->getValue());

		$attr->setValue(false);
		$this->assertSame('false', $attr->getValue());
	}

	public function testNullResetsAttributeValue()
	{
		$attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);
		$attr->setValue('foo');
		$attr->setValue(NULL);
		$this->assertFalse($attr->isDefined());
	}

	/**
	 * @expectedException \Dormilich\WebService\RIPE\Exceptions\InvalidDataTypeException
	 * @expectedExceptionMessageRegExp # \[foo\] #
	 */
	public function testAttributeDoesNotAcceptResource()
	{
		$attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);
		$attr->setValue(tmpfile());
	}

	/**
	 * @expectedException \Dormilich\WebService\RIPE\Exceptions\InvalidDataTypeException
	 * @expectedExceptionMessageRegExp # \[foo\] #
	 */
	public function testAttributeDoesNotAcceptObject()
	{
		$attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);
		$attr->setValue(new stdClass);
	}

	public function testSingleAttributeOnlyHasOneValue()
	{
		$attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);

		$attr->setValue('fizz');
		$this->assertSame('fizz', $attr->getValue());

		$attr->setValue('buzz');
		$this->assertSame('buzz', $attr->getValue());

		$attr->addValue('bar');
		$this->assertSame('bar', $attr->getValue());
	}

	/**
	 * @expectedException \Dormilich\WebService\RIPE\Exceptions\InvalidDataTypeException
	 */
	public function testSingleAttributeDoesNotAllowArrayInput()
	{
		$attr = new Attribute('foo', Attr::REQUIRED, Attr::SINGLE);
		$attr->setValue(['fizz', 'buzz']);
	}

	public function testMultipleAttributeReturnsList()
	{
		$attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);

		$attr->addValue('fizz');
		$this->assertSame(['fizz'], $attr->getValue());

		$attr->addValue('buzz');
		$this->assertSame(['fizz', 'buzz'], $attr->getValue());
	}

	public function testSetValueResetsAttributeValue()
	{
		$attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);

		$attr->setValue('fizz');
		$this->assertSame(['fizz'], $attr->getValue());

		$attr->setValue('buzz');
		$this->assertSame(['buzz'], $attr->getValue());
	}

	public function testMultipleAttributeAllowsStringArray()
	{
		$attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);

		$attr->setValue(['fizz', 'buzz']);
		$this->assertSame(['fizz', 'buzz'], $attr->getValue());
	}

	/**
	 * @expectedException \Dormilich\WebService\RIPE\Exceptions\InvalidDataTypeException
	 */
	public function testMultipleAttributeDoesNotAllowNonScalarArray()
	{
		$attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);
		$attr->setValue([NULL]);
	}

	/**
	 * @expectedException \Dormilich\WebService\RIPE\Exceptions\InvalidDataTypeException
	 */
	public function testMultipleAttributeDoesNotAllowNestedArray()
	{
		$attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);
		$attr->setValue(['bar', [1,2,3]]);
	}

	public function testMultipleAttributeIgnoresArrayKeys()
	{
		$attr = new Attribute('foo', Attr::REQUIRED, Attr::MULTIPLE);

		$attr->setValue(['fizz' => 'buzz']);
		$this->assertSame(['buzz'], $attr->getValue());
	}
}
