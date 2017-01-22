<?php
namespace Ifeeline\Beanspeak;

use Illuminate\Queue\QueueServiceProvider as SystemQueueServiceProvider;
use Ifeeline\Beanspeak\Connectors\BeanstalkdConnector;

class QueueServiceProvider extends SystemQueueServiceProvider
{
    protected function registerBeanstalkdConnector($manager)
    {
        if (extension_loaded('beanspeak')) {
            $manager->addConnector('beanstalkd', function () {
                return new BeanstalkdConnector;
            });
        } else {
            parent::registerBeanstalkdConnector($manager);
        }  
    }
}