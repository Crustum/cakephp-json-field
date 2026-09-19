<?php
declare(strict_types=1);

namespace JfApp\Model\Dto;

use Cake\ORM\Attribute\CollectionOf;

/**
 * Maps the `data` JSON column payload. The `chapters` embed arrives as a plain
 * array under `projectAs`; the core `#[CollectionOf]` attribute maps each element
 * into a {@see ChapterDto} without any manual hydration from the plugin.
 */
class ArticlePayloadDto
{
    /**
     * @param list<ChapterDto> $chapters
     */
    public function __construct(
        #[CollectionOf(ChapterDto::class)]
        public array $chapters = [],
    ) {
    }
}
