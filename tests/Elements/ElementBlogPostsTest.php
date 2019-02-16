<?php

namespace Dynamic\Elements\Blog\Elements\Tests;

use Dynamic\Elements\Blog\Elements\ElementBlogPosts;
use SilverStripe\Blog\Model\Blog;
use SilverStripe\Blog\Model\BlogPost;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

class ElementBlogPostsTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures.yml';

    /**
     *
     */
    public function testGetSummary()
    {
        $object = $this->objFromFixture(ElementBlogPosts::class, 'one');
        $count = $object->getPostsList()->count();
        $this->assertEquals(
            $object->getSummary(),
            _t(
                BlogPost::class . 'PLURALS',
                'A Blog Post|{count} Blog Posts',
                [ 'count' => $count ]
            )
        );
    }

    /**
     *
     */
    public function testGetType()
    {
        $object = $this->objFromFixture(ElementBlogPosts::class, 'one');
        $this->assertEquals($object->getType(), 'Blog Posts');
    }

    /**
     *
     */
    public function testGetCMSFields()
    {
        $object = $this->objFromFixture(ElementBlogPosts::class, 'one');
        $fields = $object->getCMSFields();
        $this->assertInstanceOf(FieldList::class, $fields);
        $this->assertNotNull($fields->dataFieldByName('BlogID'));
    }

    /**
     *
     */
    public function testGetPostsList()
    {
        $object = $this->objFromFixture(ElementBlogPosts::class, 'one');
        $this->assertEquals($object->Blog()->getBlogPosts()->limit($object->Limit), $object->getPostsList());
    }
}
