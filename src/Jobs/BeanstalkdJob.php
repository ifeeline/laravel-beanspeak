<?php
namespace Ifeeline\Beanspeak\Jobs;

use Illuminate\Queue\Jobs\Job;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Container\Container;

class BeanstalkdJob extends Job implements JobContract
{
    /**
     * The Beanspeak\Client instance.
     *
     * @var \Beanspeak\Client
     */
    protected $beanspeak;

    /**
     * The Beanspeak\Job job instance.
     *
     * @var \Beanspeak\Job
     */
    protected $job;

    /**
     * Create a new job instance.
     *
     * @param  \Illuminate\Container\Container  $container
     * @param  \Beanspeak\Client  $beanspeak
     * @param  \Beanspeak\Job  $job
     * @param  string  $queue
     * @return void
     */
    public function __construct(Container $container,
                                \Beanspeak\Client $beanspeak,
                                \Beanspeak\Job $job,
                                $queue)
    {
        $this->job = $job;
        $this->queue = $queue;
        $this->container = $container;
        $this->beanspeak = $beanspeak;
    }

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire()
    {
        $this->resolveAndFire(json_decode($this->getRawBody(), true));
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->job->getBody();
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();

        $this->job->delete();
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int   $delay
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);

        $priority = 1024;

        $this->job->release($priority, $delay);
    }

    /**
     * Bury the job in the queue.
     *
     * @return void
     */
    public function bury()
    {
        parent::release();

        $this->job->bury();
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        $stats = $this->job->stats();

		$reserves = 0;
		if (!empty($stats['reserves'])) {
			$reserves = (int) $stats['reserves'];
		}

        return $reserves;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->job->getId();
    }

    /**
     * Get the IoC container instance.
     *
     * @return \Illuminate\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get the underlying Beanspeak\Client instance.
     *
     * @return \Beanspeak\Client
     */
    public function getBeanspeak()
    {
        return $this->beanspeak;
    }

    /**
     * Get the underlying Beanspeak\Job job.
     *
     * @return \Pheanstalk\Job
     */
    public function getPheanstalkJob()
    {
        return $this->job;
    }
}