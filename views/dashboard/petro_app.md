# Petro App

- Extend Petro\Controller_App
- That's it! Check it out!!

### Petro App Controller

Petro App relies on your Model and other Petro's component to automatically make an app
out of it.

In Petro App, once you have defined your Model (by extending it from Orm\Model), it will do the rest by
creating 4 pages and a delete action for your app. You don't even have to write it yourself!

Those 4 pages are
 - index page - showing the list of data from your Model with links to other pages
 - view - showing basic information of a selected record from your Model
 - new - showing a form to create a new record for your Model
 - edit - shwoing a form allowing you to make some updates and save it

You can even add some more methods to it, after all it is just another Controller class.

### "But can I update each of those page the way I want?", you may ask. 

#### _Of course, you can._

The following methods have been predefined inside Petro's Controller class:
 - `action_index()`
 - `action_view()`
 - `action_create()`
 - `action_edit()`
 - `action_delete()`

But each of these methods will eventually call a predefined callback method allowing you
to write only minimal code to change its page content, which are:
 - `setup_index()` -- will be called by `action_index()`
 - `setup_view()` -- will be called by `action_view()`
 - `create_new()` -- will be called by `action_create()`
 - `edit_update()` -- will be called by `action_edit()`

### "Why do you define those methods?"

Well, if you have been programming for quite sometime, you may have already known that most of the time it
is about keeping data for processing it later. The data will usually going into some kind of database table
and for each table, you will usually need Create-Read-Update-Delete (CRUD) operations.

Petro App has been written for that purpose. You define your table structure, and it figure out the rest
via a certain logic.

### "It is predefined, but will it do the data validation as well?"

#### _Yes, it does_ .... _if you defined validation rules inside your model!

### "If those predefine callback methods are not enough for me?"

#### _As I told you, you can add as many methods as you like._

### "No, I mean ... I want to put something more on the View page, but `setup_view` method does not cover it all"

#### _Ahh, I see... Well, you just have to override `action_view` method and do whatever you want._

Just remember that when you override the predefine method, the related callback will not be called automatically,
you have to call it manually. Also, some interface elements, like `action_items` will not be displayed automatically 
as well. But you can always do it manually. Just follows instructions on another section.

"Authentication, suport?"
- $must_login
- config `auth`