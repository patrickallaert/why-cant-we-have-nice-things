<?php
namespace History\Entities\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Refresh the model's slug.
     */
    public function sluggify()
    {
        $this->slug = $this->slug ?: $this->getSlug();
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return Str::slug($this->getSlugSource());
    }

    /**
     * @return string
     */
    public function getSlugSource()
    {
        return $this->name;
    }
}
