# MCF Language

## Overview

The **Language** module in MCF provides a centralized translation layer
for the project.

The concept is simple:

- Each language has **one JSON file only**.
- Language files are stored inside the `Language` directory.
- MCF automatically discovers and loads the language files.
- The translations are passed into Laravel’s translation system.
- The translation key is the **original text itself**, not an artificial
  key such as `auth.login_success`.
- The language file can optionally be organized using Section Markers.

The goal is to prevent translation files from being scattered across
Models, Workflows, and Modules while keeping one central source for each
language.

------------------------------------------------------------------------

## Language File Location

MCF ships with:

``` text
MCF/
└── Language/
    └── ar.json
```

Additional languages can be added:

``` text
MCF/
└── Language/
    ├── ar.json
    ├── en.json
    └── fr.json
```

### Rule

**Each language has exactly one JSON file.**

For example:

``` text
ar.json  → Arabic
en.json  → English
fr.json  → French
```

The system does not use:

``` text
ar/
    auth.json
    audit.json

en/
    auth.json
    audit.json
```

and it does not create a translation file for every Workflow.

------------------------------------------------------------------------

# Language File

The language file uses standard JSON, with the original text as the key:

``` json
{
    "Login successful.": "تم تسجيل الدخول بنجاح.",
    "Invalid credentials.": "بيانات الدخول غير صحيحة.",
    "The user updated the password.": "قام المستخدم بتحديث كلمة المرور."
}
```

Here:

``` text
Key   = Original text
Value = Translation
```

This allows the project to use Laravel’s conventional translation
approach based on the original text as the key.

------------------------------------------------------------------------

# Organizing the File with Section Markers

Developers can optionally organize the JSON file with sections:

``` json
{
    "--- User | Authentication ---": "----------------------------------------",

    "Login successful.": "تم تسجيل الدخول بنجاح.",
    "Invalid credentials.": "بيانات الدخول غير صحيحة.",


    "--- User | Profile ---": "----------------------------------------",

    "The user updated the profile.": "تم تحديث الملف الشخصي.",


    "--- Audit ---": "----------------------------------------",

    "The user updated the password.": "قام المستخدم بتحديث كلمة المرور.",


    "--- Mail ---": "----------------------------------------",

    "Email sent successfully.": "تم إرسال البريد الإلكتروني بنجاح."
}
```

These sections are **completely optional**.

A simple file is also valid:

``` json
{
    "Login successful.": "تم تسجيل الدخول بنجاح.",
    "Invalid credentials.": "بيانات الدخول غير صحيحة."
}
```

------------------------------------------------------------------------

# Why Use Section Markers?

A Section Marker is not a real translation key.

Its purpose is **file organization only**.

Examples:

``` text
--- User | Authentication ---
--- User | Profile ---
--- Audit ---
--- Mail ---
--- SMS ---
```

This makes it easier to identify and search for the source of a
translation in a large language file.

For example:

``` json
"--- User | Profile ---": "----------------------------------------",

"The user updated the profile.": "تم تحديث الملف الشخصي.",
"The user changed the email.": "تم تغيير البريد الإلكتروني."
```

The developer can immediately see that these translations belong to:

``` text
User | Profile
```

------------------------------------------------------------------------

# Section Markers Are Optional

Developers are not required to add a Section Marker for every group.

They may:

- Use sections.
- Remove sections.
- Reorder sections.
- Add new sections.
- Leave the file without sections.

The Language Loader handles them automatically.

------------------------------------------------------------------------

# How the Language Loader Works

When MCF starts, `TranslationLoader` looks for:

``` text
MCF/Language/*.json
```

It then reads every language file.

The filename determines the Locale:

``` text
ar.json → ar
en.json → en
fr.json → fr
```

The contents are then converted into the translation collection used by
Laravel.

------------------------------------------------------------------------

# Section Markers During Loading

If the Loader finds:

``` json
"--- User | Profile ---": "----------------------------------------"
```

