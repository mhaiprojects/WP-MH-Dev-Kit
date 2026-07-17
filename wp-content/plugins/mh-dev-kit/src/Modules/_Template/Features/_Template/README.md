# Module template

Copy `src/Modules/_Template/` to `src/Modules/YourModule/` and rename classes.

Each module should include:

- `YourModule.php` — implements `ModuleInterface`; optionally `ModuleAdminContributorInterface` for MHDK Settings subpages
- `Features/` — one or more features (each with Models, Presenters, Views, Assets)
- `README.md` — module documentation

Register the module class in `config/modules.php`.

Features implement `FeatureInterface` only — admin subpages belong on the module.
