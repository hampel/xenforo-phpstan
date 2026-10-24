<?php

namespace Acme\Other\XF\Entity;

// Another installed add-on, shipped without _output/extension_hint.php, as a packaged add-on is.
/**
 * @property int $acme_other_count
 */
class User extends XFCP_User
{
    public function fromOther(): string
    {
        return 'other';
    }
}
