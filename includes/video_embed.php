<?php
/**
 * Convert common video page URLs (YouTube watch, Shorts, youtu.be, Vimeo)
 * into iframe-friendly embed URLs.
 */
function normalizeVideoEmbedUrl(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }

    if (preg_match('#(?:youtube(?:-nocookie)?\.com/embed/)([a-zA-Z0-9_-]{11})#i', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0';
    }

    if (preg_match('#(?:youtube\.com/shorts/)([a-zA-Z0-9_-]{11})#i', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0';
    }

    if (preg_match('#(?:youtube\.com/watch\?(?:[^&]*&)*v=|youtube\.com/watch\?v=)([a-zA-Z0-9_-]{11})#i', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0';
    }

    if (preg_match('#youtu\.be/([a-zA-Z0-9_-]{11})#i', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0';
    }

    if (preg_match('#vimeo\.com/(?:video/)?(\d+)#i', $url, $m)) {
        return 'https://player.vimeo.com/video/' . $m[1];
    }

    return $url;
}
