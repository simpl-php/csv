<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Simpl\Csv\Reader;

class ReaderTest extends TestCase
{
	static function getResourcePath($file = null)
	{
		$resource_path = __DIR__ . '/resources';

		if (!empty($file)){
			return $resource_path . '/' . $file;
		}

		return $resource_path;
	}

	public function testStub()
	{
		$this->assertTrue(true);
	}

	function testCanReadTsv()
	{
		$csv = Reader::createFromFile(static::getResourcePath('captains-tab-delimited.txt'));
		$csv->setColumns(['captain', 'ship', 'series']);
		$csv->setSkipRows(1);
		$csv->setDelimiter($csv::DELIMITER_TAB);

		$row = $csv->read();
		$this->assertEquals("Kirk, James T", $row['captain']);
		$this->assertEquals("Enterprise", $row['ship']);
		$this->assertEquals("TOS", $row['series']);
	}

	function testCanReadCsv()
	{
		$csv = Reader::createFromFile(static::getResourcePath('captains-comma-delimited.csv'));
		$csv->setColumns(['captain', 'ship', 'series']);

		$row = $csv->read();
		$this->assertEquals("captain", $row['captain']);
		$this->assertEquals("ship", $row['ship']);
		$this->assertEquals("series", $row['series']);
	}

	function testCanSkipColumnHeading()
	{
		$csv = Reader::createFromFile(static::getResourcePath('captains-comma-delimited.csv'));
		$csv->setColumns(['captain', 'ship', 'series']);
		$csv->setSkipRows(1);

		$row = $csv->read();
		$this->assertEquals("Kirk, James T", $row['captain']);
		$this->assertEquals("Enterprise", $row['ship']);
		$this->assertEquals("TOS", $row['series']);
	}

	function testCanReadAllRows()
	{
		$csv = Reader::createFromFile(static::getResourcePath('captains-comma-delimited.csv'));
		$csv->setColumns(['captain', 'ship', 'series']);
		$csv->setSkipRows(1);

		$count = 0;
		while($row = $csv->read())
		{
			$count++;
		}

		$this->assertEquals(4, $count);
	}
}