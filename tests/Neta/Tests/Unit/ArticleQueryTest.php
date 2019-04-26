<?php

namespace Neta\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Neta\Shopware\SDK\Entity\Article;
use Neta\Shopware\SDK\Util\Constants;
use Neta\Shopware\SDK\Query\ArticleQuery;
use Neta\Shopware\SDK\Entity\ArticleDetail;
use Neta\Shopware\SDK\Entity\ArticleAttribute;

/**
 * Copyright 2016 LeadCommerce.
 *
 * @author    Alexander Mahrt <amahrt@leadcommerce.de>
 * @copyright 2016 LeadCommerce <amahrt@leadcommerce.de>
 */
class ArticleQueryTest extends BaseTest
{
    /**
     * @var \Neta\Shopware\SDK\Query\ArticleQuery
     */
    private $query;

    public function testFindAll()
    {
        $this->mockHandler = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/files/get_articles.json')),
        ]);

        $entities = $this->getQuery()->findAll();
        $this->assertCount(2, $entities);

        foreach ($entities as $entity) {
            $this->assertInstanceOf(Article::class, $entity);
        }

        /** @var \Neta\Shopware\SDK\Entity\Article $article */
        $article = $entities[1];

        $this->assertEquals(2, $article->getId());
        $this->assertEquals('Glastisch rund', $article->getName());
        $this->assertEquals(2, $article->getMainDetailId());
    }

    public function testFindByParams()
    {
        $this->mockHandler = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/files/get_articles.json')),
        ]);

        $term = 'foo';

        $entities = $this->getQuery()->findByParams(
            [
                'limit'  => 10,
                'start'  => 20,
                'sort'   => [
                    [
                        'property'  => 'name',
                        'direction' => Constants::ORDER_ASC,
                    ],
                ],
                'filter' => [
                    [
                        'property'   => 'name',
                        'expression' => 'LIKE',
                        'value'      => '%' . $term . '%',
                    ],
                    [
                        'operator'   => 'AND',
                        'property'   => 'number',
                        'expression' => '>',
                        'value'      => '500',
                    ],
                ],
            ]);

        $this->assertCount(2, $entities);

        foreach ($entities as $entity) {
            $this->assertInstanceOf(Article::class, $entity);
        }

        /** @var \Neta\Shopware\SDK\Entity\Article $article */
        $article = $entities[1];

        $this->assertEquals(2, $article->getId());
        $this->assertEquals('Glastisch rund', $article->getName());
        $this->assertEquals(2, $article->getMainDetailId());

        $lastRequest = $this->mockHandler->getLastRequest();

        $query = urldecode($lastRequest->getUri()->getQuery());

        $this->assertContains('limit=10', $query);
        $this->assertContains('start=20', $query);
        $this->assertContains('sort[0][property]=name', $query);
        $this->assertContains('sort[0][direction]=' . Constants::ORDER_ASC, $query);
        $this->assertContains('filter[0][property]=name', $query);
        $this->assertContains('filter[0][expression]=LIKE', $query);
        $this->assertContains('filter[0][value]=%foo%', $query);
        $this->assertContains('filter[1][operator]=AND', $query);
        $this->assertContains('filter[1][property]=number', $query);
        $this->assertContains('filter[1][expression]=>', $query);
        $this->assertContains('filter[1][value]=500', $query);
    }

    /**
     * Gets the query to test.
     *
     * @return \Neta\Shopware\SDK\Query\ArticleQuery
     */
    public function getQuery()
    {
        if (! $this->query) {
            $this->query = new ArticleQuery($this->getMockClient());
        }

        return $this->query;
    }

    public function testFindOne()
    {
        $this->mockHandler = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/files/get_article.json')),
        ]);

        /** @var Article $entity */
        $entity = $this->getQuery()->findOne(1);
        $this->assertInstanceOf(Article::class, $entity);

        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('Glastisch quadratisch', $entity->getName());
        $this->assertEquals(1, $entity->getMainDetailId());
    }

    public function testCreate()
    {
        $this->mockHandler = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/files/get_article.json')),
        ]);

        $attributes = json_decode(file_get_contents(__DIR__ . '/files/create_article.json'), true);

        $entity = new Article();
        $entity->setEntityAttributes($attributes['data']);

        $response = $this->getQuery()->create($entity);

        $entity->setId($response->getId());

        $this->assertEquals($entity->getArrayCopy(), $response->getArrayCopy());
    }

    public function testUpdate()
    {
        $this->mockHandler = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/files/updated_article.json')),
        ]);

        $attributes = json_decode(file_get_contents(__DIR__ . '/files/update_article.json'), true);

        $entity = new Article();
        $entity->setEntityAttributes($attributes);
        /** @var Article $updatedEntity */
        $updatedEntity = $this->getQuery()->update($entity);
        $this->assertInstanceOf(Article::class, $updatedEntity);
        $this->assertEquals($entity->isActive(), $updatedEntity->isActive());
        $this->assertEquals($entity->getName(), $updatedEntity->getName());
    }

    public function testUpdateBatch()
    {
        $this->mockHandler = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/files/updated_articles.json')),
        ]);

        $attributes = json_decode(file_get_contents(__DIR__ . '/files/update_articles.json'), true);

        $entities = [];
        foreach ($attributes as $attribute) {
            $entity = new Article();
            $entity->setEntityAttributes($attribute);
            $entities[] = $entity;
        }

        /** @var Article $updatedEntities */
        $updatedEntities = $this->getQuery()->updateBatch($entities);
        $this->assertCount(2, $updatedEntities);

        /** @var Article $updatedEntity */
        $updatedEntity = $updatedEntities[0];
        $this->assertInstanceOf(Article::class, $updatedEntity);
        $this->assertEquals($entities[0]->isActive(), $updatedEntity->isActive());
        $this->assertEquals($entities[0]->getName(), $updatedEntity->getName());

        /** @var Article $updatedEntity */
        $updatedEntity = $updatedEntities[1];
        $this->assertInstanceOf(Article::class, $updatedEntity);
        $this->assertEquals($entities[1]->isActive(), $updatedEntity->isActive());
        $this->assertEquals($entities[1]->getName(), $updatedEntity->getName());
    }

    public function testDelete()
    {
        $this->mockHandler = new MockHandler([
            new Response(200, [], '{"success":true}'),
        ]);

        $entities = $this->getQuery()->delete(1);

        $this->assertCount(0, $entities);
    }

    public function testDeleteBatch()
    {
        $jsonResponse = [
            'success' => true,
            'data'    => [
                [
                    'success'   => true,
                    'operation' => 'delete',
                    'data'      => [
                        'id'           => 1,
                        'mainDetailId' => 1,
                        'taxId'        => 1,
                    ],
                ],
                [
                    'success'   => true,
                    'operation' => 'delete',
                    'data'      => [
                        'id'           => 2,
                        'mainDetailId' => 2,
                        'taxId'        => 1,
                    ],
                ],
            ],
        ];

        $this->mockHandler = new MockHandler([
            new Response(200, [], json_encode($jsonResponse)),
        ]);

        $entities = $this->getQuery()->deleteBatch([1, 2]);

        $this->assertCount(2, $entities);
    }

    /**
     * @test
     */
    public function it_handles_nested_attribute_object()
    {
        $this->mockHandler = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/files/get_article.json')),
        ]);

        /** @var Article $entity */
        $entity = $this->getQuery()->findOne(1);
        $this->assertInstanceOf(Article::class, $entity);
        $this->assertInstanceOf(ArticleAttribute::class, $entity->getAttribute());
    }

    /**
     * @test
     */
    public function it_handles_nested_main_detail_object()
    {
        $article = json_decode(file_get_contents(__DIR__ . '/files/get_article.json'), true);
        $article['data']['mainDetail'] = ['id' => 'mainDetailID'];

        $this->mockHandler = new MockHandler([
            new Response(200, [], json_encode($article)),
        ]);

        /** @var Article $entity */
        $entity = $this->getQuery()->findOne(1);
        $this->assertInstanceOf(Article::class, $entity);
        $this->assertInstanceOf(ArticleDetail::class, $entity->getMainDetail());
    }

    /**
     * @test
     */
    public function it_handles_nested_main_detail_attribute_object()
    {
        $article = json_decode(file_get_contents(__DIR__ . '/files/get_article.json'), true);
        $article['data']['mainDetail'] = [
            'id'        => 'mainDetailID',
            'attribute' => [
                'id' => 'mainDetailAttributeId',
            ],
        ];

        $this->mockHandler = new MockHandler([
            new Response(200, [], json_encode($article)),
        ]);

        /** @var Article $entity */
        $entity = $this->getQuery()->findOne(1);
        $this->assertInstanceOf(Article::class, $entity);
        $this->assertInstanceOf(ArticleDetail::class, $entity->getMainDetail());
        $this->assertInstanceOf(ArticleAttribute::class, $entity->getMainDetail()->getAttribute());

    }
}