it recognizes it as a Section Marker and ignores it when registering
translation keys.

Therefore:

``` text
--- User | Profile ---
```

does not become an actual translation key.

The internal result becomes:

``` php
[
    'ar' => [
        'Login successful.' => 'تم تسجيل الدخول بنجاح.',
        'Invalid credentials.' => 'بيانات الدخول غير صحيحة.',
    ],
]
```

while the JSON file remains organized for developers.

------------------------------------------------------------------------

# Avoiding Duplicate Translation Keys

One of the main goals of this design is to keep each original text as a
single translation key.

Instead of:

``` text
auth.login_success
profile.login_success
dashboard.login_success
```

the project uses:

``` text
Login successful.
```

once.

If multiple parts of the project need the same text, they use the same
key.

This reduces:

- Duplicate keys.
- Duplicate translations.
- Inconsistent translations for the same phrase.
- Search and maintenance overhead.

------------------------------------------------------------------------

# Adding a New Language

To add another language, create one language file:

``` text
MCF/
└── Language/
    ├── ar.json
    └── en.json
```

Example:

``` json
{
    "Login successful.": "Login successful.",
    "Invalid credentials.": "Invalid credentials."
}
```

Once `en.json` is placed in the `Language` directory, the Loader
discovers it automatically.

No translation files need to be created inside individual Workflows.

------------------------------------------------------------------------

# Relationship with Laravel

MCF does not create a separate translation system instead of Laravel.

The responsibility of `TranslationLoader` is:

``` text
Language JSON files
        ↓
MCF TranslationLoader
        ↓
Laravel Translation System
        ↓
Laravel Application
```

Different MCF modules can therefore use Laravel’s translation system
while MCF handles the discovery and centralized loading of language
files.

------------------------------------------------------------------------

# Translation Responsibility

Translation is the responsibility of **Language**.

Other modules only define the text they need.

For example, Audit can use:

``` php
__('The user updated the password.')
```

Audit does not need to know about:

``` text
ar.json
en.json
Language folder
```

The same applies to Mail, Authentication, AccessControl, Notifications,
and other modules.

------------------------------------------------------------------------

# Complete Example

``` text
MCF/
├── Audit/
├── Authentication/
├── AccessControl/
├── Mail/
├── Notifications/
├── SMS/
├── Language/
│   └── ar.json
└── ...
```

`ar.json`:

``` json
{
    "--- User | Authentication ---": "----------------------------------------",

    "Login successful.": "تم تسجيل الدخول بنجاح.",
    "Invalid credentials.": "بيانات الدخول غير صحيحة.",


    "--- User | Profile ---": "----------------------------------------",

    "The user updated the profile.": "تم تحديث الملف الشخصي.",


    "--- Audit ---": "----------------------------------------",

    "The user updated the password.": "قام المستخدم بتحديث كلمة المرور.",


    "--- Mail ---": "----------------------------------------",

    "Email sent successfully.": "تم إرسال البريد الإلكتروني بنجاح."
}
```

------------------------------------------------------------------------

# Design Rules

1.  Each language has one JSON file.
2.  Language files are stored in `Language`.
3.  The filename represents the Locale.
4.  The translation key is the original text.
5.  The value is the translation.
6.  Section Markers are optional.
7.  Section Markers are used for organization only.
8.  `Model | Workflow` can be used for Workflow-specific sections.
9.  A module name alone can be used for general sections.
10. A translation file must not be created for every Workflow.
11. MCF automatically loads language files into Laravel’s translation
    system.
12. A new language can be added by creating one JSON file for that
    language.

------------------------------------------------------------------------

# Architectural Goal

The Language design aims for:

``` text
One project
     ↓
One central language directory
     ↓
One JSON file per language
     ↓
One translation key per original text
     ↓
Laravel Translation System
```

This keeps translations centralized, searchable, and easy to maintain
without scattering language files throughout the project.
