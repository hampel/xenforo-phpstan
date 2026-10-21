<?php

namespace Acme\Other\XF\Entity;

// Another installed add-on, shipped without _output/extension_hint.php, as a packaged add-on is.
class User extends XFCP_User
{
    public function fromOther(): string
    {
        return 'other';
    }
}
