<?php

function run_resque($queues = '*', $options = array(), $logLevel = 0) {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'test', true);

    $worker = new sfResqueWorker($configuration, $queues, $options);
    $worker->logLevel = $logLevel;

    while(($job = $worker->reserve())) {
        $worker->workingOn($job);
        $worker->perform($job);
        $worker->doneWorking();
    }
}