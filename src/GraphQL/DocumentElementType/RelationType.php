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

namespace Pimcore\Bundle\DataHubBundle\GraphQL\DocumentElementType;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Pimcore\Bundle\DataHubBundle\GraphQL\ElementDescriptor;
use Pimcore\Bundle\DataHubBundle\GraphQL\Service;
use Pimcore\Model\Document\Tag\Relation;

class RelationType extends ObjectType
{
    protected static $instance;

    /**
     * @param Service $graphQlService
     * @return RelationType
     * @throws \Exception
     */
    public static function getInstance(Service $graphQlService)
    {

        if (!self::$instance) {

            $anyTargetType = $graphQlService->buildGeneralType("anytarget");

            $config =
                [
                    'name' => 'document_tagRelation',
                    'fields' => [
                        '__tagType' => [
                            'type' => Type::string(),
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                                if ($value instanceof Relation) {
                                    return $value->getType();
                                }
                            }
                        ],
                        '__tagName' => [
                            'type' => Type::string(),
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                                if ($value instanceof Relation) {
                                    return $value->getName();
                                }
                            }
                        ],
                        'id' => [
                            'type' => Type::int(),
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                                if ($value instanceof Relation) {
                                    return $value->getId();
                                }
                            }
                        ],
                        'type' => [
                            'type' => Type::string(),
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                                if ($value instanceof Relation) {
                                    return $value->getType();
                                }
                            }
                        ],
                        'subtype' => [
                            'type' => Type::string(),
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                                if ($value instanceof Relation) {
                                    return $value->getSubtype();
                                }
                            }
                        ],
                        'relation' => [
                            'type' => $anyTargetType,
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                                if ($value instanceof Relation) {
                                    $target = $value->getElement();
                                    if ($target) {
                                        $desc = new ElementDescriptor($target);
                                        return $desc;
                                    }
                                }
                            }
                        ]
                    ]
                ];
            self::$instance = new static($config);
        }

        return self::$instance;
    }


}
