<?php
declare(strict_types=1);

namespace JfApp\Model\Dto;

/**
 * Nested DTO used to demonstrate that, under `projectAs`, a JSON embed arrives
 * as a raw array and must be mapped into a nested DTO manually (the plugin does
 * not auto-hydrate embeds for DTO projections).
 */
class ChapterDto
{
    public function __construct(
        public string $name,
        public ?string $uid,
    ) {
    }
}
