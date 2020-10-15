<?php

namespace Simpl\Csv;

class Reader
{
	# Common delimiters
	const DELIMITER_COMMA = ",";
	const DELIMITER_PIPE = "|";
	const DELIMITER_TAB = "\t";

	# Arguments for fgetscv()
	public $max_line_length = null;
	public $delimiter = ",";
	public $enclosure = '"';
	public $escape = '\\';

	public $skip_count = 0;
	public $current_line = 0;

	public $columns = [];

	/**
	 * @var Reader
	 */
	public static $instance;

	/**
	 * @var false|resource
	 */
	public $file_handle;

	/**
	 * Reader constructor.
	 * @param $filename
	 * @param string $mode
	 * @throws \Exception
	 */
	public function __construct($filename, $mode = 'r')
	{
		if (!file_exists($filename)) {
			$message = sprintf('File %s not found.', $filename);
			throw new FileNotFoundException($message);
		}

		$this->file_handle = fopen($filename, 'r');
	}

	/**
	 * @param $filename
	 * @param string $mode
	 * @return Reader
	 * @throws \Exception
	 */
	public static function createFromFile($filename, $mode = 'r')
	{
		static::$instance = new Reader($filename, $mode);

		return static::$instance;
	}

	/**
	 * @param bool $trim
	 * @param bool $convert_empty_to_null
	 * @return array|false|null
	 * @throws InvalidColumnCountException
	 */
	public function read($trim = true, $convert_empty_to_null = true)
	{
		++$this->current_line;

		$row = fgetcsv($this->file_handle, $this->max_line_length, $this->delimiter, $this->enclosure, $this->escape);

		if ($this->skip_count > 0 && $this->current_line <= $this->skip_count) {
			return $this->read($trim, $convert_empty_to_null);
		}

		if (!$row) {
			return false;
		}

		if ($trim) {
			$row = array_map(function ($v) {
				return trim($v);
			}, $row);
		}

		if ($convert_empty_to_null) {
			$row = array_map(function ($v) {
				return trim($v) == '' ? null : $v;
			}, $row);
		}

		// Combine the parsed row array with the headings array so we can work with this as an associate
		// array with sane keys.
		$columns = $this->getColumns();

		if (!empty($columns)) {
			$expected_count = count($columns);
			$actual_count = count($row);

			if ($expected_count != $actual_count) {
				$error = sprintf('Field Count %d != Heading Count %s', $actual_count, $expected_count);
				throw new InvalidColumnCountException($error);
			}

			$row = array_combine($columns, $row);
		}

		return $row;
	}

	public function toArray($trim = true, $convert_empty_to_null = true)
	{
		$parsed = [];

		$this->rewind();

		while ($row = $this->read($trim, $convert_empty_to_null)) {
			$parsed[] = $row;
		}

		return $parsed;
	}

	public function toJson($trim = true, $convert_empty_to_null = true)
	{
		return json_encode($this->toArray($trim, $convert_empty_to_null), JSON_PRETTY_PRINT);
	}

	public function rewind()
	{
		$this->current_line = 0;
		rewind($this->file_handle);
	}

	public function setSkipRows($skip = 0)
	{
		$this->skip_count = $skip;
	}

	public function setDelimiter($delimiter)
	{
		$this->delimiter = $delimiter;
	}

	public function setEnclosure($enclosure)
	{
		$this->enclosure = $enclosure;
	}

	public function setEscapeCharacter($escape)
	{
		$this->escape = $escape;
	}

	public function setMaxLinelength($max)
	{
		$this->max_line_length = $max;
	}

	public function setColumns($columns = [])
	{
		$this->columns = $columns;
	}

	/**
	 * @return array
	 */
	public function getColumns()
	{
		return $this->columns;
	}
}
