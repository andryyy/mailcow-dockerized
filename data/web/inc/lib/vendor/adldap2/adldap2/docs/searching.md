# Searching

## Introduction

Using the Adldap2 query builder makes building LDAP queries feel effortless.

It allows you to generate LDAP filters using a fluent and
convenient interface, similar to Eloquent in Laravel.

> **Note:** The Adldap2 query builder escapes all fields & values
> given to its `where()` methods. There is no need to clean or
> escape strings before passing them into the query builder.

## Creating a new Query

To create a new search query, call the `search()` method on your connection provider instance:

```php
$search = $provider->search();
```

Or you can chain all your methods if you'd prefer:

```php
$results = $provider->search()->where('cn', '=', 'John Doe')->get();
```

## Selects

> **Note:** Fields are case in-sensitive. For example, you can
> insert `CN`, `cn` or `cN`, they will return the same result.

#### Selecting attributes

Selecting only the LDAP attributes you need will increase the speed of your queries.

```php
// Passing in an array of attributes
$search->select(['cn', 'samaccountname', 'telephone', 'mail']);

// Passing in each attribute as an argument
$search->select('cn', 'samaccountname', 'telephone', 'mail');
```

## Executing Searches

#### Finding a specific record

If you're trying to find a single record, but not sure what the record might be, use the `find()` method:

```php
$record = $search->find('John Doe');

if ($record) {
    // Record was found!    
} else {
    // Hmm, looks like we couldn't find anything...
}
```

> **Note**: Using the `find()` method will search for LDAP records using ANR
> (ambiguous name resolution) and return the first result.
>
> Since ActiveDirectory is the only LDAP distribution that supports ANR,
> an equivalent query will be created for other LDAP distributions
> that are not compatible.
>
> For a more fine-tuned search, use the `findBy()` method below.

##### Finding a record (or failing)

If you'd like to try and find a single record and throw an exception when it hasn't been
found, use the `findOrFail()` method:

```php
try {

    $record = $search->findOrFail('John Doe');

} catch (Adldap\Models\ModelNotFoundException $e) {
    // Record wasn't found!
}
```

#### Finding a record by a specific attribute

If you're looking for a single record with a specific attribute, use the `findBy()` method:

```php
// We're looking for a record with the 'samaccountname' of 'jdoe'.
$record = $search->findBy('samaccountname', 'jdoe');
```

##### Finding a record by a specific attribute (or failing)

If you'd like to try and find a single record by a specific attribute and throw
an exception when it cannot be found, use the `findByOrFail()` method:

```php
try {

    $record = $search->findByOrFail('samaccountname', 'jdoe');

} catch (Adldap\Models\ModelNotFoundException $e) {
    // Record wasn't found!
}
```

#### Finding a record by its distinguished name

If you're looking for a single record with a specific DN, use the `findByDn()` method:

```php
$record = $search->findByDn('cn=John Doe,dc=corp,dc=org');
```

###### Finding a record by its distinguished name (or failing)

If you'd like to try and find a single record by a specific DN and throw
an exception when it hasn't been found, use the `findByDnOrFail()` method:

```php
try {

    $record = $search->findByDnOrFail('cn=John Doe,dc=corp,dc=org');

} catch (Adldap\Models\ModelNotFoundException $e) {
    // Record wasn't found!
}
```

#### Retrieving results

To get the results from a search, simply call the `get()` method:

```php
$results = $search->select(['cn', 'samaccountname'])->get();
```

