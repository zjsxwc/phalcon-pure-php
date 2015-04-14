<?php
namespace Phalcon\Events
{

    interface EventsAwareInterface
    {

        public function setEventsManager(\Phalcon\Events\ManagerInterface $eventsManager);

        /**
         * @return \Phalcon\Events\ManagerInterface
         */
        public function getEventsManager();
    }
}
