## Petro_App Structure

### Initialization Steps

### CRUD actions structure

#### action_index()
- initialize `Petro_Grid`
- call `setup_index`
- create `action_items`
- set template content with grid rendered output

#### action_view()
- call model's `find` method
- check returned data
- render __attribute table__ in the panel
- call `setup_view`
- create `action_items`
- set template content output

#### action_create()
on get
- setup the form
- set rendering sequence using `form_columns` properties if exists
- set page title
- set template content with form rendered output

on post
- get posted inputs
- call `before_insert`
- forge data from posted input
- save the data
- call `after_insert`
- handle error

#### action_edit()
- retrieve data record using model's `find` method
- setup the form

on get
- set rendering sequence using `form_columns` properties if exists
- set page title
- set template content with form rendered output

on post
- validate the posted inputs
- gather posted inputs
- call `before_update`
- save the data
- call `after_update`
- handle error

#### action_delete()
- find the given data record using model's `find` method
- call `before_delete`
- if returns false, cancel the action
- delete the record
- call `after_delete`
