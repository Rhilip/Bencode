<?php

include_once 'traits/TorrentFileCommonTrait.php';

use Rhilip\Bencode\TorrentFile;
use PHPUnit\Framework\TestCase;

class TorrentV1MultiTest extends TestCase
{
    use TorrentFileCommonTrait;

    protected $protocol = TorrentFile::PROTOCOL_V1;
    protected $fileMode = TorrentFile::FILEMODE_MULTI;

    protected $infoHashs = [
        TorrentFile::PROTOCOL_V1 => '344f85b35113783a34bb22ba7661fa26f1046bd1',
        TorrentFile::PROTOCOL_V2 => null
    ];
}
