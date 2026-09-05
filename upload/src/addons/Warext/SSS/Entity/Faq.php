<?php

namespace Warext\SSS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Faq extends Entity
{
    public static function getStructure(Structure $structure): Structure
    {
        $structure->table = 'xf_wrxt_sss_faq';
        $structure->shortName = 'Warext\SSS:Faq';
        $structure->primaryKey = 'faq_id';
        $structure->columns = [
            'faq_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'category_id' => ['type' => self::UINT, 'required' => true],
            'question' => ['type' => self::STR, 'maxLength' => 255, 'required' => true],
            'answer' => ['type' => self::STR, 'required' => true],
            'anchor' => ['type' => self::STR, 'maxLength' => 100, 'default' => ''],
            'allowed_user_group_ids' => ['type' => self::STR, 'maxLength' => 255, 'default' => ''],
            'display_order' => ['type' => self::UINT, 'default' => 10],
            'is_active' => ['type' => self::BOOL, 'default' => true],
            'is_featured' => ['type' => self::BOOL, 'default' => false],
            'created_date' => ['type' => self::UINT, 'default' => \XF::$time],
            'updated_date' => ['type' => self::UINT, 'default' => 0]
        ];
        $structure->relations = [
            'Category' => [
                'entity' => 'Warext\SSS:Category',
                'type' => self::TO_ONE,
                'conditions' => [['category_id', '=', '$category_id']],
                'primary' => true
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
        if (!$this->anchor)
        {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $this->question) ?: $this->question;
            $slug = strtolower(trim((string)preg_replace('/[^a-zA-Z0-9]+/', '-', $ascii), '-'));
            $slug = substr($slug ?: 'sss', 0, 80);
            $this->anchor = $slug . '-' . bin2hex(random_bytes(3));
        }
        if ($this->isChanged()) { $this->updated_date = \XF::$time; }
    }
}
