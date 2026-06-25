# Introduction to Plugin Development

> Source: https://developer.wordpress.org/plugins/intro/ (saved 2025-06-25)

## Core rule

**Do not edit WordPress core.** Core is overwritten on every update. Add or change behavior via plugins (or themes for presentation).

## Plugins

- Can be a single PHP file with a plugin header, hooks, and callback functions.
- Hello Dolly is the minimal example.
- Plugins extend WordPress without modifying core.

## This project

Custom development lives in **`mh-dev-kit`**. WordPress core is installed locally via DDev/WP-CLI and is **not** tracked in git.
