
# MCF Language System

MCF provides a modular translation system that allows every Workflow to manage its own language files while remaining fully compatible with Laravel's JSON translation system.

## Directory Structure

After creating a Workflow, simply create a folder with the Workflow name inside `Lang`, then add one or more JSON language files.

Example:

```text
Modules/
└── Users/
    └── Lang/
        ├── Profile/
        │   ├── ar.json
        │   └── en.json
        │
        └── UserManagement/
            ├── ar.json
            └── en.json
```

Each Workflow owns its own translations independently.

---

## JSON Files

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

## Automatic Discovery

MCF automatically scans every module and every Workflow under the `Lang` directory.

All JSON files are discovered recursively and grouped by locale before the application starts. :contentReference[oaicite:0]{index=0}

No configuration or registration is required.

Simply create the JSON files and MCF will load them automatically.

---

## Automatic Merge

All discovered translations are merged into Laravel's JSON translation loader.

This allows translations from every Workflow to behave exactly like native Laravel JSON language files. :contentReference[oaicite:1]{index=1}

You can use them normally:

```php
__('Profile');
__('Save');
__('Delete');
```

---

## Duplicate Keys

MCF prevents conflicting translations.

If the same translation key exists more than once for the same locale with different values, an exception is thrown during application boot to prevent unexpected behavior. :contentReference[oaicite:2]{index=2}

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

This will generate an exception because the same key has two different values.

---

## Supported Languages

MCF does not require language registration.

Any locale is supported automatically by creating its JSON file.

Examples:

```
ar.json
en.json
fr.json
de.json
es.json
ja.json
```

---

## Benefits

- Modular translations for every Workflow.
- Automatic Workflow discovery.
- Recursive language scanning.
- Zero manual registration.
- Fully compatible with Laravel JSON translations.
- Duplicate key protection.
- Supports unlimited modules, workflows and languages.
````
