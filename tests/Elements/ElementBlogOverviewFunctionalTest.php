<?php

namespace Dynamic\Elements\Blog\Tests\Elements;

use DNADesign\Elemental\Extensions\ElementalPageExtension;
use Dynamic\Elements\Blog\Elements\ElementBlogOverview;
use Dynamic\Elements\Blog\Elements\ElementBlogPagination;
use Dynamic\Elements\Blog\Tests\Fake\SetPageLengthExtension;
use SilverStripe\Blog\Model\Blog;
use SilverStripe\Core\Config\Config;
use Dynamic\Elements\Blog\Tests\Fake\PageController;
use Page;
use PageController as BasePageController;
use SilverStripe\Blog\Model\BlogController;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\NumericField;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\Model\List\PaginatedList;
use SilverStripe\ORM\ValidationException;

/**
 * Class ElementBlogOverviewFunctionalTest
 *
 * @package Dynamic\Elements\Blog\Tests\Elements
 */
class ElementBlogOverviewFunctionalTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = 'ElementBlogOverviewFunctionalTest.yml';

    /**
     * @var bool
     */
    protected static $use_draft_site = true;

    /**
     * @var array
     */
    protected static $required_extensions = [
        Page::class => [
            ElementalPageExtension::class,
        ],
    ];

    /**
     * @throws ValidationException
     */
    public function testPaginatedListAvailable(): void
    {
        /** @var ElementBlogOverview $block */
        $block = $this->objFromFixture(ElementBlogOverview::class, 'block1');
        $controller = new BlogController();

        // Pass the controller through for the first get
        $this->assertNotNull($block->getPaginatedList($controller));
        // Our cache should now be set, so we shouldn't need the controller this time
        $this->assertNotNull($block->getPaginatedList());
    }

    /**
     * @throws ValidationException
     */
    public function testPaginatedListDenied(): void
    {
        /** @var ElementBlogOverview $block */
        $block = $this->objFromFixture(ElementBlogOverview::class, 'block2');
        $controller = new PageController();

        // We have not allowed usage outside of BlogController, so this should be null
        $this->assertNull($block->getPaginatedList($controller));
    }

    /**
     * @throws ValidationException
     */
    public function testPaginatedListCustom(): void
    {
        // Update our config to allow this Block type to be used outside of the Blog
        ElementBlogOverview::config()->set('allow_use_outside_of_blog', true);

        /** @var ElementBlogOverview $block */
        $block = $this->objFromFixture(ElementBlogOverview::class, 'block2');
        $controller = new PageController();

        // We have not allowed usage outside of BlogController, so this should be null
        $this->assertNotNull($block->getPaginatedList($controller));
    }

    /**
     * @throws ValidationException
     */
    public function testPaginatedListDefault(): void
    {
        // Update our config to allow this Block type to be used outside of the Blog
        ElementBlogOverview::config()->set('allow_use_outside_of_blog', true);

        /** @var ElementBlogOverview $block */
        $block = $this->objFromFixture(ElementBlogOverview::class, 'block2');
        $controller = new BasePageController();

        // We have not allowed usage outside of BlogController, so this should be null
        $this->assertNotNull($block->getPaginatedList($controller));
    }

    /**
     * A Block with a PageLength set overrides the page length of the PaginatedList it was given
     *
     * @throws ValidationException
     */
    public function testPageLengthOverridesInheritedValue(): void
    {
        ElementBlogOverview::config()->set('allow_use_outside_of_blog', true);

        /** @var ElementBlogOverview $block */
        $block = $this->objFromFixture(ElementBlogOverview::class, 'block2');
        $block->PageLength = 2;

        $paginatedList = $block->getPaginatedList(new PageController());

        $this->assertNotNull($paginatedList);
        $this->assertSame(2, $paginatedList->getPageLength());
    }

    /**
     * An unset (zero) PageLength must leave the page length of the Blog page's own list alone. The Blog page here is
     * set to 7 posts per page, which is neither the framework default nor any value this Block could produce, so a
     * regression that overwrote it with either of those would be caught
     *
     * @throws ValidationException
     */
    public function testPageLengthOfZeroInheritsBlogPostsPerPage(): void
    {
        /** @var ElementBlogOverview $block */
        $block = $this->objFromFixture(ElementBlogOverview::class, 'block1');
        $block->PageLength = 0;

        $paginatedList = $block->getPaginatedList($this->getBlogControllerWithPostsPerPage(7));

        $this->assertNotNull($paginatedList);
        $this->assertSame(7, $paginatedList->getPageLength());
        $this->assertNotSame($this->getDefaultPageLength(), $paginatedList->getPageLength());
    }

    /**
     * A nonsensical (negative) PageLength is treated the same way as zero, rather than being pushed onto the List
     * (which PaginatedList would reject)
     *
     * @throws ValidationException
     */
    public function testNegativePageLengthInheritsBlogPostsPerPage(): void
    {
        /** @var ElementBlogOverview $block */
        $block = $this->objFromFixture(ElementBlogOverview::class, 'block1');
        $block->PageLength = -5;

        $paginatedList = $block->getPaginatedList($this->getBlogControllerWithPostsPerPage(7));

        $this->assertNotNull($paginatedList);
        $this->assertSame(7, $paginatedList->getPageLength());
    }

    /**
     * The point of this field: a Block overriding the page length the Blog page would otherwise have used
     *
     * @throws ValidationException
     */
    public function testPageLengthOverridesBlogPostsPerPage(): void
    {
        /** @var ElementBlogOverview $block */
        $block = $this->objFromFixture(ElementBlogOverview::class, 'block1');
        $block->PageLength = 2;

        $paginatedList = $block->getPaginatedList($this->getBlogControllerWithPostsPerPage(7));

        $this->assertNotNull($paginatedList);
        $this->assertSame(2, $paginatedList->getPageLength());
    }

    /**
     * PageLength is applied *before* updatePaginatedList runs, so an extension still has the final word (as documented)
     *
     * @throws ValidationException
     */
    public function testPageLengthIsAppliedBeforeTheUpdatePaginatedListExtensionPoint(): void
    {
        ElementBlogOverview::config()->set('allow_use_outside_of_blog', true);
        Config::modify()->merge(ElementBlogOverview::class, 'extensions', [SetPageLengthExtension::class]);

        /** @var ElementBlogOverview $block */
        $block = $this->objFromFixture(ElementBlogOverview::class, 'block2');
        $block->PageLength = 2;

        $paginatedList = $block->getPaginatedList(new PageController());

        // The Block asked for 2, the extension asked for 4, and the extension ran last
        $this->assertNotNull($paginatedList);
        $this->assertSame(4, $paginatedList->getPageLength());
    }

    /**
     * The PageLength field is scaffolded into the CMS for the Block, and can be hidden via config
     */
    public function testPageLengthFieldIsDisplayedInTheCMS(): void
    {
        $block = ElementBlogOverview::create();

        $field = $block->getCMSFields()->dataFieldByName('PageLength');

        $this->assertInstanceOf(NumericField::class, $field);

        // ... and it can be turned off via config, like the other fields on this Block
        ElementBlogOverview::config()->set('show_page_length_field', false);

        $this->assertNull($block->getCMSFields()->dataFieldByName('PageLength'));
    }

    /**
     * ElementBlogPagination inherits this field, and its page links are only correct when its value matches the
     * Overview block it paginates, so it has to say something different next to the field
     */
    public function testPaginationBlockWarnsThatPageLengthMustMatch(): void
    {
        $block = ElementBlogPagination::create();

        $field = $block->getCMSFields()->dataFieldByName('PageLength');

        $this->assertInstanceOf(NumericField::class, $field);
        $this->assertStringContainsString('Must match', (string) $field->getDescription());

        // The Overview block, which does not have that problem, says something else
        $overviewField = ElementBlogOverview::create()->getCMSFields()->dataFieldByName('PageLength');

        $this->assertStringNotContainsString('Must match', (string) $overviewField->getDescription());
    }

    /**
     * A BlogController for the Blog in this test's fixture, reporting the given number of posts per page. This is the
     * value an Overview Block inherits when its own PageLength is left unset
     */
    private function getBlogControllerWithPostsPerPage(int $postsPerPage): BlogController
    {
        /** @var Blog $blog */
        $blog = $this->objFromFixture(Blog::class, 'blog1');
        $blog->PostsPerPage = $postsPerPage;

        return BlogController::create($blog);
    }

    /**
     * The page length a freshly created PaginatedList has, which is what the Block must fall back to
     */
    private function getDefaultPageLength(): int
    {
        return PaginatedList::create(ArrayList::create())->getPageLength();
    }
}
