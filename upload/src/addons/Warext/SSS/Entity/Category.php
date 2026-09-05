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
            'allowed_user_group_ids' => ['type' => self::STR, 'maxLength' => 255, 'default' => ''],
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

    public function getAllowedUserGroupIds(): array
    {
        if ($this->allowed_user_group_ids === '') { return []; }
        return array_values(array_unique(array_filter(array_map('intval', explode(',', $this->allowed_user_group_ids)))));
    }

    public function canViewFor(\XF\Entity\User $visitor): bool
    {
        $allowed = $this->getAllowedUserGroupIds();
        if (!$allowed) { return true; }
        $groups = [(int)$visitor->user_group_id];
        $secondary = $visitor->secondary_group_ids;
        if (is_array($secondary)) { $groups = array_merge($groups, array_map('intval', $secondary)); }
        elseif (is_string($secondary) && $secondary !== '') { $groups = array_merge($groups, array_map('intval', explode(',', $secondary))); }
        return (bool)array_intersect($allowed, $groups);
    }

    protected function _preSave(): void
    {
        if ($this->isChanged()) { $this->updated_date = \XF::$time; }
    }
}
