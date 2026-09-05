<?php

namespace Warext\SSS\Repository;

use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;

class Faq extends Repository
{
    public function findActiveFaqsForList(): Finder
    {
        return $this->finder('Warext\SSS:Faq')
            ->with('Category')
            ->where('is_active', 1)
            ->where('Category.is_active', 1)
            ->order('category_id')
            ->order('display_order')
            ->order('faq_id');
    }
}
