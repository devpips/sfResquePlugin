<?php

/**
 * resque:worker
 *
 * @package     sfResquePlugin
 * @subpackage  config
 * @uses        sfPluginConfiguration
 * @author      Stephen Craton <scraton@gmail.com>
 * @license     The MIT License
 * @version     SVN: $Id$
 */
class resqueWorkerTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('queues', sfCommandArgument::REQUIRED, 'The queues you want to be processed by this worker')
    ));
    
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      
      new sfCommandOption('interval', null, sfCommandOption::PARAMETER_OPTIONAL, '', 5),
      new sfCommandOption('count', null, sfCommandOption::PARAMETER_OPTIONAL, 'The number of workers to spawn', 1),
      
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_NONE, 'Show more output'),
      new sfCommandOption('vverbose', null, sfCommandOption::PARAMETER_NONE, 'Show more even more output'),
    ));
    
    $this->namespace        = 'resque';
    $this->name             = 'worker';
    $this->briefDescription = 'Sets up a worker to do Resque jobs';
    $this->detailedDescription = <<<EOF
The [resque:worker|INFO] task sets up a worker to do resque jobs.
Call it with:

  [php symfony resque:worker|INFO]
EOF;
  }
  
  private $logLevel = 0;

  protected function execute($arguments = array(), $options = array())
  {
    $this->logLevel = ($options['verbose']) ? Resque_Worker::LOG_NORMAL : 0;
    $this->logLevel = ($options['vverbose']) ? Resque_Worker::LOG_VERBOSE : $this->logLevel;
    
    $queues = explode(',', $arguments['queues']);
    
    if($options['count'] == 1) {
      $worker = $this->createWorker($queues, $options);
	  $this->logSection('worker', 'Starting worker '.$worker);
	  $worker->work($options['interval']);
    } else if($options['count'] > 1) {
      if(!function_exists('pcntl_fork'))
        throw new Exception('PHP installation lakcs PCNTL. Recompile with `--enable-pcntl` configuration option.');
      
      for($i = 0; $i < $options['count']; ++$i) {
        $pid = pcntl_fork();
        if($pid == -1) {
          $this->logBlock('Could not fork worker '.$i, 'ERROR');
          return;
        }
        // Child, start the worker
        else if(!$pid) {
          $worker = $this->createWorker($queues, $options);
          $this->logSection('worker', 'Starting worker '.$worker);
    	  $worker->work($options['interval']);
          break;
        }
	  }
    }
  }
  
  protected function createWorker($queues, $options = array())
  {
    $worker = new sfResqueWorker($this->configuration, $queues, $options);
	$worker->logLevel = $this->logLevel;
	return $worker;
  }
}
