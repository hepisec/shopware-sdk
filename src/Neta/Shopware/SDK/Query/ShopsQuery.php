<?php

namespace Neta\Shopware\SDK\Query;

use Neta\Shopware\SDK\Util\Constants;

class ShopsQuery extends Base
{
    /**
     * @var array
     */
    protected $methodsAllowed = [
        Constants::METHOD_CREATE,
        Constants::METHOD_GET,
        Constants::METHOD_GET_BATCH,
        Constants::METHOD_UPDATE,
        Constants::METHOD_DELETE,
    ];

    /**
     * @return mixed
     */
    protected function getClass()
    {
        return 'Neta\\Shopware\\SDK\\Entity\\Shop';
    }

    /**
     * @return string
     */
    protected function getQueryPath()
    {
        return 'shops';
    }
}
