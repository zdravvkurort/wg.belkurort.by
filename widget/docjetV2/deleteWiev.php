<?php
$fileSystemIterator = new FilesystemIterator('wievDoc');
$now = time();
foreach ($fileSystemIterator as $file) {
    if ($now - $file->getCTime() >= 60 * 60 * 24 * 1) // 1 days
        unlink('wievDoc/'.$file->getFilename());
}