<?php

/**
 * Convert upstream UploadedFileInterface payloads to facade-compatible arrays.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       3.0.0
 */

namespace Webtolk\Otpravkapochtaru\Joomla;

defined('_JEXEC') or die;

use Psr\Http\Message\UploadedFileInterface;

/**
 * Normalize uploaded file responses for legacy facade callers.
 *
 * @since  3.0.0
 */
final class UploadedFileSerializer
{
    /**
     * Convert an uploaded file to an associative array response.
     *
     * @param   UploadedFileInterface  $file  Uploaded file returned by the upstream SDK.
     *
     * @return  array{
     *     content: string,
     *     contentType: string,
     *     fileName: string|null,
     *     statusCode: int,
     *     headers: array<string, mixed>
     * }
     *
     * @since   3.0.0
     */
    public static function toArray(UploadedFileInterface $file): array
    {
        $stream = $file->getStream();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        return [
            'content'     => (string) $stream->getContents(),
            'contentType' => $file->getClientMediaType() ?: 'application/octet-stream',
            'fileName'    => self::sanitizeFileName($file->getClientFilename() ?? null),
            'statusCode'  => 200,
            'headers'     => [
                'Content-Type' => $file->getClientMediaType(),
                'Content-Disposition' => $file->getClientFilename() === null ? null : 'attachment; filename="' . $file->getClientFilename() . '"',
                'Content-Length' => (string) $file->getSize(),
            ],
        ];
    }

    /**
     * Sanitize an upstream filename for facade response metadata.
     *
     * @param   string|null  $fileName  Raw upstream filename.
     *
     * @return  string|null
     *
     * @since   3.0.0
     */
    private static function sanitizeFileName(?string $fileName): ?string
    {
        if ($fileName === null) {
            return null;
        }

        $value = trim($fileName, "\"' \t\n\r\0\x0B");
        $value = basename(str_replace('\\', '/', $value));
        $value = preg_replace('/[\x00-\x1F\x7F<>:\"|?*]/', '_', $value) ?? '';
        $value = trim($value, " .\t\n\r\0\x0B");

        return $value === '' ? null : $value;
    }
}
