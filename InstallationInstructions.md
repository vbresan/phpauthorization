# 1. Extract the zip file #

# 2. Set database connection parameters #

PHP Authorization service uses database to store data. All most frequently used databases are supported (MySQL, PostgreSQL, SQLite). Database is not accessed directly but through EZPDO, lightweight data persistence library that comes with the product. It is not necessary for you to know anything about the mentioned library, you just need to edit the following two configuration files:

  * `./config.xml`
  * `./tools/config.xml`

In both files look for `<default_dsn>` tags and uncomment the one depending on the database type that you use. Put the right username, password and database name in connection string. Please note that the database already has to be created and user has to be given the right credentials, including the one necessary for table creation. The following MySQL statements might be helpful in that task:

```
CREATE DATABASE databasename DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX ON databasename . * TO 'username'@'localhost';

SET PASSWORD FOR 'username'@'localhost'=PASSWORD('password');
```

Syntax might differ slightly if you are using database other than MySQL.

# 3. Call `_buildTables.php` script #

If you have set everything from the previous step correctly, this script will create two tables named `ClientData` and `ServiceDefaults`. Also in local file system, folder `compiled` and file `ezpdo.log` will be created. After you are done with this step, you can delete `_buildTables.php` script.

# 4. Set up cron to run `./tools/removeExpiredClients.php` once a day #

Instructions for this step are different on each hosting environment. If you don't know how to set up scheduler to run the script in regular intervals, the best would be to ask your hosting provider for help.

**That should be it!** For further instructions take a look at the UsageExamples.