<?php

declare(strict_types=1);

namespace FreeScout\Api\Conversations;

use FreeScout\Api\Conversations\Threads\ThreadFactory;
use FreeScout\Api\Customers\Customer;
use FreeScout\Api\Entity\LinkedEntityLoader;
use FreeScout\Api\Mailboxes\Mailbox;
use FreeScout\Api\Users\User;

class ConversationLoader extends LinkedEntityLoader
{
    public function load()
    {
        /** @var Conversation $conversation */
        $conversation = $this->getEntity();

        if ($this->shouldLoadResource(ConversationLinks::MAILBOX)) {
            $mailbox = $this->loadResource(Mailbox::class, ConversationLinks::MAILBOX);
            $conversation->setMailbox($mailbox);
        }

        if ($this->shouldLoadResource(ConversationLinks::PRIMARY_CUSTOMER)) {
            $customer = $this->loadResource(Customer::class, ConversationLinks::PRIMARY_CUSTOMER);
            $conversation->setCustomer($customer);
        }

        if ($this->shouldLoadResource(ConversationLinks::CREATED_BY_CUSTOMER)) {
            $createdByCustomer = $this->loadResource(Customer::class, ConversationLinks::CREATED_BY_CUSTOMER);
            $conversation->setCreatedByCustomer($createdByCustomer);
        }

        if ($this->shouldLoadResource(ConversationLinks::CREATED_BY_USER)) {
            $createdByUser = $this->loadResource(User::class, ConversationLinks::CREATED_BY_USER);
            $conversation->setCreatedByUser($createdByUser);
        }

        if ($this->shouldLoadResource(ConversationLinks::ASSIGNEE)) {
            $assignee = $this->loadResource(User::class, ConversationLinks::ASSIGNEE);
            $conversation->setAssignee($assignee);
        }

		if ($this->shouldLoadResource(ConversationLinks::THREADS)) {
			$threadFactory = new ThreadFactory();
			$threads = $this->loadResources(function (array $data) use ($threadFactory) {
				return $threadFactory->make($data['type'], $data);
			}, ConversationLinks::THREADS);

			$conversation->setThreads($threads);
		}

        if ($conversation->getStatus() === Status::CLOSED && $this->shouldLoadResource(ConversationLinks::CLOSED_BY)) {
            $closedBy = $this->loadResource(User::class, ConversationLinks::CLOSED_BY);
            $conversation->setClosedBy($closedBy);
        }
    }
}
