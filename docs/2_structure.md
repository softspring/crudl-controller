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
- Apply

None of those actions are required, you will be able to enable just one or more of them.

## Forms

Controller actions (all except read action) requires a form to work. They could be any AbstractType form.

## Events

Every CRUDL action dispatches a lot of events, that allows to extend functionality, checking
values, security, adding view data, or anything you need to do into the action flow.

For example, create action dispatches following events:

- initialize_event_name
- create_entity_event_name
- form_prepare_event_name
- form_init_event_name
- form_valid_event_name
- apply_event_name
- success_event_name
- failure_event_name
- form_invalid_event_name
- view_event_name
- exception_event_name

For more details about events, check actions sections and [Events](docs/5_events.md) documentation.

Most events are dispatched with a *Softspring\Component\Events\GetResponseEventInterface* event object, 
 that allows to set a response to be returned by the controller, breaking the action flow.

