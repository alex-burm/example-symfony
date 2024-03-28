<?php

namespace App\EventListener;

use App\Entity\Category;

class PostChangesEvent
{
    public function __construct(
        protected Category $category
    ) {
    }

    public function getCategory(): Category
    {
        return $this->category;
    }
}
