# sfResquePlugin

This is just a very simple wrapper around the already excellent [php-resque](http://github.com/chrisboulton/php-resque) library.

The key differences between php-resque and this plugin is the configuration is handled by Symfony's YML structure, database initialization for jobs, and a symfony task to start a worker.

## Requirements

* Symfony 1.4 (may work in others, only tested on 1.4)
* Redis

## Initial Setup

Just stick the plugin in the right place...

    git submodule add git://github.com/devpips/sfResquePlugin.git plugins/sfResquePlugin
    git submodule update --init --recursive

...and then enable it in `config/ProjectConfiguration.class.php`.

    public function setup()
    {
      // ...
      $this->enablePlugin('sfResquePlugin');
      // ...
    }

## Configuration

It's fairly simple. Create a file `config/resque.yml` and set your options.

    prod:
      database: 0
      server:
        - 10.0.0.9:6379
        - 10.0.0.8:6379
    all:
      database: 1
      server:   127.0.0.1:6379

`database` option is used to select what redis database is going to be used by each environment.

Along with `php-resque`, you can cluster your Redis servers or just supply a single server. In the above example, `prod` is clustered while all other environments will connect to the local address.

## Usage

Use it just like [php-resque](http://github.com/chrisboulton/php-resque) and [resque](https://github.com/defunkt/resque). Read their documentation.

    Resque::enqueue('default', 'My_Job', array('job_param' => true));

It'd be best to stick your jobs in a new folder called `/lib/job`. That just makes the most sense.

### Running a Worker

You can setup and run a worker as simply as this...

    ./symfony resque:worker default

This will setup a worker to work on the default queue. There are some additional options if you wish...

    ./symfony resque:worker '*' --env=dev --connection=doctrine --interval=5 --count=1 --verbose --vverbose

This will run all queues in the `dev` environment using the `doctrine` connection (`propel` will work also, but `doctrine` is default). The interval between checking for new jobs is 5 seconds with only 1 child worker.

> *Note:* To spawn multiple children, you must have the `pcntl` extension installed for PHP.

### A Sample Job

Let's create a sample job to show you how to use sfResque. Maybe we want to make a user explode into a bunch of tiny pieces. We create this file and save it to `/lib/job/Job_ExplodeUser.class.php`.

    <?php
    
    class Job_ExplodeUser
    {
      public function perform()
      {
        $user = UserTable::getInstance()->find($this->args['user_id']);
        
        if(!$user)
          throw new Exception('No such user found for id `'.$this->args['user_id'].'`');

        $user->explode();
        $user->save();
      }
    }

> *Note:* `sfResque` and `Resque` are synonymous. `sfResque` provides some additional features described below.

Now we need to enqueue the job at some point. Maybe when the user gets shot by the angry elephant squadron from space, whatever your reason...

    <?php
    
    class User extends BaseUser
    {
      // ...
      
      public function postDelete($event)
      {
        sfResque::enqueue('user', 'Job_ExplodeUser', array('user_id' => $this->id));
      }
      
      public function explode()
      {
        // KA BOOM BOOM POW!
      }
      
      // ...
    }

Once a worker is free, the job will be executed and the user will explode. Poor guy.

It is important to note that your *worker* works on a database. So you may enqueue a job on `connection_a` but your worker is running on `connection_b` (as defined when you create your worker). If your resource is on `connection_a`, it won't be found when doing a lookup when connected to `connection_b`. Be wary of that.

### Testing

You, of course, want to test your jobs. And test your jobs execute when they should. Let's test our `Job_ExplodeUser` job.

Create a test file in `/test/unit/job/Job_ExplodeUserTest.php`.

    <?php
    
    require_once dirname(__FILE__).'/../../bootstrap/Doctrine.php';
    
    // we need this line to have access to some sfResque test helpers
    require_once(sfConfig::get('sf_plugins_dir').'/sfResquePlugin/lib/test/bootstrap.php');

    $t = new lime_test(2);
    
    // should perform as expected
    
      $job = new Job_ExplodeUser();
      $job->args = array('user_id' => 5);
      
      // check that whatever happens after a user explodes actually happened
      
      $t->todo('->perform() works by itself and doesn\'t fatally crash in a ball of fire which would kill our worker');
      
    // should be run when a user is deleted
    
      $user = new User();
      $user->save();
      
      $user->delete();
      
      run_resque();
      
      // check that whatever happens after a user explodes actually happened
      
      $t->todo('a user exploded after the user was deleted');

And there you have it. A job fairly well tested.

## Additional Features

This may or may not be totally unnecessary, but if you want to track whether or not a job is in a queue, you can with sfResque.

    $args = array('job_param' => true);
    sfResque::enqueue('default', 'My_Job', $args);
    
    var_dump( sfResque::in_queue('default', 'My_Job', $args) ); // => true

For this to work, you must use `sfResque` rather than `Resque` as it uses some additional keys to track jobs in queues.

## Questions or Concerns

Feel free to open an issue here, fork this project, or do whatever you want with this plugin.
