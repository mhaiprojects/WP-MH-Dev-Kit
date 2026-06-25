# Feature template

Copy `src/Features/_Template/` to `src/Features/YourFeature/` and rename classes.

Each feature should include:

- `YourFeatureFeature.php` — implements `FeatureInterface`
- `Models/` — data access
- `Presenters/` — hooks and coordination
- `Views/` — PHP templates
- `Assets/` — css, js, block.json folders
- `README.md` — feature documentation

Register the feature class in `config/features.php`.
