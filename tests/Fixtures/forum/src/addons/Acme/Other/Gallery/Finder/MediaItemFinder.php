<?php

namespace Acme\Other\Gallery\Finder;

// An optional integration: extends a finder of an add-on this forum does not have. XenForo
// requests a finder with Finder as the fake base class, so that is what the XFCP_ parent is.
class MediaItemFinder extends XFCP_MediaItemFinder
{
    public function withAlbum(): static
    {
        return $this->where('album_id', '>', 0);
    }
}
