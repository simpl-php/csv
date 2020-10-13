<?php

namespace Simpl;

class Library
{
	const DELIMITER_COMMA = ",";
	const DELIMITER_PIPE = "|";
	const DELIMITER_TAB = "\t";

	public $max_line_length = null;
	public $delimiter = ",";
	public $enclosure = '"';
	public $escape = '\\';
	public $skip_first_row = false;

	public $transformations = ['trim'];

	/**
	 * @param $filename
	 * @param string $mode
	 * @return mixed
	 * @throws \Exception
	 */
	public function read($filename, $mode = 'r')
	{
		$fh = fopen('file.txt', 'r');

		$headings = $this->getHeadings();
		$expected_count = count($headings);

		$i = 0;
		while($row = fgetcsv($fh, $this->max_line_length, $this->delimiter, $this->enclosure, $this->escape)){

			if (++$i == 1 && $this->skip_first_row){
				// Skip the first row.
				continue;
			}

			// Combine the parsed row array with the headings array so we can work with this as an associate array with sane keys.
			$row = array_combine($headings, $row);
			$actual_count = count($row);

			if ($expected_count != $actual_count){
				$error = sprintf('Field Count %d != Heading Count %s', $actual_count, $expected_count);
				throw new \Exception($error);
			}

			return $this->transform($row);
		}
	}

	/**
	 * @param $row
	 * @return mixed
	 */
	public function transform($row)
	{
		// What transformations do we want?
		// Ideas: trim, cast/convert dates, integers, bool
		return $row;
	}

	/**
	 * @return array
	 */
	public function getHeadings()
	{
		return [];
	}
}