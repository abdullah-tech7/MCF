# MCF Language System

MCF provides a modular translation system that allows every Workflow to manage its own language files while remaining fully compatible with Laravel's JSON translation system.

---

# Directory Structure

Each Workflow owns its own `Lang` directory.

Example:

```text
Modules/
└── Users/
    ├── Profile/
    │   └── Lang/
    │       ├── ar.json
    │       └── en.json
    │
    └── UserManagement/
        └── Lang/
            ├── ar.json
            └── en.json
```

Each Workflow manages its own translations independently.

---

# JSON Files

Each language file contains standard Laravel JSON translations.

Example (`ar.json`):

```json
{
    "Profile": "الملف الشخصي",
    "Save": "حفظ",
    "Cancel": "إلغاء"
}
```

Example (`en.json`):

```json
{
    "Profile": "Profile",
    "Save": "Save",
    "Cancel": "Cancel"
}
```

---

# Automatic Discovery

MCF automatically scans every Workflow's `Lang` directory recursively during application boot.

No configuration or manual registration is required.

Simply create the JSON files inside a Workflow's `Lang` directory and MCF will discover them automatically.

---

# Automatic Merge

All discovered JSON translation files are grouped by locale and merged into Laravel's native JSON translation loader.

Translations behave exactly like Laravel's built-in JSON translations.

You can use them normally:

```php
__('Profile');
__('Save');
__('Delete');
```

---

# Duplicate Keys

MCF protects applications from conflicting translations.

If the same translation key exists more than once for the same locale with different values, MCF throws an exception during application boot.

Example (invalid):

Workflow A

```json
{
    "Save": "Save"
}
```

Workflow B

```json
{
    "Save": "Store"
}
```

This produces an exception because the same translation key has two different values.

Identical values are allowed.

Example:

Workflow A

```json
{
    "Save": "Save"
}
```

Workflow B

```json
{
    "Save": "Save"
}
```

Since both values are identical, no conflict exists.

---

# Supported Languages

MCF does not require language registration.

Any locale is supported automatically by creating its corresponding JSON file.

Examples:

```text
ar.json
en.json
fr.json
de.json
es.json
ja.json
```

---

# Laravel Compatibility

MCF extends Laravel's native JSON translation system.

No custom translation API is introduced.

Laravel helpers continue to work normally.

Examples:

```php
__('Profile');

trans('Profile');

@lang('Profile');
```

Existing Laravel localization features continue to function without modification.

---

# Benefits

- Each Workflow owns its own translations.
- Recursive discovery of every Workflow's `Lang` directory.
- Automatic locale grouping.
- Zero manual registration.
- Fully compatible with Laravel JSON translations.
- Duplicate key protection.
- Supports unlimited Modules.
- Supports unlimited Workflows.
- Supports unlimited languages.
- Preserves Laravel's native localization workflow.
