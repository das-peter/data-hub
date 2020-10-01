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

namespace Pimcore\Bundle\DataHubBundle\GraphQL\DocumentElementInputProcessor;


use GraphQL\Type\Definition\ResolveInfo;
use Pimcore\Bundle\DataHubBundle\WorkspaceHelper;
use Pimcore\Model\Asset;
use Pimcore\Model\Document\PageSnippet;

class Image extends Base
{

    /**
     * @param PageSnippet $document
     * @param mixed $newValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     */
    public function process($document, $newValue, $args, $context, ResolveInfo $info)
    {

        $dataFromEditMode = [];

        $assetId = $newValue['id'];
        $asset = Asset::getById($assetId);
        if (WorkspaceHelper::checkPermission($asset, 'read')) {
            $dataFromEditMode['id'] = $assetId;
        }

        if (isset($newValue['alt'])) {
            $dataFromEditMode['alt'] = $newValue['alt'];
        }

        $tagType = $newValue['_tagType'];

        $tag = $this->tagLoader->build($tagType);

        $tagName = $newValue['_tagName'];
        $tag->setName($tagName);


        $tag->setDataFromEditmode($dataFromEditMode);

        if (method_exists($document, 'setEditable')) {
            $document->setEditable($tagName, $tag);
        } else {
            // this one is deprecated and will be removed with pimcore 7
            $document->setElement($tagName, $tag);
        }
    }

}

