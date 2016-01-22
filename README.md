# Doctrine Migrations

Implementation of [Doctrine\Migrations](http://docs.doctrine-project.org/projects/doctrine-migrations/en/latest/) to Nette.


## Install

```sh
composer require dtforce/doctrine-migrations
```

Register extensions in `config.neon`:

```yaml
extensions:
	migrations: DTForce\DoctrineMigrations\DI\MigrationsExtension
```


## Configuration

`config.neon` with default values

```yaml
migrations:
	table: doctrine_migrations # database table for applied migrations
	directory: %appDir%/../migrations # directory, where all migrations are stored
	namespace: Migrations # namespace of migration classes
```


## Usage

Open your CLI and run command (based on `DTForce\NetteConsole` integration):

```sh
php bin/console
```

### Migrate changes to database

If you want to migrate existing migration to your database, just run migrate commmand:
 
```sh
php bin/console migrations:migrate
```

If you get lost, just use `-h` option for help:

```sh
php bin/console migrations:migrate -h
```

### Create new migration

To create new empty migration, just run:

```sh
php bin/console migrations:generate
```

A new empty migration will be created at your migrations directory. You can add your sql there then.

Migration that would add new role `"superadmin"` to `user_role` table would look like this:

```php
namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


/**
 * New role "superadmin" added.
 */
final class Version20151015000003 extends AbstractMigration
{

	/**
	 * {@inheritdoc}
	 */
	public function up(Schema $schema)
	{
		$this->addSql("INSERT INTO 'user_role' (id, value, name) VALUES (3, 'superadmin', 'Super Admin')");
	}
	

	/**
	 * {@inheritdoc}
	 */
	public function down(Schema $schema)
	{
		$this->addSql("DELETE FROM 'user_role' WHERE ('id' = 3);");
	}

}
```

As simple as that!


For further use, please check [docs in Symfony bundle](http://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html).


## Features

### Cleanup your directories

If you have over 100 migrations in one directory, it might get messy. How to make it nicer? You can create a subdirectory and move some migrations there. I would group them up by year or by purpose. All subdirectories of `directory` you set up in configuration will be scanned.
 
 It can look like this:
 
 ```
 /migrations/
    - VersionZZZ.php
 /migrations/2015/
    - VersionYYY.php
 /migrations/basic-data
    - VersionXXXX.php
```


### Injected migrations

```php
namespace Migrations;


final class Version20140801152432 extends AbstractMigration
{

	/**
	 * @inject
	 * @var Doctrine\ORM\EntityManagerInterface
	 */
	public $entityManager;


	public function up(Schema $schema)
	{
		// ...
	}

	// ...

}
```
