For run Energine PHP unit tests you need:
1) Create symlink "ln -s ../../tests/phpunit/config/system.config.phpunit.php ../../starter/htdocs/system.config.php".
2) Create database and fill it with data from ../../starter/sql files need to be run in order:
        - starter.structure.sql;
        - starter.routines.sql;
        - starter.data.sql.
3) Configure your config/system.config.phpunit.php according to energine install manual (https://github.com/energine-cmf/energine/wiki/Installation-guide)
4) Run "php ../../starter/htdocs/index.php setup"
5) now you can run tests using "phpunit -c config/energine.xml [TestName]"