> **Note**: Executed searches via the `get()` method will return them inside an
> `Illuminate\Support\Collection` instance (a glorified array), with allows
> you to utilize [some extremely handy methods](https://laravel.com/docs/collections).
>
> Executed searches via the `first()` method will return **a model instance only**.

##### Retrieving the first record

To retrieve the first record of a search, call the `first()` method:

```php
$record = $search->first();
```

> **Note**: If you are using `sortBy()`, calling `first()` will not take this into account. Sorts
> are performed **after** retrieving query results. If you would like the first record of
> a sorted result set, call `first()` on a `Collection` of returned models.

###### Retrieving the first record (or failing)

To retrieve the first record of a search or throw an exception when one isn't found, call the `firstOrFail()` method:

```php
try {

    $record = $search->firstOrFail();

} catch (Adldap\Models\ModelNotFoundException $e) {
    // Record wasn't found!
}
```

## Limit

To limit the results records returned from your LDAP server and increase the
speed of your queries, you can use the `limit()` method:

```php
// This will only return 5 records that contain the name of 'John':
$records = $search->where('cn', 'contains', 'John')->limit(5)->get();
```

## Wheres

To perform a where clause on the search object, use the `where()` function:

```php
$search->where('cn', '=', 'John Doe');
```

This query would look for a record with the common name of 'John Doe' and return the results.

We can also perform a 'where equals' without including the operator:

```php
$search->whereEquals('cn', 'John Doe');
```

We can also supply an array of key - value pairs to quickly add multiple wheres:

```php
$wheres = [
    'cn' => 'John Doe',
    'samaccountname' => 'jdoe',
];

$search->where($wheres);
```

Or, if you require conditionals, you can quickly add multiple wheres with nested arrays:

```php
$search->where([
   ['cn', '=', 'John Doe'],
   ['manager', '!', 'Suzy Doe'],
]);
```

#### Where Starts With

We could also perform a search for all objects beginning with the common name of 'John' using the `starts_with` operator:

```php
$results = $provider->search()->where('cn', 'starts_with', 'John')->get();

// Or use the method whereStartsWith($attribute, $value):

$results = $provider->search()->whereStartsWith('cn', 'John')->get();
```

#### Where Ends With

We can also search for all objects that end with the common name of `Doe` using the `ends_with` operator:

```php
$results = $provider->search()->where('cn', 'ends_with', 'Doe')->get();

// Or use the method whereEndsWith($attribute, $value):

$results = $provider->search()->whereEndsWith('cn', 'Doe')->get();
```

#### Where Between

To search for records between two values, use the `whereBetween` method.

For the example below, we'll retrieve all users who were created between two dates:

```php
$from = (new DateTime('October 1st 2016'))->format('YmdHis.0\Z');
$to = (new DateTime('January 1st 2017'))->format('YmdHis.0\Z');

$users = $provider->search()
    ->users()
    ->whereBetween('whencreated', [$from, $to])
    ->get();
```

#### Where Contains

We can also search for all objects with a common name that contains `John Doe` using the `contains` operator:

```php
$results = $provider->search()->where('cn', 'contains', 'John Doe')->get();

// Or use the method whereContains($attribute, $value):

$results = $provider->search()->whereContains('cn', 'John Doe')->get();
```

##### Where Not Contains

You can use a 'where not contains' to perform the inverse of a 'where contains':

```php
$results = $provider->search()->where('cn', 'not_contains', 'John Doe')->get();

// Or use the method whereNotContains($attribute, $value):

$results = $provider->search()->whereNotContains('cn', 'John Doe');
```

#### Where Has

Or we can retrieve all objects that have a common name attribute using the wildcard operator (`*`):

```php
$results = $provider->search()->where('cn', '*')->get();

// Or use the method whereHas($field):

$results = $provider->search()->whereHas('cn')->get();
```

This type of filter syntax allows you to clearly see what your searching for.

##### Where Not Has

You can use a 'where not has' to perform the inverse of a 'where has':

```php
$results = $provider->search->where('cn', '!*')->get();

// Or use the method whereNotHas($field):

$results = $provider->search()->whereNotHas($field)->get();
```

## Or Wheres

To perform an `or where` clause on the search object, use the `orWhere()` method. However,
please be aware this function performs differently than it would on a database.

For example:

```php
$results = $search
            ->where('cn', '=', 'John Doe')
            ->orWhere('cn', '=', 'Suzy Doe')
            ->get();
```

This query would return no results. Since we're already defining that the common name (`cn`) must equal `John Doe`, applying
the `orWhere()` does not amount to 'Look for an object with the common name as "John Doe" OR "Suzy Doe"'. This query would
actually amount to 'Look for an object with the common name that <b>equals</b> "John Doe" OR "Suzy Doe"

To solve the above problem, we would use `orWhere()` for both fields. For example:

```php
$results = $search
        ->orWhere('cn', '=', 'John Doe')
        ->orWhere('cn', '=', 'Suzy Doe')
        ->get();
```

Now, we'll retrieve both John and Suzy's LDAP records, because the common name can equal either.

> **Note**: You can also use all `where` methods as an or where, for example:
> `orWhereHas()`, `orWhereContains()`, `orWhereStartsWith()`, `orWhereEndsWith()`

## Dynamic Wheres

To perform a dynamic where, simply suffix a `where` with the field you're looking for.

This feature was directly ported from Laravel's Eloquent.

Here's an example:

```php
// This query:
$result = $search->where('cn', '=', 'John Doe')->first();

// Can be converted to:
$result = $search->whereCn('John Doe')->first();
```

You can perform this on **any** attribute:

```php
$result = $search->whereTelephonenumber('555-555-5555')->first();
```

You can also chain them:

```php
$result = $search
    ->whereTelephonenumber('555-555-5555')
    ->whereGivenname('John Doe')
    ->whereSn('Doe')
    ->first();
```

You can even perform multiple dynamic wheres by separating your fields by an `And`:

```php
// This would perform a search for a user with the
// first name of 'John' and last name of 'Doe'.
$result = $search->whereGivennameAndSn('John', 'Doe')->first();
```

## Nested Filters

By default, the Adldap2 query builder automatically wraps your queries in `and` / `or` filters for you.
However, if any further complexity is required, nested filters allow you
to construct any query fluently and easily.

#### andFilter

The `andFilter` method accepts a closure which allows you to construct a query inside of an `and` LDAP filter:

```php
$query = $provider->search()->newQuery();

// Creates the filter: (&(givenname=John)(sn=Doe))
$results = $query->andFilter(function (Adldap\Query\Builder $q) {

    $q->where('givenname', '=', 'John')
      ->where('sn', '=', 'Doe');

})->get();
```

The above query would return records that contain the first name `John` **and** the last name `Doe`.

#### orFilter

The `orFilter` method accepts a closure which allows you to construct a query inside of an `or` LDAP filter:

```php
$query = $provider->search()->newQuery();


// Creates the filter: (|(givenname=John)(sn=Doe))
$results = $query->orFilter(function (Adldap\Query\Builder $q) {

    $q->where('givenname', '=', 'John')
      ->where('sn', '=', 'Doe');

})->get();
```

The above query would return records that contain the first name `John` **or** the last name `Doe`.

#### notFilter

The `notFilter` method accepts a closure which allows you to construct a query inside a `not` LDAP filter:

```php
$query = $provider->search()->newQuery();

// Creates the filter: (!(givenname=John)(sn=Doe))
$results = $query->notFilter(function (Adldap\Query\Builder $q) {

    $q->where('givenname', '=', 'John')
      ->where('sn', '=', 'Doe');

})->get();
```

The above query would return records that **do not** contain the first name `John` **or** the last name `Doe`.

#### Complex Nesting

The above methods `andFilter` / `orFilter` can be chained together and nested
as many times as you'd like for larger complex queries:

```php
$query = $provider->search()->newQuery();

$query = $query->orFilter(function (Adldap\Query\Builder $q) {
    $q->where('givenname', '=', 'John')->where('sn', '=', 'Doe');
})->andFilter(function (Adldap\Query\Builder $q) {
    $q->where('department', '=', 'Accounting')->where('title', '=', 'Manager');
})->getUnescapedQuery();

echo $query; // Returns '(&(|(givenname=John)(sn=Doe))(&(department=Accounting)(title=Manager)))'
```

## Raw Filters

> **Note**: Raw filters are not escaped. **Do not** accept user input into the raw filter method.

Sometimes you might just want to add a raw filter without using the query builder.
You can do so by using the `rawFilter()` method:

```php
$filter = '(samaccountname=jdoe)';

$results = $search->rawFilter($filter)->get();

// Or use an array
$filters = [
    '(samaccountname=jdoe)',
    '(surname=Doe)',
];

$results = $search->rawFilter($filters)->get();

// Or use multiple arguments
$results = $search->rawFilter($filters[0], $filters[1])->get();

// Multiple raw filters will be automatically wrapped into an `and` filter:
$query = $search->getUnescapedQuery();

echo $query; // Returns (&(samaccountname=jdoe)(surname=Doe))
```

## Sorting

Sorting is really useful when your displaying tabular LDAP results. You can
easily perform sorts on any LDAP attribute by using the `sortBy()` method:

```php
$results = $search->whereHas('cn')->sortBy('cn', 'asc')->get();
```

You can also sort paginated results:

```php
$results = $search->whereHas('cn')->sortBy('cn', 'asc')->paginate(25);
```

> **Note**: Sorting occurs *after* results are returned. This is due
> to PHP not having the functionality of sorting records on
> the server side before they are returned.

## Paginating

Paginating your search results will allow you to return more results than
your LDAP cap (usually 1000) and display your results in pages.

> **Note**: Calling `paginate()` will retrieve **all** records from your LDAP server for the current query.
>
> This **does not** operate the same way pagination occurs in a database. Pagination of
> an LDAP query simply allows you to return a larger result set than your
> LDAP servers configured maximum (usually 1000).
>
> The pagination object is simply a collection that allows you to iterate
> through all the resulting records easily and intuitively.

To perform this, call the `paginate()` method instead of the `get()` method:

```php
$recordsPerPage = 50;

$currentPage = $_GET['page'];

// This would retrieve all records from your LDAP server inside a new Adldap\Objects\Paginator instance.
$paginator = $search->paginate($recordsPerPage, $currentPage);

// Returns total number of pages, int
$paginator->getPages();

// Returns current page number, int
$paginator->getCurrentPage();

// Returns the amount of entries allowed per page, int
$paginator->getPerPage();

// Returns all of the results in the entire paginated result
$paginator->getResults();

// Returns the total amount of retrieved entries, int
$paginator->count();

// Iterate over the results like normal
foreach($paginator as $result)
{
    echo $result->getCommonName();
}
```

## Scopes

Search scopes allow you to easily retrieve common models of a particular 'scope'.

Each scope simply applies the required filters to the search object
that (when executed) will only return the relevant models.

Here is a list of all available scopes:

```php
// Retrieve all users (Adldap\Models\User).
$results = $search->users()->get();

// Retrieve all printers (Adldap\Models\Printer).
$results = $search->printers()->get();

// Retrieve all organizational units (Adldap\Models\OrganizationalUnit).
$results = $search->ous()->get();

// Retrieve all organizational units (Adldap\Models\OrganizationalUnit).
$results = $search->organizations()->get();

// Retrieve all groups (Adldap\Models\Group).
$results = $search->groups()->get();

// Retrieve all containers (Adldap\Models\Container).
$results = $search->containers()->get();

// Retrieve all contacts (Adldap\Models\Contact).
$results = $search->contacts()->get();

// Retrieve all computers (Adldap\Models\Computer).
$results = $search->computers()->get();
```

## Base DN

To set the base DN of your search you can use one of two methods:

```php
// Using the `in()` method:
$results = $provider->search()->in('ou=Accounting,dc=acme,dc=org')->get();

// Using the `setDn()` method:
$results = $provider->search()->setDn('ou=Accounting,dc=acme,dc=org')->get();

// You can also include `in()` with the scope
$results = $provider->search()->organizations()->in('ou=Accounting,dc=acme,dc=org')->get()

```

Either option will return the same results. Use which ever method you prefer to be more readable.

## Search Options

#### Recursive

By default, all searches performed are recursive.

If you'd like to disable recursive search and perform a single level search, use the `listing()` method:

```php
$result = $provider->search()->listing()->get();
```

This would perform an `ldap_listing()` instead of an `ldap_search()`.

#### Read

If you'd like to perform a read instead of a listing or a recursive search, use the `read()` method:

```php
$result = $provider->search()->read()->where('objectClass', '*')->get();
```

This would perform an `ldap_read()` instead of an `ldap_listing()` or an `ldap_search()`.

> **Note**: Performing a `read()` will always return *one* record in your result.

#### Raw

If you'd like to retrieve the raw LDAP results, use the `raw()` method:

```php
$rawResults = $provider->search()->raw()->where('cn', '=', 'John Doe')->get();

var_dump($rawResults); // Returns an array
```

## Retrieving the ran query

If you'd like to retrieve the current query to save or run it at another
time, use the `getQuery()` method on the query builder.

This will return the escaped filter.

```php
$query = $provider->search()->where('cn', '=', 'John Doe')->getQuery();

echo $query; // Returns '(cn=\4a\6f\68\6e\20\44\6f\65)'
```

To retrieve the unescaped filter, call the `getUnescapedQuery()` method:

```php
$query = $provider->search()->where('cn', '=', 'John Doe')->getUnescapedQuery();

echo $query; // Returns '(cn=John Doe)'
```

Now that you know how to search your directory, lets move onto [creating / modifying LDAP records](models/model.md).
