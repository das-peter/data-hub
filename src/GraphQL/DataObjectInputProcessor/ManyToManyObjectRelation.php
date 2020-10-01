<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Bundle\DataHubBundle\GraphQL\DataObjectInputProcessor;

use GraphQL\Type\Definition\ResolveInfo;
use Pimcore\Bundle\DataHubBundle\GraphQL\Exception\ClientSafeException;
use Pimcore\Bundle\DataHubBundle\GraphQL\Service;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData;


class ManyToManyObjectRelation extends Base
{

    /**
     * @param Concrete|AbstractData $object
     * @param $newValue
     * @param array $args
     * @param array $context
     * @param ResolveInfo $info
     * @throws \Exception
     */
    public function process($object, $newValue, $args, $context, ResolveInfo $info)
    {
        $attribute = $this->getAttribute();
        Service::setValue($object, $attribute, function($container, $setter) use ($newValue) {
            $result = [];
            if (is_array($newValue)) {
                foreach ($newValue as $newValueItemKey => $newValueItemValue) {
                    if (isset($newValueItemValue["type"]) && $newValueItemValue["type"] !== 'object') {
                        throw new ClientSafeException("expected object type");
                    }

                    $element = \Pimcore\Model\Element\Service::getElementById('object', $newValueItemValue["id"]);
                    if ($element) {
                        $result[] = $element;
                    }
                }
            }

            return $container->$setter($result);
        });
    }
}

