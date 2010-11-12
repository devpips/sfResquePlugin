<?php

/**
 * sfResque
 *
 * @package     sfResquePlugin
 * @subpackage  config
 * @uses        sfPluginConfiguration
 * @author      Stephen Craton <scraton@gmail.com>
 * @license     The MIT License
 * @version     SVN: $Id$
 */
class sfResque extends Resque
{
    
    protected static function tokenize($class, $args = null) {
        return md5($class.json_encode($args));
    }
    
    public static function in_queue($queue, $class, $args = null) {
        return (self::redis()->sismember('sfresque:queue:'.$queue, self::tokenize($class, $args)) == '1');
    }
    
    /**
     * @see Resque::enqueue
     */
    public static function enqueue($queue, $class, $args = null, $trackStatus = false) {
        var_dump($class, $args);
        self::redis()->sadd('sfresque:queue:'.$queue, self::tokenize($class, $args));
        return parent::enqueue($queue, $class, $args, $trackStatus);
    }
    
    public static function reserve($queue) {
        $job = parent::reserve($queue);
        var_dump($job->payload['class'], $job->payload['args']);
        self::redis()->srem('sfresque:queue:'.$queue, self::tokenize($job->payload['class'], $job->payload['args']));
        return $job;
    }
    
}