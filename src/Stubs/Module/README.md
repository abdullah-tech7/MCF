# Workflow Language Files

Each Workflow manages its own translation files independently.

After creating a new Workflow, simply create one or more JSON language files inside its folder.

Example:

```
Lang/
├── Profile/
│   ├── ar.json
│   └── en.json
├── UserManagement/
│   ├── ar.json
│   └── en.json
```

Each folder represents a Workflow, and each JSON file represents a language.

During application boot, MCF automatically discovers all Workflow language files and merges them into Laravel's translation loader.

This means you do **not** need to register language files manually. Just create the JSON files and they will become available automatically.

Example:

__('Profile');
__('Save');
__('Delete');


Supported languages depend on the JSON files you create (for example: `ar.json`, `en.json`, `fr.json`, ...).
