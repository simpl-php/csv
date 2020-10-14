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
		if (!file_exists($filename) || !is_readable($filename)) {
			$message = sprintf('File %s not found.', $filename);
			throw new FileNotFoundException($message);
		}

		if (!is_readable($filename)) {
			$message = sprintf('File %s not readable.', $filename);
			throw new FileNotReadableException($message);
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

	public function read($trim = true, $convert_empty_to_null = true)
	{
		++$this->current_line;

		$columns = $this->getColumns();

		$expected_count = false;

		if (!empty($columns)) {
			$expected_count = count($columns);
		}

		$row = fgetcsv($this->file_handle, $this->max_line_length, $this->delimiter, $this->enclosure, $this->escape);

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

		if ($this->skip_count > 0 && $this->current_line <= $this->skip_count) {
			return $this->read();
		}

		// Combine the parsed row array with the headings array so we can work with this as an associate
		// array with sane keys.
		if (!empty($columns)) {
			$row = array_combine($columns, $row);
		}

//		$actual_count = count($row);
//
//		if ($expected_count !== false && $expected_count != $actual_count){
//			$error = sprintf('Field Count %d != Heading Count %s', $actual_count, $expected_count);
//			throw new \Exception($error);
//		}
//
		return $row;
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
