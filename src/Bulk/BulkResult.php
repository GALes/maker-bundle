<?php

namespace GALes\MakerBundle\Bulk;

final readonly class BulkResult
{
    public function __construct(
        public int $affected,
        public ?string $message = null,
    ) {
    }
}
