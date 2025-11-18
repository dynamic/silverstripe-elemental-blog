<?php

namespace Dynamic\Elements\Blog\Elements;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Blog\Model\Blog;
use SilverStripe\Blog\Model\BlogController;
use SilverStripe\Blog\Model\BlogPost;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Model\List\PaginatedList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ValidationException;

/**
 * Class ElementBlogOverview
 *
 * @package Dynamic\Elements\Blog\Elements
 * @property string $Content
 * @property int $ShowPagination
 */
class ElementBlogOverview extends BaseElement
{
    private static array $db = [
        'Content' => 'HTMLText',
        'ShowPagination' => 'Boolean(0)',
    ];

    private static string $icon = 'font-icon-p-articles';

    private static string $table_name = 'ElementBlogOverview';

    private static string $singular_name = 'Element blog overview';

    private static string $plural_name = 'Element blog overview blocks';

    private static string $description = 'Block displaying Blog Posts with pagination';

    /**
     * We use this default_title for the Block name in the CMS. Feel free to update it via config
     */
    private static string $default_title = 'Blog Overview';

    /**
     * The main purpose of this Block is to replace the standard Blog Layout functionality (including pagination,
     * filters, and widgets). In order to do that out of the box, this Block must be used on a Blog page (as it is the
     * Blog page and BlogController that provides the feedback we require)
     *
     * By default, this Block will return null in all the areas where it expects to find a Blog/BlogController if it
     * does not. However, if you enable this Block to be used elsewhere, there are extension points/etc available for
     * you to return/update the DataList/Paginated list in other ways
     */
    private static bool $allow_use_outside_of_blog = false;

    /**
     * Depending on your config, there is potentially no reason for a content author to enter this Block to make any
     * edits, if that is the case for you, then it likely makes sense that you just set a title for them by default
     */
    private static bool $set_default_title = false;

    /**
     * You can set this to false if you would prefer that we do not display the default Title field that Elemental
     * provides to users
     */
    private static bool $show_title_field = true;

    /**
     * You can set this to false if you would prefer that we do not display the HTMLEditor/$Content field to users
     */
    private static bool $show_content_field = true;

    /**
     * By default, we show the "Show Pagination" field in the CMS (since it is part of the default supported features).
     * You may, however, prefer that content authors display pagination for the Blog using the specific
     * `PaginationBlock`, and if that's the case, you'll likely want to set this to false via config, as it will no
     * longer be relevant for your content authors
     */
    private static bool $show_pagination_field = true;

    /**
     * Default value for ShowPagination. If set, this block will also output the pagination for your Blog. You can
     * update this value via config
     */
    private static int $pagination_field_default = 1;

    /**
     * This can be updated via config if (for whatever reason) you do not wish to show this message field in the CMS
     */
    private static bool $show_info_message_field = true;

    /**
     * Default value used for the message field in the CMS
     */
    private static string $info_message_field_default = 'This block automatically displays Blog Posts and pagination';

    /**
     * Cached value for BlogPosts from the Blog page
     *
     * @var DataList|BlogPost[]|null
     */
    private $blogPosts;

    /**
     * Cached value for the PaginatedList from BlogController
     *
     * @var PaginatedList|null
     */
    private $paginatedList;

    /**
     * Cached value for our CacheKey. It's not all that cheap to generate it, so, we should only do it once per
     * request
     *
     * @var string|null
     */
    private $cacheKey;

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getType(): string
    {
        return static::config()->get('default_title');
    }

    /**
     * @codeCoverageIgnore
     * @return FieldList
     */
    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        // Removing scaffold fields so that they can be added more explicitly (and allowing for update via extension
        // points)
        $fields->removeByName([
            'Content',
            'ShowPagination',
        ]);

        // Check whether we want to display the default Title field
        if (!static::config()->get('show_content_field')) {
            $fields->removeByName('Title');
        }

        // Check whether we want to display our Content WYSIWYG field
        if (static::config()->get('show_content_field')) {
            $contentField = HTMLEditorField::create('Content');

            // An opportunity for you to update this field before it is added (EG: you might want to add a description)
            $this->invokeWithExtensions('updateContentField', $contentField);

            $fields->addFieldToTab(
                'Root.Main',
                $contentField
            );
        }

        // Check whether we want to allow the author to determine whether or not the Block outputs with pagination
        if (static::config()->get('show_pagination_field')) {
            $showPaginationField = CheckboxField::create('ShowPagination');

            // An opportunity for you to update this field before it is added (EG: you might want to add a description)
            $this->invokeWithExtensions('updateShowPaginationField', $showPaginationField);

            $fields->addFieldToTab(
                'Root.Main',
                $showPaginationField
            );
        }

        // Check whether want to display this message
        if (static::config()->get('show_info_message_field')) {
            $messageField = LiteralField::create(
                'BlockInfoMessage',
                sprintf(
                    '<p style="text-align:center">%s</p>',
                    static::config()->get('info_message_field_default')
                )
            );

            // An opportunity for you to update this field before it is added
            $this->invokeWithExtensions('updateMessageField', $messageField);

            $fields->addFieldToTab(
                'Root.Main',
                $messageField
            );
        }

