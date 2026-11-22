<?php

namespace Acme\Other\Repository;

// A second checkout of the add-on inside its own directory, as a git worktree made by editor
// tooling is. By its path this is `Acme\Other\.worktrees\some-branch\Repository\GadgetRepository`,
// which is no class name; and this class exists here and nowhere else, so it resolves only if
// the dot directory is scanned.
class GadgetRepository
{
}
