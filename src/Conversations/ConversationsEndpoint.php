<?php

declare(strict_types=1);

namespace FreeScout\Api\Conversations;

use FreeScout\Api\Endpoint;
use FreeScout\Api\Entity\Collection;
use FreeScout\Api\Entity\PagedCollection;
use FreeScout\Api\Entity\Patch;
use FreeScout\Api\Exception\ValidationErrorException;
use FreeScout\Api\Http\Hal\HalPagedResources;
use FreeScout\Api\Http\Hal\HalResource;
use FreeScout\Api\Tags\TagsCollection;

class ConversationsEndpoint extends Endpoint
{
    public function get(int $id, ?ConversationRequest $conversationRequest = null): Conversation
    {
		$conversationRequest = $conversationRequest ?: (new ConversationRequest())->withThreads();
		$uri = sprintf('/api/conversations/%d', $id);
		$embed = [];
		foreach([
			ConversationLinks::THREADS,
			ConversationLinks::TAGS,
			ConversationLinks::TIMELOGS,
		] as $link) if($conversationRequest->hasLink($link)) $embed[] = $link;
		if($embed) $uri.= '?embed='.implode(',', $embed);

        $conversationResource = $this->restClient->getResource(Conversation::class, $uri);

        return $this->hydrateConversationWithSubEntities($conversationResource, $conversationRequest);
    }

    public function delete(int $conversationId): void
    {
        $this->restClient->deleteResource(sprintf('/api/conversations/%d', $conversationId));
    }

    /**
     * @throws ValidationErrorException
     */
    public function create(Conversation $conversation): ?int
    {
        return $this->restClient->createResource($conversation, sprintf('/api/conversations'));
    }

    /**
     * Updates the custom field values for a given conversation.  Ommitted fields are removed.
     *
     * @param CustomField[]|array|Collection|CustomFieldsCollection $customFields
     *
     * @throws ValidationErrorException
     */
    public function updateCustomFields(int $conversationId, $customFields): void
    {
        if ($customFields instanceof CustomFieldsCollection) {
            $customFieldsCollection = $customFields;
        } else {
            if ($customFields instanceof Collection) {
                $customFields = $customFields->toArray();
            }

            $customFieldsCollection = new CustomFieldsCollection();
            $customFieldsCollection->setCustomFields($customFields);
        }

        $this->restClient->updateResource(
            $customFieldsCollection,
            sprintf('/api/conversations/%d/fields', $conversationId)
        );
    }

    /**
     * Updates the tags for a given conversation.
     * Omitted tags are removed.
     *
     * @param array|Collection|TagsCollection $tags
     *
     * @throws ValidationErrorException
     */
    public function updateTags(int $conversationId, $tags): void
    {
        if ($tags instanceof TagsCollection) {
            $tagsCollection = $tags;
        } else {
            if ($tags instanceof Collection) {
                $tagNames = [];
                foreach ($tags as $tag) {
                    $tagNames[] = $tag->getName();
                }
                $tags = $tagNames;
            }

            $tagsCollection = new TagsCollection();
            $tagsCollection->setTags($tags);
        }

        $this->restClient->updateResource(
            $tagsCollection,
            sprintf('/api/conversations/%d/tags', $conversationId)
        );
    }

    /**
     * @return Conversation[]|PagedCollection
     */
    public function list(
        ?ConversationFilters $conversationFilters = null,
        ?ConversationRequest $conversationRequest = null
    ): PagedCollection {
        $uri = '/api/conversations';
        if ($conversationFilters) {
            $params = $conversationFilters->getParams();
            if (!empty($params)) {
                $uri .= '?'.http_build_query($params);
            }
        }

        return $this->loadConversations(
            $uri,
            $conversationRequest ?: new ConversationRequest()
        );
    }

    /**
     * @return Conversation[]|PagedCollection
     */
    private function loadConversations(string $uri, ConversationRequest $conversationRequest): PagedCollection
    {
        /** @var HalPagedResources $conversationResources */
        $conversationResources = $this->restClient->getResources(Conversation::class, 'conversations', $uri);
        $conversations = $conversationResources->map(function (HalResource $customerResource) use ($conversationRequest) {
            return $this->hydrateConversationWithSubEntities($customerResource, $conversationRequest);
        });

        return new PagedCollection(
            $conversations,
            $conversationResources->getPageMetadata(),
            $conversationResources->getLinks(),
            function (string $uri) use ($conversationRequest) {
                return $this->loadConversations($uri, $conversationRequest);
            },
			$uri
        );
    }

    private function hydrateConversationWithSubEntities(
        HalResource $conversationResource,
        ConversationRequest $conversationRequest
    ): Conversation {
        $conversationLoader = new ConversationLoader($this->restClient, $conversationResource, $conversationRequest->getLinks());
        $conversationLoader->load();

        return $conversationResource->getEntity();
    }

    /**
     * Move a conversation to a given mailbox.
     */
    public function move(int $conversationId, int $toMailboxId): void
    {
        $patch = Patch::move('mailboxId', $toMailboxId);
        $this->patchConversation($conversationId, $patch);
    }

    /**
     * Update the subject of a conversation.
     *
     * @throws ValidationErrorException
     */
    public function updateSubject(int $conversationId, string $subject): void
    {
        $patch = Patch::replace('subject', $subject);
        $this->patchConversation($conversationId, $patch);
    }

    /**
     * Change the customer associated with a conversation.
     *
     * @throws ValidationErrorException
     */
    public function updateCustomer(int $conversationId, int $newCustomerId): void
    {
        $patch = Patch::replace('primaryCustomer.id', $newCustomerId);
        $this->patchConversation($conversationId, $patch);
    }

    public function publishDraft(int $conversationId): void
    {
        $patch = Patch::replace('draft', false);
        $this->patchConversation($conversationId, $patch);
    }

    /**
     * @throws ValidationErrorException
     */
    public function updateStatus(int $conversationId, string $status): void
    {
        $patch = Patch::replace('status', $status);
        $this->patchConversation($conversationId, $patch);
    }

    public function assign(int $conversationId, int $assigneeId): void
    {
        $patch = Patch::replace('assignTo', $assigneeId);
        $this->patchConversation($conversationId, $patch);
    }

    public function unassign(int $conversationId): void
    {
        $patch = Patch::remove('assignTo');
        $this->patchConversation($conversationId, $patch);
    }

    private function patchConversation(int $conversationId, Patch $patch): void
    {
        $this->restClient->patchResource($patch, sprintf('/api/conversations/%d', $conversationId));
    }
}
