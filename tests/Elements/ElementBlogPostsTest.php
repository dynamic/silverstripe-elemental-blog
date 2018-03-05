<?php

namespace Dynamic\Elements\Elements\Tests;

use Dynamic\Elements\Elements\ElementBlogPosts;
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
    public function testGetElementSummary()
    {
        $object = $this->objFromFixture(ElementBlogPosts::class, 'one');
        $this->assertEquals($object->ElementSummary(), $object->dbObject("Content")->Summary(20));
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
    public function testValidateBlog()
    {
        $object = $this->objFromFixture(ElementBlogPosts::class, 'one');
        $blog = $this->objFromFixture(Blog::class, 'default');

        $valid = $object->validate()->isValid();
        $this->assertFalse($valid);
        $object->BlogID = $blog->ID;
        $valid = $object->validate()->isValid();
        $this->assertTrue($valid);
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
