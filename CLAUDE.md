# postslistOptions

Notes for AI assistants working on the Dotclear plugin postslistOptions.

**Read first** the shared conventions of the author's Dotclear modules:
https://raw.githubusercontent.com/JcDenis/gh2da/master/CONVENTIONS.md
(if a web tool refuses the address, use `curl -s` on it from the shell).
The rules below take precedence for this module.

## Module

Add options on the posts or pages lists actions box like open/close/delete comments/trackbacks on multiple posts/pages.

No admin page. Nothing on the public side.

Required Dotclear and PHP versions: see `_define.php`.

## Structure

* `src/Backend.php`: admin side: menu entry, favorites, behaviors registration.
* `src/BackendBehaviors.php`: admin behaviors.
* `src/My.php`: module helpers (`My::id()`, settings, permissions).
* Behaviors used: `adminPostsActions`, `adminPagesActions`.

## Pitfalls

* None recorded yet: add here the traps met while working on the module.

## Testing

* CI browses the common admin and public pages; extra pages can be listed in `.github/dotclear-test.txt`.
