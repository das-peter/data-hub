# Events

DataHub GraphQL events are based on symfony event dispatcher, which are triggered during execution of Query and Mutation requests.
[Read more](https://github.com/pimcore/pimcore/blob/master/doc/Development_Documentation/20_Extending_Pimcore/11_Event_API_and_Event_Manager.md) about events on Pimcore documentation.

All DataHub events are defined as a constant on component classes:
- [Query](https://github.com/pimcore/data-hub/blob/master/src/Event/GraphQL/QueryEvents.php)
- [Mutation](https://github.com/pimcore/data-hub/blob/master/src/Event/GraphQL/MutationEvents.php)
- [Executor](https://github.com/pimcore/data-hub/blob/master/src/Event/GraphQL/ExecutorEvents.php)
- [Listing](https://github.com/pimcore/data-hub/blob/master/src/Event/GraphQL/ListingEvents.php)

## Event Listener examples

Add configuration in your `app/config/services.yml`:
```yml
services:
    AppBundle\EventListener\GraphQlListener:
        tags:
            - { name: kernel.event_listener}
```

Add Listener class `src/AppBundle/EventListener/GraphQlListener`

#### Example 1: Query & Mutation execution
```php
<?php

namespace AppBundle\EventListener;
use Pimcore\Bundle\DataHubBundle\Event\GraphQL\ExecutorEvents;
use Pimcore\Bundle\DataHubBundle\Event\GraphQL\Model\ExecutorEvent;
use Pimcore\Bundle\DataHubBundle\Event\GraphQL\Model\ExecutorResultEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GraphQlListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ExecutorEvents::PRE_EXECUTE => 'onPreExecute', //Pre execute on Query & Mutation
            ExecutorEvents::POST_EXECUTE => 'onPostExecute' //Post execute on Query & Mutation
        ];
    }

    /**
     * @param ExecutorEvent $event
     */
    public function onPreExecute(ExecutorEvent $event)
    {
        // do something with the query or schema
        $query = $event->getQuery();
        $schema = $event->getSchema();
    }

    /**
     * @param ExecutorResultEvent $event
     */
    public function onPostExecute(ExecutorResultEvent $event)
    {
        // do something with output result
        $result = $event->getResult();
    }
}

```

#### Example 2: Bypass workspace permissions with Query/Mutation build events
```php
<?php

namespace AppBundle\EventListener;
use Pimcore\Bundle\DataHubBundle\Event\GraphQL\MutationEvents;
use Pimcore\Bundle\DataHubBundle\Event\GraphQL\Model\MutationTypeEvent;
use Pimcore\Bundle\DataHubBundle\Event\GraphQL\Model\QueryTypeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GraphqlListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            MutationEvents::PRE_BUILD => 'onMutationPreBuild',
            QueryEvents::PRE_BUILD => 'onQueryPreBuild'
        ];
    }

    /**
     * @param MutationTypeEvent $event
     */
    public function onMutationPreBuild(MutationTypeEvent $event)
    {
        $mutationType = $event->getMutationType();
        $mutationType->setOmitPermissionCheck(true); //omit permission check for mutations
    }

    /**
     * @param QueryTypeEvent $event
     */
    public function onQueryPreBuild(QueryTypeEvent $event)
    {
        $queryType = $event->getQueryType();
        $queryType->setOmitPermissionCheck(true); //omit permission check for queries
    }
}

```

#### Example 3: Add custom arguments to existing types
```php
<?php

namespace AppBundle\EventListener;
use Pimcore\Bundle\DataHubBundle\Event\GraphQL\MutationEvents;
use Pimcore\Bundle\DataHubBundle\Event\GraphQL\Model\MutationTypeEvent;
use Pimcore\Bundle\DataHubBundle\Event\GraphQL\Model\QueryTypeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use GraphQL\Type\Definition\Type;

class GraphqlListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            MutationEvents::POST_BUILD => 'onMutationPostBuild',
            QueryEvents::POST_BUILD => 'onQueryPostBuild'
        ];
    }

    /**
     * @param MutationTypeEvent $event
     */
    public function onMutationPostBuild(MutationTypeEvent $event)
    {
        $config = $event->getConfig();
        $config['fields']['getProductListing']['args']['foo'] = [
            'type'  => Type::boolean()
        ];
        $event->setConfig($config);
    }

    /**
     * @param QueryTypeEvent $event
     */
    public function onQueryPostBuild(QueryTypeEvent $event)
    {
        $config = $event->getConfig();
        $config['fields']['getProductListing']['args']['foo'] = [
            'type'  => Type::boolean()
        ];
        $event->setConfig($config);
    }
}

```

#### Example 4: Add custom query conditions to object listing

- For global SQL conditions also [General Settings](https://github.com/pimcore/data-hub/blob/master/doc/graphl/General.md#general-settings)
- For simple filter conditions also see [Filtering](https://github.com/pimcore/data-hub/blob/master/doc/graphl/Filtering.md#request)

```php
<?php

namespace AppBundle\EventListener;
use Pimcore\Bundle\DataHubBundle\Event\GraphQL\ListingEvents;
use Pimcore\Bundle\DataHubBundle\Event\GraphQL\Model\ListingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GraphqlListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ListingEvents::PRE_LOAD => 'onListingPreLoad',
        ];
    }

    /**
     * @param ListingEvent $event
     */
    public function onListingPreLoad(ListingEvent $event)
    {
        $listing = $event->getListing();

        if ($listing->getClassName() === 'Product') {
          $listing->setCondition('(o_parentId IN (SELECT o_id FROM objects WHERE o_type=\'object\') AND o_type = \'variant\')');
        }

        $event->setListing($listing);
    }
}

```
