# Structure

CRUDL bundle provides a set of classes that allows to create a CRUD+List controller for any entity.

It's mainly based on a Controller and a Manager.

## Manager

The Manager will take care of entity management, acting as a factory of entities and doing the doctrine calls.

Managers must implement *Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface* interface.

For more info about managers, check [Manager](docs/3_manager.md) section.

## Controller

The CRUDL controller performs the following actions:

- Create
- Read
- Update
- Delete
- List

None of those actions are required, you will be able to enable just one or more of them.

## Forms

Controller actions (all except read action) requires a form to work. They could be any AbstractType form.

## Events

Every CRUDL action dispatches a lot of events, that allows to extend functionality, checking
values, security, adding view data, or anything we need to do into the action flow.

For example, create action dispatches following events:

- initialize event: before doing anything after creating a new entity
- form_init event: after form creation
- form_valid event: on successful submit and before performing flush
- success event: on successful submit and after performing flush
- form_invalid event: on failure submit
- view event: after everything has been processed, and before creating view

Each of those events, dispatch an object of next classes:

- Softspring\Component\Events\FormEvent
- Softspring\Component\Events\GetResponseRequestEvent
- Softspring\Component\Events\ViewEvent
- Softspring\Component\CrudlController\Event\FilterEvent
- Softspring\Component\CrudlController\Event\GetResponseEntityEvent
- Softspring\Component\CrudlController\Event\GetResponseEntityExceptionEvent
- Softspring\Component\CrudlController\Event\GetResponseFormEvent