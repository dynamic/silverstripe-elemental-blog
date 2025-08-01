<?php

namespace Dynamic\Elements\Blog\Elements;

use DNADesign\Elemental\Models\BaseElement;
use Sheadawson\DependentDropdown\Forms\DependentDropdownField;
use SilverStripe\Blog\Model\Blog;
use SilverStripe\Blog\Model\BlogCategory;
use SilverStripe\Blog\Model\BlogPost;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\ValidationResult;

/**
 * Class ElementBlogPosts
 * @package Dynamic\Elements\Elements
 *
 * @property int $Limit
 * @property string $Content
 *
 * @property int $BlogID
 * @property int $CategoryID
 * @method Blog Blog()
 * @method BlogCategory Category()
 */
class ElementBlogPosts extends BaseElement
{
    private static string $icon = 'font-icon-menu-campaigns';

    private static string $table_name = 'ElementBlogPosts';

    private static array $db = [
        'Limit' => 'Int',
        'Content' => 'HTMLText',
    ];

    /**
     * @var array
     */
    private static array $has_one = [
        'Blog' => Blog::class,
        'Category' => BlogCategory::class,
    ];

    /**
     * @var array
     */
    private static array $defaults = [
        'Limit' => 3,
    ];

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->dataFieldByName('Content')
                ->setRows(8);

            $fields->dataFieldByName('Limit')
                ->setTitle(_t(__CLASS__ . 'LimitLabel', 'Posts to show'));

            if (class_exists(Blog::class)) {
                $fields->insertBefore(
                    'Limit',
                    $fields->dataFieldByName('BlogID')
                        ->setTitle(_t(__CLASS__ . 'BlogLabel', 'Featured Blog'))
                        ->setEmptyString('')
                );

                $dataSource = function ($val) {
                    if ($val) {
                        $blog = Blog::get()->byID($val);
                        if ($blog) {
                            return $blog->Categories()->map('ID', 'Title');
                        }
                        return [];
                    }
                    return [];
                };

                $fields->insertAfter(
                    'BlogID',
                    DependentDropdownField::create('CategoryID', _t(
                        __CLASS__ . 'CategoryLabel',
                        'Category'
                    ), $dataSource)
                        ->setDepends($fields->dataFieldByName('BlogID'))
                        ->setHasEmptyDefault(true)
                        ->setEmptyString('')
                );
            }
        });

        return parent::getCMSFields();
    }

    /**
     * @return ArrayList|DataList
     */
    public function getPostsList()
    {
        /** @var ArrayList $posts */
        $posts = ArrayList::create();

        if ($this->BlogID && $this->CategoryID && $category = BlogCategory::get()->byID($this->CategoryID)) {
            $posts = $category->BlogPosts();
        } elseif ($this->BlogID && $blog = Blog::get()->byID($this->BlogID)) {
            $posts = $blog->getBlogPosts();
        } else {
            $posts = BlogPost::get()->sort('PublishDate DESC');
        }

        $this->extend('updateGetPostsList', $posts);

        return $posts->limit($this->Limit);
    }


    /**
     * @return DBHTMLText
     */
    public function getSummary()
    {
        $count = $this->getPostsList()->count();
        $label = _t(
            BlogPost::class . '.PLURALS',
            'A Blog Post|{count} Blog Posts',
            [ 'count' => $count ]
        );
        return DBField::create_field('HTMLText', $label)->Summary(20);
    }

    /**
     * @return array
     */
    protected function provideBlockSchema()
    {
        $blockSchema = parent::provideBlockSchema();
        $blockSchema['content'] = $this->getSummary();
        return $blockSchema;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'Blog Posts');
    }
}
