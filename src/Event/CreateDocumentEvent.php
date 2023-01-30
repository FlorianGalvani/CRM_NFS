<?php

namespace App\Event;

use App\Entity\Document;

class CreateDocumentEvent
{
    public const NAME = 'document.create';
    private $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function getDocument()
    {
        return $this->document;
    }
}