<?php

class resqueStatusTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('token', sfCommandArgument::REQUIRED, 'The token of the job you wish to track')
    ));
    
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));
    
    $this->namespace        = 'resque';
    $this->name             = 'status';
    $this->briefDescription = 'Tracks the status of a resque job';
    $this->detailedDescription = <<<EOF
The [resque:status|INFO] task tracks the status of a resque job
Call it with:

  [php symfony resque:status|INFO]
EOF;
  }
  
  private $logLevel = 0;

  protected function execute($arguments = array(), $options = array())
  {
    $status = new Resque_Job_Status($arguments['token']);
    if(!$status->isTracking()) {
      $this->logBlock("Resque is not tracking the status of this job.", 'ERROR');
      return;
    }
    
    $this->logSection('log', 'Tracking the status of `'.$arguments['token'].'`. Press [break] to stop.');
    $this->log("\n");

    while(true) {
      $this->logSection('log', 'Status: '.$status->get());
	  sleep(1);
    }
  }
}
