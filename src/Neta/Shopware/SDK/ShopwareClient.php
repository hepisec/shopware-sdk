<?php

namespace Neta\Shopware\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use Neta\Shopware\SDK\Query\CacheQuery;
use Neta\Shopware\SDK\Query\MediaQuery;
use Neta\Shopware\SDK\Query\ShopsQuery;
use Neta\Shopware\SDK\Query\OrdersQuery;
use Neta\Shopware\SDK\Query\AddressQuery;
use Neta\Shopware\SDK\Query\ArticleQuery;
use Neta\Shopware\SDK\Query\VersionQuery;
use Neta\Shopware\SDK\Query\CustomerQuery;
use Neta\Shopware\SDK\Query\VariantsQuery;
use Neta\Shopware\SDK\Query\CountriesQuery;
use Neta\Shopware\SDK\Query\CategoriesQuery;
use Neta\Shopware\SDK\Query\TranslationsQuery;
use Neta\Shopware\SDK\Query\ManufacturersQuery;
use Neta\Shopware\SDK\Query\CustomerGroupsQuery;
use Neta\Shopware\SDK\Query\PropertyGroupsQuery;
use Neta\Shopware\SDK\Query\GenerateArticleImagesQuery;

/**
 * Class ShopwareClient.
 *
 * @author    Alexander Mahrt <amahrt@leadcommerce.de>
 * @copyright 2016 LeadCommerce <amahrt@leadcommerce.de>
 *
 * @method AddressQuery getAddressQuery()
 * @method ArticleQuery getArticleQuery()
 * @method CacheQuery getCacheQuery()
 * @method CategoriesQuery getCategoriesQuery()
 * @method CountriesQuery getCountriesQuery()
 * @method CustomerGroupsQuery getCustomerGroupsQuery()
 * @method CustomerQuery getCustomerQuery()
 * @method GenerateArticleImagesQuery getGenerateArticleImageQuery()
 * @method MediaQuery getMediaQuery()
 * @method ManufacturersQuery getManufacturersQuery()
 * @method OrdersQuery getOrdersQuery()
 * @method PropertyGroupsQuery getPropertyGroupsQuery()
 * @method ShopsQuery getShopsQuery()
 * @method TranslationsQuery getTranslationsQuery()
 * @method VariantsQuery getVariantsQuery()
 * @method VersionQuery getVersionQuery()
 */
class ShopwareClient
{
    const VERSION = '0.0.1';

    /**
     * @var string|null
     */
    protected $baseUrl;

    /**
     * @var string|null
     */
    protected $username;

    /**
     * @var string|null
     */
    protected $apiKey;

    /**
     * @var Client
     */
    protected $client;

    /**
     * ShopwareClient constructor.
     *
     * @param      $baseUrl
     * @param null $username
     * @param null $apiKey
     */
    public function __construct($baseUrl, $username = null, $apiKey = null, array $guzzleOptions = [])
    {
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->apiKey = $apiKey;
        $curlHandler = new CurlHandler();
        $handlerStack = HandlerStack::create($curlHandler);

        $guzzleOptions = array_merge($guzzleOptions, [
            'base_uri' => $this->baseUrl,
            'handler'  => $handlerStack,
        ]);
        $this->client = new Client($guzzleOptions);
    }

    /**
     * Does a request.
     *
     * @param        $uri
     * @param string $method
     * @param null   $body
     * @param array  $headers
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function request($uri, $method = 'GET', $body = null, $headers = [])
    {
        return $this->client->request($method, $uri, [
            'json'    => $body,
            'headers' => $headers,
            'auth'    => [
                $this->username,
                $this->apiKey,
                'digest',
            ],
        ]);
    }

    /**
     * Magically get the query classes.
     *
     * @param       $name
     * @param array $arguments
     *
     * @return bool
     */
    public function __call($name, $arguments = [])
    {
        if (! preg_match('/^get([a-z]+Query)$/i', $name, $matches)) {
            return false;
        }

        $className = __NAMESPACE__ . '\\Query\\' . $matches[1];

        if (! class_exists($className)) {
            return false;
        }

        return new $className($this);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     *
     * @return ShopwareClient
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }
}
