# Crudl Controller Features

Functional definition for `softspring/crudl-controller`.

This file defines the expected behavior and scope of the component.

## Purpose

- Provide a reusable controller base for CRUD and list screens in Symfony applications.
- Keep create, read, update, delete, list, apply, and transition flows consistent across entities.
- Expose controller workflows through configuration, managers, forms, and events instead of duplicating controller code in every project.

## Main Features

- Reusable controller actions for create, read, update, delete, list, apply, and transition flows.
- Action-specific configuration objects for each supported workflow.
- Entity manager abstraction for entity creation, persistence, deletion, and repository access.
- Form-based workflows for create, update, delete, and apply actions.
- List integration based on `doctrine-query-filters` and `doctrine-paginator`.
- Event hooks across the full action lifecycle.
- Helper classes to keep action flow logic separated from the controller entry points.

## Expected Usage

- Use `CrudlController` as a Symfony service and expose only the actions a project needs.
- Provide a manager implementing `CrudlEntityManagerInterface`.
- Configure each enabled action through the controller `$configs` array.
- Use events to customize steps such as entity loading, form preparation, apply logic, success handling, and view data.
- Use the list action together with a filter form when backoffice screens need filtering, sorting, and pagination.

## Operational Expectations

- Actions should fail early when required configuration is missing.
- The default action flow should be usable without subclassing the controller.
- Projects should be able to override behavior through events without forking the base controller.
- Managers should support both direct entity classes and Doctrine target entities resolved from interfaces.

## Current Limits

- The component is designed for server-rendered Symfony CRUD and list screens, not for API-first resource controllers.
- The list action assumes integration with the Softspring filter and paginator components.
- Some action flows depend on configuration discipline; unsupported combinations should be treated as integration errors.
