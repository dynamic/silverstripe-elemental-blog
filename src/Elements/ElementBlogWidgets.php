<?php

namespace Dynamic\Elements\Blog\Elements;

/**
 * A Block that has the intention of being used to display just the Widgets that have been specified for this Page.
 *
 * The reason we have made it extend ElementBlogOverview, however, is because you *may* decide to use this differently.
 * If we simply extend ElementBlogOverview, then you do also get access to Pagination/Posts/etc.
 *
 * All this class has done (really) is set different default config values, and it has been given a separate\
 * (simplified) template.
 *
 * Class ElementBlogWidgets
 *
 * @package Dynamic\Elements\Blog\Elements
 */
class ElementBlogWidgets extends ElementBlogOverview
{
    private static string $icon = 'font-icon-block-layout';

    private static string $table_name = 'ElementBlogWidgets';

    private static string $singular_name = 'Element blog widgets';

    private static string $plural_name = 'Element blog widget blocks';

    private static string $description = 'Block displaying Blog Widgets';

    /**
     * We use this default_title for the Block name in the CMS. Feel free to update it via config
     */
    private static string $default_title = 'Blog Widgets';

    private static bool $allow_use_outside_of_blog = false;

    /**
     * OotB there is really no reason for a content author to enter this Block to make any edits, so, by default, we'll
     * just set a generic default Title. You can disable this via config
     */
    private static bool $set_default_title = true;

    /**
     * This Block is intended to display widgets, so, hide this field by default
     */
    private static bool $show_content_field = false;

    /**
     * This Block is not intended to display pagination, so, hide this field by default
     */
    private static int $show_pagination_field = 0;

    /**
     * This Block is not intended to display pagination, so, set this to `0` by default
     */
    private static int $pagination_field_default = 0;

    /**
     * This is the Widgets Block, we assume that we always want Widgets to be displayed. So, OotB there is no reason
     * to show the CMS field to allow folks to toggle this on/off
     */
    private static int $show_widgets_field = 0;

    /**
     * We want Widgets to be displayed by default for this Block
     */
    private static int $widgets_field_default = 1;

    /**
     * This can be updated via config if (for whatever reason) you do not wish to show this message field in the CMS
     */
    private static int $show_info_message_field = 1;

    /**
     * Default value used for the message field in the CMS.
     */
    private static string $info_message_field_default = 'This block will automatically display Blog Widgets';
}
