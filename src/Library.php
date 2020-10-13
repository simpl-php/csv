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
		if (!file_exists($filename) || !is_readable($filename)){
			$message = sprintf('File %s not found.', $filename);
			throw new \Exception($message);
		}

		if (!is_readable($filename)){
			$message = sprintf('File %s not readable.', $filename);
			throw new \Exception($message);
		}

		$fh = fopen($filename, 'r');

		$headings = $this->getColumns();
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
		// Ideas: trim, convert null, convert bool

		$transformations = $this->getTransformations();

		if (array_key_exists('*', $transformations)){
			// Perform this transformation on every item in the array.
			foreach ($row as $key=>$value){
				$this->transformItem($row, $transformations['*']);
			}
		}

		foreach ($row as $key=>$value){
			if (array_key_exists('*', $transformations)){
				// Perform this transformation on every item.
				$this->transformItem($row, $transformations['*']);
			}

			if (array_key_exists($key, $transformations)){
				// Perform transformation specific to this item.
				$this->transformItem($row, $transformations[$key]);
			}
		}

		return $row;
	}

	public function transformItem($item, $transformation)
	{
		$transformations = explode('|', $transformation);

		foreach ($transformations as $t){

			// trim the string.
			if ($t == 'trim'){
				$item = trim($item);
			}

			// Convert empty values to null.
			if ($t == 'null'){
				if (trim($t) == ''){
					$t = null;
				}
			}

			// Convert truthy values to boolean.
			if ($t == 'bool'){
				$t = filter_var($t, FILTER_VALIDATE_BOOL);
			}
		}

		return $item;
	}

	public function getTransformations()
	{
		return [
			'*' => 'trim|null|bool'
		];
	}

	/**
	 * @return array
	 */
	public function getColumns()
	{
		return [];
	}
}