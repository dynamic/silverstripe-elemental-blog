<?php

namespace Dynamic\Elements\Blog\Tests\Elements;

use DNADesign\Elemental\Extensions\ElementalPageExtension;
use Dynamic\Elements\Blog\Elements\ElementBlogOverview;
use Page;
use SilverStripe\Blog\Model\Blog;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ValidationException;

class ElementBlogOverviewTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = 'ElementBlogOverviewTest.yml';

    /**
     * @var array
     */
    protected static $required_extensions = [
        Page::class => [
            ElementalPageExtension::class,
        ],
    ];

    public function testPopulateDefaults(): void
    {
        $block = ElementBlogOverview::create();
        $this->assertEmpty($block->Title);
        $this->assertTrue((bool) $block->ShowPagination);

        // Update our config settings
        ElementBlogOverview::config()->set('set_default_title', true);
        ElementBlogOverview::config()->set('pagination_field_default', 0);

        $block = ElementBlogOverview::create();
        $this->assertEquals(ElementBlogOverview::config()->get('default_title'), $block->Title);
        $this->assertFalse((bool) $block->ShowPagination);
    }

    public function testGetBlogPosts(): void
    {
        /** @var ElementBlogOverview $block */
        $block = $this->objFromFixture(ElementBlogOverview::class, 'block1');

        $this->assertNotNull($block->getBlogPosts());
        $this->assertCount(4, $block->getBlogPosts());
    }

    /**
     * This Block is on a non Blog page, and we have not allowed this type of Block to be used on other page types, so,
     * it should just return null
     *
     * @throws ValidationException
     */
    public function testGetBlogPostsDenied(): void
    {
        /** @var ElementBlogOverview $block */
        $block = $this->objFromFixture(ElementBlogOverview::class, 'block4');

        $this->assertNull($block->getBlogPosts());
    }

    /**
     * This Block has been added to a (test only) Page, and our config is going to be set to allow that. We should
     * expect that the custom getBlogPosts() method on the (test only) Page returns an empty DataList
     *
     * @throws ValidationException
     */
    public function testGetBlogPostsCustom(): void
    {
        // Update our config to allow this Block type to be used outside of the Blog
        ElementBlogOverview::config()->set('allow_use_outside_of_blog', true);

        /** @var ElementBlogOverview $block */
        $block = $this->objFromFixture(ElementBlogOverview::class, 'block4');

        $this->assertNotNull($block->getBlogPosts());
        $this->assertCount(0, $block->getBlogPosts());
    }

    /**
     * This Block has been added to a standard Page, and our config is going to be set to allow that. We should
     * expect that the we are simply returned a DataList of all available BlogPosts in the DB
     *
     * @throws ValidationException
     */
    public function testGetBlogPostsDefault(): void
    {
        // Update our config to allow this Block type to be used outside of the Blog
        ElementBlogOverview::config()->set('allow_use_outside_of_blog', true);

        /** @var ElementBlogOverview $block */
        $block = $this->objFromFixture(ElementBlogOverview::class, 'block3');

        $this->assertNotNull($block->getBlogPosts());
        $this->assertCount(4, $block->getBlogPosts());
    }
}
