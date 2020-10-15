# Simpl/Csv

[![Build Status](https://img.shields.io/travis/simpl-php/csv.svg?style=flat-square)](https://travis-ci.org/simpl-php/csv)

## Reading delimited files... the Simpl way!

### Why should you use this? We already have `fgetcsv()`.

Yes, `fgetcsv()` is great, and this package uses it under the hood - with some quality of life improvements.


The main benefits of this package are:

- Easy to set number of records to skip (like headings)
- You can give it an array of column headings and it will return the parsed row as an associative array.
    - This means you get to work with array keys you define instead of having to remember the numerical position.
- This provides some basic transformations out of the box.
    - Automatically trim all values.
    - Automatically convert empty strings to `null`.
- If you provide column headings, it will compare the number of column headings to the number of columns it
parsed from each row and throw an exception if your data is missing a column.
- Return the full CSV and an array or json object.

## Installation

```bash
composer require simpl/csv
```

## Basic Usage
```php
<?php
use Simpl\Csv\Reader;
$csv = Reader::createFromFile('/path/to/your/file.csv');
$csv->setColumns(['name', 'address', 'phone']);
$csv->setSkipRows(1);

while($row = $csv->read())
{
    print_r($row['address']);
}
```

See <https://simpl-php.com/components/csv> for full documentation.

## Testing

```bash
composer test
```

## Coding Standards
This library uses [PHP_CodeSniffer](http://www.squizlabs.com/php-codesniffer) to ensure coding standards are followed.

I have adopted the [PHP FIG PSR-2 Coding Standard](http://www.php-fig.org/psr/psr-2/) EXCEPT for the tabs vs spaces for indentation rule. PSR-2 says 4 spaces. I use tabs. No discussion.

To support indenting with tabs, I've defined a custom PSR-2 ruleset that extends the standard [PSR-2 ruleset used by PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/CodeSniffer/Standards/PSR2/ruleset.xml). You can find this ruleset in the root of this project at PSR2Tabs.xml


### Codesniffer

```bash
composer codensiffer
```

### Codefixer

```bash
composer codefixer
```