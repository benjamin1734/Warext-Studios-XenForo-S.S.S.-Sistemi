<?php

namespace Warext\SSS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Category extends Entity
{
    public static function getStructure(Structure $structure): Structure
    {
        $structure->table = 'xf_wrxt_sss_category';
        $structure->shortName = 'Warext\SSS:Category';
        $structure->primaryKey = 'category_id';
        $structure->columns = [
            'category_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'title' => ['type' => self::STR, 'maxLength' => 100, 'required' => true],
            'description' => ['type' => self::STR, 'maxLength' => 255, 'default' => ''],
            'icon' => ['type' => self::STR, 'maxLength' => 64, 'default' => 'fa-circle-question'],
            'display_order' => ['type' => self::UINT, 'default' => 10],
            'is_active' => ['type' => self::BOOL, 'default' => true],
            'created_date' => ['type' => self::UINT, 'default' => \XF::$time],
            'updated_date' => ['type' => self::UINT, 'default' => 0]
        ];
        $structure->relations = [
            'Faqs' => [
                'entity' => 'Warext\SSS:Faq',
                'type' => self::TO_MANY,
                'conditions' => [['category_id', '=', '$category_id']]
            ]
        ];

        return $structure;
    }

    protected function _preSave(): void
    {
        if ($this->isChanged())
        {
            $this->updated_date = \XF::$time;
        }
    }
}
