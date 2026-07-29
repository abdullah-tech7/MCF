# Contributing

---

# Overview

Thank you for your interest in contributing to MCF.

MCF is a Workflow-driven framework built on top of Laravel. Its goal is to provide a clean, modular, and predictable architecture while remaining fully compatible with Laravel.

Every contribution should preserve the framework's design philosophy and architectural consistency.

Please read this guide before submitting changes.

---

# Before Contributing

Before opening a Pull Request, ensure that you have:

- Read the project documentation.
- Understood the MCF architecture.
- Followed the Coding Standards.
- Followed the CLI conventions.
- Tested your changes.
- Verified generated output.

Contributions should integrate naturally with the existing framework.

---

# Types of Contributions

MCF welcomes contributions including:

- Bug fixes.
- Performance improvements.
- Documentation improvements.
- Workflow enhancements.
- New Artisan generators.
- Framework improvements.
- Refactoring.
- Test improvements.

Every contribution should provide measurable value to the framework.

---

# Design Philosophy

Every contribution should follow the core principles of MCF.

- Workflow-Driven Architecture
- Single Responsibility
- Modular Design
- Laravel Compatibility
- Predictable Behavior
- Minimal Boilerplate
- Clean Architecture

Avoid introducing features that conflict with these principles.

---

# Workflow-First Development

MCF applications are organized around Workflows.

New features should integrate naturally into the existing Workflow architecture.

Avoid introducing structures that bypass or duplicate the Workflow system.

Business capabilities should remain inside Workflows.

---

# Coding Standards

All submitted code must follow the official MCF Coding Standards.

This includes:

- PSR-12 formatting.
- Laravel coding conventions.
- Consistent naming.
- Proper namespaces.
- Explicit visibility.
- Strong typing where appropriate.
- Readable, maintainable code.

Project consistency is more important than personal coding style.

---

# Generator Contributions

New generators should follow existing MCF conventions.

Requirements:

- One responsibility per generator.
- Predictable output.
- Laravel-compatible implementation.
- Consistent command naming.
- Minimal generated code.
- No unexpected side effects.

Avoid generators that create unrelated components automatically.

---

# Workflow Generator Contributions

If contributing to Workflow generators:

- Follow the existing Workflow structure.
- Preserve generated file naming.
- Preserve generated directory layout.
- Keep generated components consistent across all Workflows.

Generated Workflows should always remain predictable.

---

# Backward Compatibility

Avoid breaking existing applications.

Whenever possible:

- Extend existing behavior.
- Preserve public APIs.
- Preserve CLI behavior.
- Keep generated structures compatible.

Breaking changes should only be introduced when absolutely necessary.

---

# Documentation

Documentation is considered part of every feature.

Whenever framework behavior changes, update the relevant documentation.

Examples include:

- README
- Quick Start
- Architecture
- CLI Specification
- Coding Standards
- Best Practices
- Folder Reference

Implementation and documentation should evolve together.

---

# Pull Requests

Each Pull Request should:

- Focus on a single topic.
- Be easy to review.
- Include a clear description.
- Explain the motivation.
- Avoid unrelated changes.

Small, focused Pull Requests are preferred over large mixed submissions.

---

# Commit Messages

Write descriptive commit messages.

Good examples:

```text
Add Layout Workflow generator

Improve Workflow generation

Refactor module registration

Fix workflow route discovery

Update CLI documentation
```

Avoid vague messages.

Examples:

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
- Generated files are placed in the expected locations.
- Workflow generation behaves correctly.
- Route registration works correctly.
- Existing functionality remains unaffected.

Contributors are responsible for validating their own changes.

---

# Discussions

Major architectural proposals should begin with a discussion before implementation.

This helps:

- Avoid duplicated work.
- Preserve architectural consistency.
- Ensure alignment with the project's direction.

---

# Code Reviews

Contributions may be reviewed for:

- Architecture.
- Workflow consistency.
- Maintainability.
- Readability.
- Laravel compatibility.
- Documentation.
- Coding standards.
- Predictability.

Requested revisions should be addressed before merging.

---

# Code of Conduct

Contributors are expected to communicate respectfully and professionally.

Discussions should focus on improving the framework.

Constructive collaboration is expected throughout the contribution process.

---

# Thank You

Every contribution helps improve MCF.

Whether you contribute:

- Bug fixes.
- Documentation.
- New generators.
- Performance improvements.
- Architectural enhancements.
- Tests.

your effort helps make MCF a more reliable and maintainable framework.

Thank you for contributing to MCF.