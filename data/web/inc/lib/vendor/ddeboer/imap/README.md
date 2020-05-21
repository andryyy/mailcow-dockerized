# IMAP library

[![Build Status](https://travis-ci.org/ddeboer/imap.svg?branch=master)](https://travis-ci.org/ddeboer/imap)
[![Code Coverage](https://scrutinizer-ci.com/g/ddeboer/imap/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ddeboer/imap/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ddeboer/imap/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ddeboer/imap/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/ddeboer/imap/v/stable.svg)](https://packagist.org/packages/ddeboer/imap)
[![Total Downloads](https://poser.pugx.org/ddeboer/imap/downloads.png)](https://packagist.org/packages/ddeboer/imap)

A PHP 7.2+ library to read and process e-mails over IMAP.

This library requires [IMAP](https://secure.php.net/manual/en/book.imap.php),
[iconv](https://secure.php.net/manual/en/book.iconv.php) and
[Multibyte String](https://secure.php.net/manual/en/book.mbstring.php) extensions installed.

## Table of Contents

1. [Feature Requests](#feature-requests)
1. [Installation](#installation)
1. [Usage](#usage)
    1. [Connect and Authenticate](#connect-and-authenticate)
    1. [Mailboxes](#mailboxes)
    1. [Messages](#messages)
        1. [Searching for Messages](#searching-for-messages)
        1. [Unknown search criterion: OR](#unknown-search-criterion-or)
        1. [Message Properties and Operations](#message-properties-and-operations)
    1. [Message Attachments](#message-attachments)
    1. [Embedded Messages](#embedded-messages)
    1. [Timeouts](#timeouts)
1. [Mock the library](#mock-the-library)
1. [Running the Tests](#running-the-tests)
    1. [Running Tests using Docker](#running-tests-using-docker)

## Feature Requests

[![Feature Requests](https://feathub.com/ddeboer/imap?format=svg)](https://feathub.com/ddeboer/imap)

## Installation

The recommended way to install the IMAP library is through [Composer](https://getcomposer.org):

```bash
$ composer require ddeboer/imap
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

## Usage

### Connect and Authenticate

```php
use Ddeboer\Imap\Server;

$server = new Server('imap.gmail.com');

// $connection is instance of \Ddeboer\Imap\Connection
$connection = $server->authenticate('my_username', 'my_password');
```

You can specify port, [flags and parameters](https://secure.php.net/manual/en/function.imap-open.php)
to the server:

```php
$server = new Server(
    $hostname, // required
    $port,     // defaults to '993'
    $flags,    // defaults to '/imap/ssl/validate-cert'
    $parameters
);
```

### Mailboxes

Retrieve mailboxes (also known as mail folders) from the mail server and iterate
over them:

```php
$mailboxes = $connection->getMailboxes();

foreach ($mailboxes as $mailbox) {
    // Skip container-only mailboxes
    // @see https://secure.php.net/manual/en/function.imap-getmailboxes.php
    if ($mailbox->getAttributes() & \LATT_NOSELECT) {
        continue;
    }

    // $mailbox is instance of \Ddeboer\Imap\Mailbox
    printf('Mailbox "%s" has %s messages', $mailbox->getName(), $mailbox->count());
}
```

Or retrieve a specific mailbox:

```php
$mailbox = $connection->getMailbox('INBOX');
```

Delete a mailbox:

```php
$connection->deleteMailbox($mailbox);
```

You can bulk set, or clear, any [flag](https://secure.php.net/manual/en/function.imap-setflag-full.php) of mailbox messages (by UIDs):

```php
$mailbox->setFlag('\\Seen \\Flagged', ['1:5', '7', '9']);
$mailbox->setFlag('\\Seen', '1,3,5,6:8');

$mailbox->clearFlag('\\Flagged', '1,3');
```

**WARNING** You must retrieve new Message instances in case of bulk modify flags to refresh the single Messages flags.

### Messages

Retrieve messages (e-mails) from a mailbox and iterate over them:

```php
$messages = $mailbox->getMessages();

foreach ($messages as $message) {
    // $message is instance of \Ddeboer\Imap\Message
}
```

To insert a new message (that just has been sent) into the Sent mailbox and flag it as seen:

```php
$mailbox = $connection->getMailbox('Sent');
$mailbox->addMessage($messageMIME, '\\Seen');
```

Note that the message should be a string at MIME format (as described in the [RFC2045](https://tools.ietf.org/html/rfc2045)).

#### Searching for Messages

```php
use Ddeboer\Imap\SearchExpression;
use Ddeboer\Imap\Search\Email\To;
use Ddeboer\Imap\Search\Text\Body;

$search = new SearchExpression();
$search->addCondition(new To('me@here.com'));
$search->addCondition(new Body('contents'));

$messages = $mailbox->getMessages($search);
```

**WARNING** We are currently unable to have both spaces _and_ double-quotes
escaped together. Only spaces are currently escaped correctly.
You can use `Ddeboer\Imap\Search\RawExpression` to write the complete search
condition by yourself.

Messages can also be retrieved sorted as per [imap_sort](https://secure.php.net/manual/en/function.imap-sort.php)
function:

```php
$today = new DateTimeImmutable();
$thirtyDaysAgo = $today->sub(new DateInterval('P30D'));

$messages = $mailbox->getMessages(
    new Ddeboer\Imap\Search\Date\Since($thirtyDaysAgo),
    \SORTDATE, // Sort criteria
    true // Descending order
);
```

#### Unknown search criterion: OR

Note that PHP imap library relies on the `c-client` library available at https://www.washington.edu/imap/
which doesn't fully support some IMAP4 search criteria like `OR`. If you want those unsupported criteria,
you need to manually patch the latest version (`imap-2007f` of 23-Jul-2011 at the time of this commit)
and recompile PHP onto your patched `c-client` library.

By the way most of the common search criteria are available and functioning, browse them in `./src/Search`.

References:

1. https://stackoverflow.com/questions/36356715/imap-search-unknown-search-criterion-or
1. imap-2007f.tar.gz: `./src/c-client/mail.c` and `./docs/internal.txt`

#### Message Properties and Operations

Get message number and unique [message id](https://en.wikipedia.org/wiki/Message-ID)
in the form <...>:

```php
$message->getNumber();
$message->getId();
```

Get other message properties:

```php
$message->getSubject();
$message->getFrom();    // Message\EmailAddress
$message->getTo();      // array of Message\EmailAddress
$message->getDate();    // DateTimeImmutable
$message->isAnswered();
$message->isDeleted();
$message->isDraft();
$message->isSeen();
```

Get message headers as a [\Ddeboer\Imap\Message\Headers](/src/Ddeboer/Imap/Message/Headers.php) object:

```php
$message->getHeaders();
```

Get message body as HTML or plain text:

```php
$message->getBodyHtml();    // Content of text/html part, if present
$message->getBodyText();    // Content of text/plain part, if present
```

Reading the message body keeps the message as unseen.
If you want to mark the message as seen:

```php
$message->markAsSeen();
```

Or you can set, or clear, any [flag](https://secure.php.net/manual/en/function.imap-setflag-full.php):

```php
$message->setFlag('\\Seen \\Flagged');
$message->clearFlag('\\Flagged');
```

Move a message to another mailbox:

```php
$mailbox = $connection->getMailbox('another-mailbox');
$message->move($mailbox);
$connection->expunge();
```

Deleting messages:

```php
$mailbox->getMessage(1)->delete();
$mailbox->getMessage(2)->delete();
$connection->expunge();
```

### Message Attachments

Get message attachments (both inline and attached) and iterate over them:

```php
$attachments = $message->getAttachments();

foreach ($attachments as $attachment) {
    // $attachment is instance of \Ddeboer\Imap\Message\Attachment
}
```

Download a message attachment to a local file:

```php
// getDecodedContent() decodes the attachment’s contents automatically:
file_put_contents(
    '/my/local/dir/' . $attachment->getFilename(),
    $attachment->getDecodedContent()
);
```

### Embedded Messages

Check if attachment is embedded message and get it:

```php
$attachments = $message->getAttachments();

foreach ($attachments as $attachment) {
    if ($attachment->isEmbeddedMessage()) {
        $embeddedMessage = $attachment->getEmbeddedMessage();
        // $embeddedMessage is instance of \Ddeboer\Imap\Message\EmbeddedMessage
    }
}
```

An EmbeddedMessage has the same API as a normal Message, apart from flags
and operations like copy, move or delete.

### Timeouts

The IMAP extension provides the [imap_timeout](https://secure.php.net/manual/en/function.imap-timeout.php)
function to adjust the timeout seconds for various operations.

However the extension's implementation doesn't link the functionality to a
specific context or connection, instead they are global. So in order to not
affect functionalities outside this library, we had to choose whether wrap
every `imap_*` call around an optional user-provided timeout or leave this
task to the user.

Because of the heterogeneous world of IMAP servers and the high complexity
burden cost for such a little gain of the former, we chose the latter.

## Mock the library

Mockability is granted by interfaces present for each API.
Dig into [MockabilityTest](tests/MockabilityTest.php) for an example of a
mocked workflow.

## Running the Tests

This library is functionally tested on [Travis CI](https://travis-ci.org/ddeboer/imap)
against a local Dovecot server.

If you have your own IMAP (test) account, you can run the tests locally by
providing your IMAP credentials:

```bash
$ composer install
$ IMAP_SERVER_NAME="my.imap.server.com" IMAP_SERVER_PORT="60993" IMAP_USERNAME="johndoe" IMAP_PASSWORD="p4ssword" vendor/bin/phpunit
```

You can also copy `phpunit.xml.dist` file to a custom `phpunit.xml` and put
these environment variables in it:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    bootstrap="./vendor/autoload.php"
    colors="true"
    verbose="true"
>
    <testsuites>
        <testsuite name="ddeboer/imap">
            <directory>./tests/</directory>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist>
            <directory suffix=".php">./src</directory>
        </whitelist>
    </filter>
    <php>
        <env name="IMAP_SERVER_NAME" value="my.imap.server.com" />
        <env name="IMAP_SERVER_PORT" value="60993" />
        <env name="IMAP_USERNAME" value="johndoe" />
        <env name="IMAP_PASSWORD" value="p4ssword" />
    </php>
</phpunit>
```

**WARNING** Tests create new mailboxes without removing them.

### Running Tests using Docker

If you have Docker installed you can run the tests locally with the following command:

```
$ docker-compose run tests
```
