<?php

/**
 * sfResqueWorker
 *
 * @package     sfResquePlugin
 * @subpackage  config
 * @uses        sfPluginConfiguration
 * @author      Stephen Craton <scraton@gmail.com>
 * @license     The MIT License
 * @version     SVN: $Id$
 */
class sfResqueWorker extends Resque_Worker
{

  protected
    $configuration = null,
    $connection    = null,
    $options       = array();

  public function __construct(sfProjectConfiguration $configuration, $queues, $options = array())
  {
    $this->configuration = $configuration;
    $this->options       = $options;
    
    parent::__construct($queues);
  }
  
  /**
   * @see Resque_Worker::perform()
   */
  public function perform(Resque_Job $job)
  {
    if(isset($this->options['connection'])) {
      $databaseManager  = new sfDatabaseManager($this->configuration);
      $this->connection = $databaseManager->getDatabase($this->options['connection'])->getConnection();
    }
    
    parent::perform($job);
    
    if($this->connection)
        $databaseManager->shutdown();
  }
  
  public function doneWorking()
  {
    $job = $this->job();
    sfResque::remove_track_queue($job['queue'], sfResque::tokenize($job['payload']['class'], $job['payload']['args']));
    return parent::doneWorking();
  }
    
}