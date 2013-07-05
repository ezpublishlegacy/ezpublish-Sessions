Ez Publish 5.x Database Session Handler
======

## Instructions

1. Add the bundle to your `src` directory
2. Add the following to `config.yml`
````
framework:
    session:
        handler_id:     session.handler.pdo
        cookie_lifetime: 0
        # cookie_lifetime in seconds

parameters:
    pdo.db_options:
        db_table:    pdo.db_options
        db_table:    sessions
        db_id_col:   sess_id
        db_data_col: sess_value
        db_time_col: sess_time

services:
    pdo:
        class: PDO
        arguments:
            dsn:      "mysql:dbname=<database_name>"
            user:     <username>
            password: <password>

    session.handler.pdo:
        class:     Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler
        arguments: ["@pdo", "%pdo.db_options%"]

````
3. The timeout will be read from site.ini [Sessions] SessionTimout=100
4. Add the following cronjob to run every 1/2 minutes
````
* * * * * cd /var/www/www.mysite.com && php ezpublish/console session:garbage_collector >/dev/null
````
5. Test the timeout by running `php ezpublish/console session:garbage_collector` manually from the console