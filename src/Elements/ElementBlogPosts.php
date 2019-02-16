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
    /**
     * @var string
     */
    private static $icon = 'font-icon-block-content';

    /**
     * @var string
     */
    private static $table_name = 'ElementBlogPosts';

    /**
     * @var array
     */
    private static $db = array(
        'Limit' => 'Int',
        'Content' => 'HTMLText',
    );

    /**
     * @var array
     */
    private static $has_one = array(
        'Blog' => Blog::class,
        'Category' => BlogCategory::class,
    );

    /**
     * @var array
     */
    private static $defaults = array(
        'Limit' => 3,
    );

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
                    $fields->dataFieldByName('BlogID')
                        ->setTitle(_t(__CLASS__ . 'BlogLabel', 'Featured Blog'))
                        ->setEmptyString(''),
                    'Limit'
                );

                $dataSource = function ($val) {
                    if ($val) {
                        return Blog::get()->byID($val)->Categories()->map('ID', 'Title');
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
     * @return mixed
     */
    public function getPostsList()
    {
        if ($this->BlogID) {
            if ($this->CategoryID) {
                return BlogCategory::get()->byID($this->CategoryID)->BlogPosts()->Limit($this->Limit);
            }
            return Blog::get()->byID($this->BlogID)->getBlogPosts()->Limit($this->Limit);
        } else {
            return BlogPost::get()->sort('PublishDate DESC')->limit($this->Limit);
        }
    }

    /**
     * @return DBHTMLText
     */
    public function getSummary()
    {
        $count = $this->getPostsList()->count();
        $label = _t(
            BlogPost::class . '.PLURALS',
            '{count} post|{count} posts',
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
