# CakePHP JsonField Plugin

The **JsonField** plugin treats JSON columns as nested-entity stores for CakePHP applications, with paths as first-class addresses into JSON data.

JsonField enables reading, writing, and querying values at arbitrary paths inside JSON columns: filter, order, and compare against path values in SQL, hydrate nested data into real entities, and persist changes as atomic engine-native path updates instead of whole-column rewrites.

The plugin declares schema and per-path types with PHP attributes on entity classes, and supports embedded entities as well as foreign keys stored inside JSON with joins to related tables. It includes engine adapters for PostgreSQL, MySQL, and SQLite, resolved automatically from the connection driver.

The plugin integrates seamlessly with CakePHP's ORM and provides database types, query expressions, and a finder for working with JSON paths.

## Requirements

* PHP 8.2+

See [Versions.md](docs/Versions.md) for the supported CakePHP versions.

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org):

```
composer require crustum/cakephp-json-field
```

Load the plugin:

```
bin/cake plugin load Crustum/JsonField
```

## Documentation

For documentation, as well as tutorials, see the [docs](docs/index.md) directory of this repository.

## License

Licensed under the [MIT](http://www.opensource.org/licenses/mit-license.php) License. Redistributions of the source code included in this repository must retain the copyright notice found in each file.
