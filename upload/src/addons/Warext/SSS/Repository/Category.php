<?php

namespace Warext\SSS\Repository;

use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;

class Category extends Repository
{
    public function findActiveCategoriesForList(): Finder
    {
        return $this->finder('Warext\SSS:Category')
            ->where('is_active', 1)
            ->order('display_order')
            ->order('title');
    }
}
