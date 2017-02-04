<?php
namespace Ifeeline\Beanspeak;

use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Ifeeline\Beanspeak\Jobs\BeanstalkdJob;

class BeanstalkdQueue extends Queue implements QueueContract
{
    /**
     * The Beanspeak\Client instance.
     *
     * @var Beanspeak\Client
     */
    protected $beanspeak;

    /**
     * The name of the default tube.
     *
     * @var string
     */
    protected $default;

    /**
     * The "time to run" for all pushed jobs.
     *
     * @var int
     */
    protected $timeToRun;

    /**
     * Create a new Beanspeak\Client queue instance.
     *
     * @param  \Beanspeak\Client  $beanspeak
     * @param  string  $default
     * @param  int  $timeToRun
     * @return void
     */
    public function __construct(\Beanspeak\Client $beanspeak, $default, $timeToRun)
    {
        $this->default = $default;
        $this->timeToRun = $timeToRun;
        $this->beanspeak = $beanspeak;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string  $queue
     * @param  array   $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->beanspeak->useTube($this->getQueue($queue))->put(
            $payload, 1024, 0, $this->timeToRun
        );
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTime|int  $delay
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        $payload = $this->createPayload($job, $data);

        $beanspeak = $this->beanspeak->useTube($this->getQueue($queue));

        return $beanspeak->put($payload, 1024, $this->getSeconds($delay), $this->timeToRun);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        $job = $this->beanspeak->watchOnly($queue)->reserve();

        if ($job instanceof \Beanspeak\Job) {
            return new BeanstalkdJob($this->container, $this->beanspeak, $job, $queue);
        }
    }

    /**
     * Delete a message from the Beanstalk queue.
     *
     * @param  string  $queue
     * @param  string  $id
     * @return void
     */
    public function deleteMessage($queue, $id)
    {
        $job = $this->beanspeak->useTube($this->getQueue($queue))->peekJob($id);
        
        if ($job instanceof \Beanspeak\Job) {
            $job->delete($id);
        }
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null  $queue
     * @return string
     */
    public function getQueue($queue)
    {
        return $queue ?: $this->default;
    }

    /**
     * Get the underlying \Beanspeak\Client instance.
     *
     * @return \Beanspeak\Client
     */
    public function getBeanspeak()
    {
        return $this->beanspeak;
    }	
}