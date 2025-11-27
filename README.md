# Free Scout API PHP Client

This is a totally unoffical FreeScout PHP client. This client contains methods for easily interacting with the [FreeScout API](https://freescout.net/module/api-webhooks/).

It's based on the Help Scout PHP SDK, which just happens to work nearly identically.

| SDK Version | PHP Version | Branch   | Documentation                                                         |
|-------------|-------------|----------|-----------------------------------------------------------------------|
| `1.*`       | >= 7.3      | `master` | This page                                                             |

## Table of Contents

 * [Installation](#installation)
 * [Usage](#usage)
   * [Examples](#examples)
   * [Authentication](#authentication)
   * [Customers](#customers)
     * [Email](#email)
     * [Address](#address)
     * [Phone Number](#phone-number)
     * [Social Profile](#social-profile)
     * [Chat Handles](#chat-handles)
     * [Website](#website)
     * [Properties](#properties)
   * [Mailboxes](#mailboxes)
   * [Conversations](#conversations)
     * [Threads](#threads)
       * [Attachments](#attachments)
   * [Chats](#chats)
   * [Tags](#tags)
   * [Teams](#teams)
   * [Users](#users)
   * [Reports](#reports)
   * [Webhooks](#webhooks)
   * [Workflows](#workflows)
 * [Error Handling](#error-handling)
   * [Validation](#validation)
 * [Pagination](#pagination)
 * [Testing](#testing)
 * [Getting Support](#getting-support)

## Installation

The recommended way to install the client is by using [Composer](https://getcomposer.org/doc/00-intro.md).

```bash
composer require thelogicstudio/freescout-api "^1.0"
```

## Usage

You should always use Composer's autoloader in your application to autoload classes. All examples below assume you've already included this in your code:

```php
require_once 'vendor/autoload.php';
```

### Authentication

Use the factory to create a client. Once created, you can set the various credentials to make requests.

```php
use FreeScout\Api\ApiClientFactory;

$client = ApiClientFactory::createClient([
    'baseUri' => 'https://freescout.example.org', 
    'apiKey' => 'abc123',
]);
```

## Customers

Get a customer.  Whenever getting a customer, all it's entities (email addresses, phone numbers, social profiles, etc.) come preloaded in the same request.

```php
$customer = $client->customers()->get($customerId);
```

Get customers.

```php
$customers = $client->customers()->list();
```

Get customers with a filter.

As described in the API docs the [customer list can be filtered](https://api-docs.freescout.net/#list-customers) by a variety of fields. The `CustomerFields` class
provides a simple interface to set filter values. For example:

```php
use FreeScout\Api\Customers\CustomerFilters;

$filter = (new CustomerFilters())
    ->byFirstName('Tom')
    ->byLastName('Graham');

$customers = $client->customers()->list($filter);
```

Create a customer.

```php
use FreeScout\Api\Customers\Customer;

$customer = new Customer();
$customer->setFirstName('Bob');
// ...

try {
    $customerId = $client->customers()->create($customer);
} catch (\FreeScout\Api\Exception\ValidationErrorException $e) {
    var_dump($e->getError()->getErrors());
}
```

Update a customer.

```php
// ...
$customer->setFirstName('Bob');

$client->customers()->update($customer);
```

### Email

Create a customer email.

```php
use FreeScout\Api\Customers\Entry\Email;

$email = new Email();
$email->setValue('lucy@example.com');
$email->setType('work');
// ...

$client->customerEntry()->createEmail($customerId, $email);
```

Update a customer email.

```php
// ...
$email->setType('home');

$client->customerEntry()->updateEmail($customerId, $email);
```

Delete a customer email.

```php
$client->customerEntry()->deleteEmail($customerId, $emailId);
```

### Address

Create a customer address.

```php
use FreeScout\Api\Customers\Entry\Address;

$address = new Address();
$address->setCity('Boston');
// ...

$client->customerEntry()->createAddress($customerId, $address);
```

Update a customer address.

```php
// ...
$address->setCity('Boston');

$client->customerEntry()->updateAddress($customerId, $address);
```

Delete a customer address.

```php
$client->customerEntry()->deleteAddress($customerId);
```

### Phone number

Create a customer phone.

```php
use FreeScout\Api\Customers\Entry\Phone;

$phone = new Phone();
$phone->setValue('123456789');
$phone->setType('work');
// ...

$client->customerEntry()->createPhone($customerId, $phone);
```

Update a customer phone.

```php
// ...
$phone->setType('home');

$client->customerEntry()->updatePhone($customerId, $phone);
```

Delete a customer phone.

```php
$client->customerEntry()->deletePhone($customerId, $phoneId);
```

### Chat Handles

Create a customer chat.

```php
use FreeScout\Api\Customers\Entry\ChatHandle;

$chat = new ChatHandle();
$chat->setValue('1239134812348');
$chat->setType('icq');
// ...

$client->customerEntry()->createChat($customerId, $chat);
```

Update a customer chat.

```php
// ...
$chat->setValue('1230148584583');

$client->customerEntry()->updateChat($customerId, $chat);
```

Delete a customer chat.

```php
$client->customerEntry()->deleteChat($customerId, $chatId);
```

### Social profile

Create a customer social profile.

```php
use FreeScout\Api\Customers\Entry\SocialProfile;

$socialProfile = new SocialProfile();
$socialProfile->setValue('example');
$socialProfile->setType('twitter');
// ...

$client->customerEntry()->createSocialProfile($customerId, $socialProfile);
```

Update a customer social profile.

```php
// ...
$socialProfile->setType('facebook');

$client->customerEntry()->updateSocialProfile($customerId, $socialProfile);
```

Delete a customer social profile.

```php
$client->customerEntry()->deleteSocialProfile($customerId, $socialProfileId);
```

### Website

Create a customer website.

```php
use FreeScout\Api\Customers\Entry\Website;

$website = new Website();
$website->setValue('https://www.example.com');
// ...

$client->customerEntry()->createWebsite($customerId, $website);
```

Update a customer website.

```php
// ...
$website->setValue('https://www.example.net');

$client->customerEntry()->updateWebsite($customerId, $website);
```

Delete a customer website.

```php
$client->customerEntry()->deleteWebsite($customerId, $websiteId);
```

### Properties

Get a customer's properties and their values.

```php
$customer = $client->customers()->get(418048101);
// ...

foreach ($customer->getProperties() as $property) {
    echo $property->getName().': '.$property->getValue().PHP_EOL;
}
```

Update a customer's properties.
```php
use FreeScout\Api\Entity\Collection;
use FreeScout\Api\Entity\Patch;

$operations = new Collection(
    [
        new Patch('remove', 'property-1'),
        new Patch('replace', 'property-2', 'value'),
    ];
);
$client->customerEntry()->updateProperties($customerId, $operations);

```
## Mailboxes

Get a mailbox.

```php
$mailbox = $client->mailboxes()->get($mailboxId);
```

Get a mailbox with pre-loaded sub-entities.

A mailbox entity has two related sub-entities:

* Fields
* Folders

Each of these sub-entities can be pre-loaded when fetching a mailbox to remove the need for multiple method calls. The `MailboxRequest` class is used
to describe which sub-entities should be pre-loaded. For example:

```php
use FreeScout\Api\Mailboxes\MailboxRequest;

$request = (new MailboxRequest)
    ->withFields()
    ->withFolders();

$mailbox = $client->mailboxes()->get($mailboxId, $request);

$fields = $mailbox->getFields();
$folders = $mailbox->getFolders();
```

Get mailboxes.

```php
$mailboxes = $client->mailboxes()->list();
```

Get mailboxes with pre-loaded sub-entities.

```php
use FreeScout\Api\Mailboxes\MailboxRequest;

$request = (new MailboxRequest)
    ->withFields()
    ->withFolders();

$mailboxes = $client->mailboxes()->list($request);
```

## Conversations

Get a conversation.

```php
$conversation = $client->conversations()->get($conversationId);
```

You can easily eager load additional information/relationships for a conversation.  For example:

```php
use FreeScout\Api\Conversations\ConversationRequest;

$request = (new ConversationRequest)
    ->withMailbox()
    ->withPrimaryCustomer()
    ->withCreatedByCustomer()
    ->withCreatedByUser()
    ->withClosedBy()
    ->withThreads()
    ->withAssignee();

$conversation = $client->conversations()->get($conversationId, $request);

$mailbox = $conversation->getMailbox();
$primaryCustomer = $conversation->getCustomer();
```

Get conversations.

```php
$conversations = $client->conversations()->list();
```

Get conversations with pre-loaded sub-entities.

```php
use FreeScout\Api\Conversations\ConversationRequest;

$request = (new ConversationRequest)
    ->withMailbox()
    ->withPrimaryCustomer()
    ->withCreatedByCustomer()
    ->withCreatedByUser()
    ->withClosedBy()
    ->withThreads()
    ->withAssignee();

$conversations = $client->conversations()->list(null, $request);
```

Narrow down the list of Conversations based on a set of filters.

```php
use FreeScout\Api\Conversations\ConversationFilters;

$filters = (new ConversationFilters())
    ->inMailbox(1)
    ->inFolder(13)
    ->inStatus('all')
    ->hasTag('testing')
    ->assignedTo(1771)
    ->modifiedSince(new DateTime('2017-05-06T09:04:23+05:00'))
    ->byNumber(42)
    ->sortField('createdAt')
    ->sortOrder('asc')
    ->withQuery('query')
    ->byCustomField(123, 'blue');

$conversations = $client->conversations()->list($filters);

```

You can even combine the filters with the pre-loaded sub-entities in one request

```php
use FreeScout\Api\Conversations\ConversationRequest;
use FreeScout\Api\Conversations\ConversationFilters;

$request = (new ConversationRequest)
    ->withMailbox()
    ->withThreads();

$filters = (new ConversationFilters())
    ->inMailbox(1)
    ->inFolder(13)
    ->byCustomField(123, 'blue');

$conversations = $client->conversations()->list($filters, $request);
```

Update the custom fields on a conversation:

```php
$customField = new CustomField();
$customField->setId(10524);
$customField->setValue(new DateTime('today'));
$client->conversations()->updateCustomFields($conversationId, [$customField]);
```

Create a new conversation, as if the customer sent an email to your mailbox.

```php

// We can specify either the id or email for the Customer
$customer = new Customer();
$customer->addEmail('my-customer@company.com');

$thread = new CustomerThread();
$thread->setCustomer($customer);
$thread->setText('Test');

$conversation = new Conversation();
$conversation->setSubject('Testing the PHP SDK v2: Phone Thread');
$conversation->setStatus('active');
$conversation->setType('email');
$conversation->setMailboxId(80261);
$conversation->setCustomer($customer);
$conversation->setThreads(new Collection([
    $thread,
]));

// You can optionally add tags
$tag = new Tag();
$tag->setName('testing');
$conversation->addTag($tag);

try {
    $conversationId = $client->conversations()->create($conversation);
} catch (ValidationErrorException $e) {
    var_dump($e->getError()->getErrors());
}
```

Here's some other example scenarios where you might create conversations:

<details>
  <summary>Phone conversation, initiated by a FreeScout user</summary>

```
$user = $client->users()->get(31231);

$customer = new Customer();
$customer->setId(193338443);

$thread = new PhoneThread();
$thread->setCustomer($customer);
$thread->setCreatedByUser($user);
$thread->setText('Test');

$conversation = new Conversation();
$conversation->setSubject('Testing the PHP SDK v2: Phone Thread');
$conversation->setStatus('active');
$conversation->setType('phone');
$conversation->setMailboxId(80261);
$conversation->setCustomer($noteCustomer);
$conversation->setCreatedByUser($user);
$conversation->setThreads(new Collection([
    $thread,
]));
```
</details>
<details>
  <summary>Chat conversation, initiated by the Customer</summary>

```
$noteCustomer = new Customer();
$noteCustomer->setId(163315601);
$thread = new ChatThread();
$thread->setCustomer($noteCustomer);
$thread->setText('Test');
$conversation = new Conversation();
$conversation->setSubject('Testing the PHP SDK v2: Chat Thread');
$conversation->setStatus('active');
$conversation->setType('chat');
$conversation->setAssignTo(271315);
$conversation->setMailboxId(138367);
$conversation->setCustomer($noteCustomer);
$conversation->setThreads(new Collection([
    $thread,
]));

// Also adding a tag to this conversation
$tag = new Tag();
$tag->setName('testing');
$conversation->addTag($tag);

$conversationId = $client->conversations()->create($conversation);
```
</details>

Delete a conversation:

```php
$client->conversations()->delete($conversationId);
```

Update an existing conversation:

```php
$client->conversations()->move($conversationId, 18);
$client->conversations()->updateSubject($conversationId, 'Need more help please');
$client->conversations()->updateCustomer($conversationId, 6854);
$client->conversations()->publishDraft($conversationId);
$client->conversations()->updateStatus($conversationId, 'closed');
$client->conversations()->assign($conversationId, 127);
$client->conversations()->unassign($conversationId);
```

### Threads

#### Chat Threads

Create new Chat threads for a conversation.

```php
use FreeScout\Api\Customers\Customer;
use FreeScout\Api\Conversations\Threads\ChatThread;

$thread = new ChatThread();
$customer = new Customer();
$customer->setId(163487350);

$thread->setCustomer($customer);
$thread->setText('Thanks for reaching out to us!');

$client->threads()->create($conversationId, $thread);
```

#### Customer Threads

Create new Customer threads for a conversation.

```php
use FreeScout\Api\Customers\Customer;
use FreeScout\Api\Conversations\Threads\CustomerThread;

$thread = new CustomerThread();
$customer = new Customer();
$customer->setId(163487350);

$thread->setCustomer($customer);
$thread->setText('Please help me figure this out');

$client->threads()->create($conversationId, $thread);
```

#### Note Threads

Create new Note threads for a conversation.

```php
use FreeScout\Api\Conversations\Threads\NoteThread;

$thread->setText('We are still looking into this');

$client->threads()->create($conversationId, $thread);
```

#### Phone Threads

Create new Phone threads for a conversation.

```php
use FreeScout\Api\Customers\Customer;
use FreeScout\Api\Conversations\Threads\PhoneThread;

$thread = new PhoneThread();
$customer = new Customer();
$customer->setId(163487350);

$thread->setCustomer($customer);
$thread->setText('This customer called and spoke with us directly about the delay on their order');

$client->threads()->create($conversationId, $thread);
```

#### Reply Threads

Create new Reply threads for a conversation.

```php
use FreeScout\Api\Customers\Customer;
use FreeScout\Api\Conversations\Threads\ReplyThread;

$thread = new ReplyThread();
$customer = new Customer();
$customer->setId(163487350);

$thread->setCustomer($customer);
$thread->setText("Thanks, we'll be with you shortly!");

$client->threads()->create($conversationId, $thread);
```

Get threads for a conversation.

```php
$threads = $client->threads()->list($conversationId);
```

#### Attachments

Get an attachment.

```php
$attachment = $client->attachments()->get($conversationId, $attachmentId);
$attachment->getData(); // attached file's contents
```

Create an attachment:

```php
use FreeScout\Api\Conversations\Threads\Attachments\AttachmentFactory;
use FreeScout\Api\Support\Filesystem;

$attachmentFactory = new AttachmentFactory(new Filesystem());
$attachment = $attachmentFactory->make('path/to/profile.jpg');

$attachment->getMimeType(); // image/jpeg
$attachment->getFilename(); // profile.jpg
$attachment->getData(); // base64 encoded contents of the file

$client->attachments()->create($conversationId, $threadId, $attachment);
```

Delete an attachment:

```php
$client->attachments()->delete($conversationId, $attachmentId);
```

## Chats

Get a chat

```php
$chat = $client->chats()->get($chatId);
```

List the chat events

```php
$events = $client->chats()->events($chatId);
```

## Tags

List the tags

```php
$tags = $client->tags()->list();
```

## Teams

List the teams

```php
$teams = $client->teams()->list();
```

List the members of a team

```php
$users = $client->teams()->members($teamId);
```

## Users

Get a user.

```php
$user = $client->users()->get($userId);
```

Get users.

```php
$users = $client->users()->list();
```

Narrow down the list of Users based on a set of filters.

```php
use FreeScout\Api\Users\UserFilters;

$filters = (new UserFilters())
    ->inMailbox(1)
    ->byEmail('tester@test.com');

$users = $client->users()->list($filters);
```

## Webhooks

Get a webhook.

```php
$webhook = $client->webhooks()->get($webhookId);
```

List webhooks.

```php
$webhooks = $client->webhooks()->list();
```

Create a webhook.

The default state for a newly-created webhook is `enabled`.

```php
use FreeScout\Api\Webhooks\Webhook;

$data = [
    'url' => 'http://bad-url.com',
    'events' => ['convo.assigned', 'convo.moved'],
    'secret' => 'notARealSecret'
];
$webhook = new Webhook();
$webhook->hydrate($data);
// ...

$client->webhooks()->create($webhook);
```

Update a webhook

This operation replaces the entire webhook entity, so you must provide the secret again. Once updated, the webhook will be in the `enabled` state again.
```php
$webhook->setUrl('http://bad-url.com/really_really_bad');
$webhook->setSecret('mZ9XbGHodY');
$client->webhooks()->update($webhook);
```

Delete a webhook.

```php
$client->webhooks()->delete($webhookId);
```

### Processing an incoming webhook
You can also use the SDK to easily process an incoming webhook.  Signature validation will happen when creating the new object, so no need to check if it is valid or not. If the signatures do not match, the constructor of the `IncomingWebhook` object will throw an `InvalidSignatureException` to let you know something is wrong.

```php
// Build it from globals
$incoming = IncomingWebhook::makeFromGlobals($secret);
```

```php
// or build using a request object that satisfies the PSR-7 RequestInterface
/** @var RequestInterface $request */
$request = new Request(...);
$secret = 'superSekretKey';
$incoming = new IncomingWebhook($request, $secret);
```

Once you have the incoming webhook object, you can check the type of payload (customer, conversation, or test) as well as retrieve the data. If a customer or conversation, you can retrieve the model associated. Otherwise, you can get the payload as either an associative array or standard class object.

## Workflows

Fetch a paginated list of all workflows.
```php
$workflows = $client->workflows()->list();
```

Run a manual workflow on a list of conversations.
```php
$convos = [
    123,
    321
];
$client->workflows()->runWorkflow($id, $convos);
```

Change a workflow status to either "active" or "inactive"
```php
$client->workflows()->updateStatus($id, 'active');
```

# Error handling

Any exception thrown by the client directly will implement `FreeScout\Api\Exception` and HTTP errors will result in `Http\Client\Exception\RequestException` being thrown.

If an OAuth2 token is not provided or invalid then a `FreeScout\Api\Exception\AuthenticationException` is thrown.

## Validation

You'll encounter a `ValidationErrorException` if there are any validation errors with the request you submitted to the API.  Here's a quick example on how to use that exception:

```php
try {
    // do something
} catch (\FreeScout\Api\Exception\ValidationErrorException $e) {
    $error = $e->getError();

    var_dump(
        // A reference id for that request.  Including this anytime you contact Free Scout will
        // empower us to dig right to the heart of the issue
        $error->getCorrelationId(),

        // Details about the invalid fields in the request
        $error->getErrors()
    );
    exit;
}
```


# Pagination

When fetching a collection of entities the client will return an instance of `FreeScout\Api\Entity\Collection`. If the end point supports pagination then it will return an instance of `FreeScout\Api\Entity\PagedCollection`.

```php
/** @var PagedCollection $users */
$users = $client->users()->list();

// Iterate over the first page of results
foreach ($users as $user) {
    echo $users->getFirstName();
}

// The current page number
$users->getPageNumber();

// The total number of pages
$users->getTotalPageCount();

// Load the next page
$nextUsers = $users->getNextPage();

// Load the previous page
$previousUsers = $users->getPreviousPage();

// Load the first page
$firstUsers = $users->getFirstPage();

// Load the last page
$lastUsers = $users->getLastPage();

// Load a specific page
$otherUsers = $users->getPage(12);

// Paged results are accessible as normal arrays, so you can simply iterate over them
foreach ($otherUsers as $user) {
    echo $user->getFirstName();
}
```

# Testing

The SDK comes with a handy `mock` method on the `ApiClient` class. To use this, pass in the name of the endpoint you want to mock. You'll get a `\Mockery\MockInterface` object back. Once you set the mock, any subsequent calls to that endpoint will return the mocked object.

```php
// From within the tests/ApiClientTest.php file...
public function testMockReturnsProperMock()
{
    $client = ApiClientFactory::createClient();
    $mockedWorkflows = $client->mock('workflows');

    $this->assertInstanceOf(WorkflowsEndpoint::class, $mockedWorkflows);
    $this->assertInstanceOf(MockInterface::class, $mockedWorkflows);

    $this->assertSame(
        $mockedWorkflows,
        $client->workflows()
    );
}
```

Once you've mocked an endpoint, you may want to clear it later on. To do this, you can use the `clearMock($endpoint)` method on the `ApiClient`.
