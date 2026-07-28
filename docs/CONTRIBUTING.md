# Contributing

## Overview

Thank you for your interest in contributing to MCF.

MCF aims to remain a clean, modular, and Laravel-compatible framework. Every contribution should align with the project's architecture and design philosophy.

Please read this document before submitting changes.

---

# Before Contributing

Before opening a Pull Request, ensure that you have:

- Read the project documentation.
- Understood the architecture.
- Followed the coding standards.
- Reviewed the generator rules.
- Tested your changes.

---

# Types of Contributions

Contributions may include:

- Bug fixes
- Performance improvements
- Documentation improvements
- New generators
- Framework enhancements
- Refactoring
- Test improvements

Every contribution should provide clear value to the framework.

---

# Design Philosophy

All contributions should respect the core principles of MCF.

- Single Responsibility
- Modular Design
- Laravel Compatibility
- Predictable Behavior
- Minimal Boilerplate
- Clean Architecture

Do not introduce features that conflict with these principles.

---

# Coding Standards

All submitted code must follow the project's coding standards.

In particular:

- PSR-12 formatting
- Consistent naming
- Proper namespaces
- Explicit visibility
- Strong typing where appropriate
- Clear, readable code

Consistency is more important than personal style preferences.

---

# Generator Contributions

New generators should follow the existing conventions.

Requirements:

- One responsibility per generator.
- Predictable output location.
- Laravel-compatible implementation.
- Consistent command naming.
- Minimal generated code.

Avoid generators that create large collections of unrelated files.

---

# Backward Compatibility

Avoid breaking existing applications.

Whenever possible:

- Extend existing behavior.
- Preserve public APIs.
- Keep command behavior stable.

Breaking changes should be introduced only when necessary.

---

# Documentation

Any feature that changes framework behavior should include documentation updates.

Relevant documentation should be updated alongside the implementation.

Examples include:

- README
- Quick Start
- CLI Specification
- Architecture
- Folder Reference

Documentation is considered part of the feature.

---

# Pull Requests

A Pull Request should:

- Focus on a single topic.
- Be easy to review.
- Include a clear description.
- Explain the motivation.
- Avoid unrelated changes.

Large mixed Pull Requests are discouraged.

---

# Commit Messages

Write clear and descriptive commit messages.

Good examples:

```text
Add notification generator

Improve workflow generation

Refactor module creation

Fix migration namespace
```

Avoid vague messages such as:

```text
Update

Fix

Changes

Stuff
```

---

# Testing

Before submitting a Pull Request, verify that:

- The framework builds successfully.
- All Artisan commands execute correctly.
- Generated files are placed in the correct directories.
- Existing functionality remains unaffected.

Contributors are responsible for validating their own changes.

---

# Discussions

For major architectural changes, open a discussion before beginning implementation.

This helps avoid duplicated work and ensures that the proposed solution aligns with the project's direction.

---

# Code Reviews

Every contribution may be reviewed for:

- Architecture
- Maintainability
- Readability
- Compatibility
- Documentation
- Consistency

Requested revisions should be addressed before merging.

---

# Code of Conduct

Contributors are expected to communicate respectfully and constructively.

Technical discussions should focus on improving the framework.

Professionalism and collaboration are expected throughout the contribution process.

---

# Thank You

Every contribution, whether it is a bug report, documentation improvement, code enhancement, or new feature, helps improve MCF.

Thank you for contributing to the project.