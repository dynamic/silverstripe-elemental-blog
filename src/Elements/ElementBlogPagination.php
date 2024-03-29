<?php

namespace Dynamic\Elements\Blog\Elements;

/**
 * A Block that has the intention of being used to display just our Pagination results for the Blog Posts on this page.
 *
 * The reason we have made it extend ElementBlogOverview, however, is because you *may* decide to use this differently.
 * If we simply extend ElementBlogOverview, then you do also get access to Widgets/Posts/etc.
 *
 * All this class has done (really) is set different default config values, and it has been given a separate\
 * (simplified) template.
 *
 * Class ElementBlogPagination
 *
 * @package Dynamic\Elements\Blog\Elements
 */
class ElementBlogPagination extends ElementBlogOverview
{
    private static string $icon = 'font-icon-dot-3';

    private static string $table_name = 'ElementBlogPagination';

    private static string $singular_name = 'Element blog pagination';

    private static string $plural_name = 'Element blog pagination blocks';

    private static string $description = 'Block displaying pagination for Blog Posts';

    /**
     * We use this default_title for the Block name in the CMS. Feel free to update it via config
     */
    private static string $default_title = 'Blog Pagination';

    private static bool $allow_use_outside_of_blog = false;

    /**
     * OotB there is really no reason for a content author to enter this Block to make any edits, so, by default, we'll
     * just set a generic default Title. You can disable this via config
     */
    private static bool $set_default_title = true;

    /**
     * This Block is intended to display pagination, so, hide this field by default
     */
    private static bool $show_content_field = false;

    /**
     * This is the Pagination Block, we assume that we always want Pagination to be displayed (since that's all it will
     * display). So, no point in showing the CMS field to allow folks to toggle this on/off
     */
    private static bool $show_pagination_field = false;

    private static int $pagination_field_default = 1;

    /**
     * This Block is not intended to display widgets, so, hide this field by default
     */
    private static bool $show_widgets_field = false;

    /**
     * This Block is not intended to display widgets, so, set this to 0 by default
     */
    private static int $widgets_field_default = 0;

    /**
     * This can be updated via config if (for whatever reason) you do not wish to show this message field in the CMS
     */
    private static bool $show_info_message_field = true;

    /**
     * Default value used for the message field in the CMS. You can update this via config
     */
    private static string $info_message_field_default = 'This block automatically displays pagination for Blog Posts';
}
