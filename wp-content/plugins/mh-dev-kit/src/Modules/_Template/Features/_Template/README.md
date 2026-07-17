# Module template

Copy `src/Modules/_Template/` to `src/Modules/YourModule/` and rename classes.

Each module should include:

- `YourModule.php` — implements `ModuleInterface`; optionally `ModuleAdminContributorInterface` for MHDK Settings subpages
- `Features/` — one or more features (each with Models, Presenters, Views, Assets)
- `README.md` — module documentation

Register the module class in `config/modules.php`.

Features implement `FeatureInterface` only — admin subpages belong on the module.

## Admin settings pattern

See `Features/_Template/Presenters/TemplateSettingsPresenter.php` and `Core/Views/admin/partials/settings-page.php`:

1. `register_setting()` + `add_settings_section()` / `add_settings_field()` in the presenter
2. Field callbacks use `Core\Admin\SettingsFieldRenderer`
3. Render via the shared settings partial (`settings_fields` group must match `register_setting` group)
4. List pages use `wp-list-table` or `Core\Admin\ModulesListTable`
5. Meta boxes use `table.form-table` with `th scope="row"`

Reference implementation: `Modules/Core/Features/MhdkPoc/Presenters/PocSettingsPresenter.php`.