        return $fields;
    }

    public function populateDefaults(): void
    {
        parent::populateDefaults();

        // Set the Title by default, if you have specified for us to
        if (static::config()->get('set_default_title')) {
            $this->Title = static::config()->get('default_title');
        }

        // Always set the default for these fields
        $this->ShowPagination = static::config()->get('pagination_field_default');
    }

    /**
     * We'll allow the passing of a Controller for a couple of reasons:
     * 1) Provides a new way for you to dictate how a PaginatedList might be provided
     * 2) Makes testing much easier...
     *
     * @param Controller|null $controller
     * @return PaginatedList|null
     * @throws ValidationException
     */
    public function getPaginatedList(?Controller $controller = null): ?PaginatedList
    {
        // Return our cached value, if one exists
        if ($this->paginatedList !== null) {
            return $this->paginatedList;
        }

        if ($controller === null) {
            /** @var BlogController $controller */
            $controller = Controller::curr();
        }

        // Ideally, we want to fetch the PaginatedList from the BlogController, but, if this Block is not being used on
        // a Blog page, then that will not be (immediately) possible. You have three options:
        // 1) You can implement a method `PaginatedList` on your Controller, and provide it with the appropriate data
        // 2) You can use the `updatePaginatedList` extension point to apply your filters/limits to the PaginatedList
        //    that will be provided to it
        // 3) Maybe you should be using ElementBlogPosts Block instead

        // The active Controller is what we expect, so we can simply return the PaginatedList from there. Early exit for
        // increased readability
        if ($controller instanceof BlogController) {
            // Store this PaginatedList as our cached value and provide an extension point
            $this->setPaginatedList($controller->PaginatedList());

            return $this->paginatedList;
        }

        // You have specified that this Block *cannot* be used outside of the Blog, so that also means that we
        // expect a Controller to have been present
        if (!static::config()->get('allow_use_outside_of_blog')) {
            return null;
        }

        if ($controller !== null && $controller->hasMethod('PaginatedList')) {
            // If you know that you're going to be using this Block on another Page type, then you can implement the
            // getBlogPostPaginatedList() method there, and we'll use that
            $paginatedList = $controller->PaginatedList();
        } else {
            // Since you have specified that this Block *can* be used outside of the Blog, we'll create a PaginatedList
            // containing all BlogPosts in the DB, and you can then manipulate that List as you wish through the
            // extension point
            $paginatedList = PaginatedList::create($this->getBlogPosts());
        }

        // Store this PaginatedList as our cached value and provide an extension point
        $this->setPaginatedList($paginatedList);

        return $this->paginatedList;
    }

    /**
     * @return DataList
     * @throws ValidationException
     */
    public function getBlogPosts(): ?DataList
    {
        // Return our cached value, if one exists
        if ($this->blogPosts !== null) {
            return $this->blogPosts;
        }

        /** @var Blog $page */
        $page = $this->getPage();

        // Ideally, we want to fetch the BlogPosts that were specifically posted under a Parent Blog page, but, if this
        // Block is not being used on a Blog page, then that is not possible. Instead, we will just return all Blog
        // Posts in the DB. You can then update this DataList (maybe with additional filters/limits/etc) by using the
        // `updateBlogPosts` extension point
        if ($page instanceof Blog) {
            // Store this DataList as our cached value and provide an extension point
            $this->setBlogPosts($page->getBlogPosts());

            return $this->blogPosts;
        }

        // You have specified that this Block *cannot* be used outside of the Blog, so that also means that we
        // expect a parent Page to have been present and of type Blog
        if (!static::config()->get('allow_use_outside_of_blog')) {
            return null;
        }

        if ($page !== null && $page->hasMethod('getBlogPosts')) {
            // If you know that you're going to be using this Block on another Page type, then you can implement the
            // getBlogPosts() method there, and we'll use that
            $blogPosts = $page->getBlogPosts();
        } else {
            // Since you have specified that this Block *can* be used outside of the Blog, we'll create a DataList
            // containing all BlogPosts in the DB, and you can then manipulate that List as you wish through the
            // extension point
            $blogPosts = BlogPost::get();
        }

        // Store this DataList as our cached value and provide an extension point
        $this->setBlogPosts($blogPosts);

        return $this->blogPosts;
    }

    /**
     * @param PaginatedList|null $paginatedList
     */
    protected function setPaginatedList(?PaginatedList $paginatedList): void
    {
        // Provided is an opportunity for you to update this PaginatedList
        $this->invokeWithExtensions('updatePaginatedList', $paginatedList);

        // Store this List as our cached value
        $this->paginatedList = $paginatedList;
    }

    /**
     * @param DataList|null $blogPosts
     */
    protected function setBlogPosts(?DataList $blogPosts): void
    {
        // Provided is an opportunity for you to update this DataList
        $this->invokeWithExtensions('updateBlogPosts', $blogPosts);

        // Store this DataList as our cached value
        $this->blogPosts = $blogPosts;
    }
}
