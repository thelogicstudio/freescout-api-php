<?php

declare(strict_types=1);

namespace FreeScout\Api\Mailboxes;

use FreeScout\Api\Entity\LinkedEntityLoader;
use FreeScout\Api\Mailboxes\Entry\Field;
use FreeScout\Api\Mailboxes\Entry\Folder;

class MailboxLoader extends LinkedEntityLoader
{
    public function load()
    {
        /** @var Mailbox $mailbox */
        $mailbox = $this->getEntity();

        if ($this->shouldLoadResource(MailboxLinks::FIELDS)) {
            $mailbox->setFields($this->loadResources(Field::class, MailboxLinks::FIELDS));
        }

        if ($this->shouldLoadResource(MailboxLinks::FOLDERS)) {
            $mailbox->setFolders($this->loadResources(Folder::class, MailboxLinks::FOLDERS));
        }
    }
}
