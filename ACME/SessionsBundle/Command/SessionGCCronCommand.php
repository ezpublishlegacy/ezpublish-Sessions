<?php

namespace ACME\SessionsBundle;

//set up class for console use
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

//get data from ini
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Exception\ParameterNotFoundException;
use eZINI;

/**
 * Class SessionGCCronCommand
 *
 * Cronjob used to clear expired sessions from the database.
 * Extending ContainerAwareCommand gives access to ezp legacy api calls.
 *
 * Example CronJob to run every minute:
 * "* * * * * cd /var/www/www.mysite.com && php ezpublish/console session:garbage_collector >/dev/null"
 *
 * @package ACME\SessionsBundle
 * @since 5.1
 * @author Me
 */
class SessionGCCronCommand extends ContainerAwareCommand
{
    public function __construct(){
        parent::__construct();
    }

    protected function configure(){
        $this
            ->setName('session:garbage_collector')
            ->setDescription('Clear Expired Database Sessions');
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $configResolver = $this->getContainer()->get( 'ezpublish.config.resolver' );
        $databaseSettings = $configResolver->getParameter( 'database' );

        $databaseTableOptions = $this->getContainer()->getParameter( 'pdo.db_options' );
        $sessionIdleTimeout = $this->_getIdleTimeoutValue( );

        try{
            $connectionString = sprintf( "%s:host=%s;dbname=%s", $databaseSettings['type'], $databaseSettings['server'], $databaseSettings['database_name'] );
            $pdo = new \PDO( $connectionString, $databaseSettings['user'], $databaseSettings['password'] );

            $session = new PdoSessionHandler( $pdo, $databaseTableOptions );
            $session->gc( $sessionIdleTimeout );
        }
        catch( PDOException $e ){
            printf( "Session GC PDO Connection Error: %s", $e->getMessage() );
        }
    }

    /**
     * Uses legacy resolver to pull the session timeout from
     * the legacy site.ini
     *
     * @see https://confluence.ez.no/display/EZP51/Legacy+configuration
     * @return mixed  The value for session timeout from site.ini (int). False on failure.
     */
    private function _getIdleTimeoutValue(){
        $legacyResolver = $this->getContainer()->get( 'ezpublish_legacy.config.resolver' );
        $sessionIdleTimeout = $legacyResolver->getParameter( 'Session.SessionTimeout' );
        return $sessionIdleTimeout;
    }
}