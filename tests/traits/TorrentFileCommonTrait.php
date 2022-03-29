<?php

use Rhilip\Bencode\TorrentFile;

trait TorrentFileCommonTrait
{
    private $torrent;

    protected $announce = 'https://example.com/announce';
    protected $announceList = [
        ["https://example.com/announce"],
        ["https://example1.com/announce"]
    ];
    protected $comment = 'Rhilip';
    protected $createdBy = 'qBittorrent v4.4.2';
    protected $creationDate = 1648385760;
    protected $urlList = "https://example.com/webseed";

    protected $pieceLength = 65536;
    protected $source = 'Rhilip';

    protected function setUp()
    {
        $this->torrent = TorrentFile::load("tests/asserts/{$this->protocol}-{$this->fileMode}.torrent");
    }

    public function testAnnounce()
    {
        $this->assertEquals($this->announce, $this->torrent->getAnnounce());

        $announce = 'https://example2.com/announce';
        $this->torrent->setAnnounce($announce);
        $this->assertEquals($announce, $this->torrent->getAnnounce());
    }

    public function testAnnounceList()
    {
        $this->assertEquals($this->announceList, $this->torrent->getAnnounceList());

        $announceList = [["https://example1.com/announce"], ["https://example2.com/announce"]];
        $this->torrent->setAnnounceList($announceList);
        $this->assertEquals($announceList, $this->torrent->getAnnounceList());
    }

    public function testComment()
    {
        $this->assertEquals($this->comment, $this->torrent->getComment());

        $comment = 'new comment';
        $this->torrent->setComment($comment);
        $this->assertEquals($comment, $this->torrent->getComment());
    }

    public function testCreatBy()
    {
        $this->assertEquals($this->createdBy, $this->torrent->getCreatedBy());

        $createdBy = 'new createdBy';
        $this->torrent->setCreatedBy($createdBy);
        $this->assertEquals($createdBy, $this->torrent->getCreatedBy());
    }

    public function testCreationDate()
    {
        $this->assertEquals($this->creationDate, $this->torrent->getCreationDate());

        $creationDate = time();
        $this->torrent->setCreationDate($creationDate);
        $this->assertEquals($creationDate, $this->torrent->getCreationDate());
    }

    public function testHttpSeeds()
    {
        $this->assertNull($this->torrent->getHttpSeeds());

        $httpSeeds = ['udp://example.com/seed'];
        $this->torrent->setHttpSeeds($httpSeeds);
        $this->assertEquals($httpSeeds, $this->torrent->getHttpSeeds());
    }

    public function testNodes()
    {
        $this->assertNull($this->torrent->getNodes());

        $nodes = ['udp://example.com/seed'];
        $this->torrent->setNodes($nodes);
        $this->assertEquals($nodes, $this->torrent->getNodes());
    }

    public function testUrlList()
    {
        $this->assertEquals($this->urlList, $this->torrent->getUrlList());

        $urlList = "https://example1.com/webseed";
        $this->torrent->setUrlList($urlList);
        $this->assertEquals($urlList, $this->torrent->getUrlList());
    }

    public function testGetProtocol()
    {
        $this->assertEquals($this->protocol, $this->torrent->getProtocol());
    }

    public function testgetFileMode()
    {
        $this->assertEquals($this->fileMode, $this->torrent->getFileMode());
    }

    public function testInfoHash()
    {
        $this->assertEquals($this->infoHashs, $this->torrent->getInfoHashs());

        if ($this->protocol === TorrentFile::PROTOCOL_V1) {
            $this->assertEquals($this->infoHashs[TorrentFile::PROTOCOL_V1], $this->torrent->getInfoHashV1());
            $this->assertEquals($this->infoHashs[TorrentFile::PROTOCOL_V1], $this->torrent->getInfoHash());
        }

        if ($this->protocol === TorrentFile::PROTOCOL_V2) {
            $this->assertEquals($this->infoHashs[TorrentFile::PROTOCOL_V2], $this->torrent->getInfoHashV2());
            $this->assertEquals($this->infoHashs[TorrentFile::PROTOCOL_V2], $this->torrent->getInfoHash());
            $this->assertEquals(substr(pack("H*", $this->infoHashs[TorrentFile::PROTOCOL_V2]), 0, 20), $this->torrent->getInfoHashV2ForAnnounce());
        }

        if ($this->protocol === TorrentFile::PROTOCOL_HYBRID) {
            $this->assertEquals($this->infoHashs[TorrentFile::PROTOCOL_V1], $this->torrent->getInfoHashV1());
            $this->assertEquals($this->infoHashs[TorrentFile::PROTOCOL_V2], $this->torrent->getInfoHashV2());
            $this->assertEquals($this->infoHashs[TorrentFile::PROTOCOL_V2], $this->torrent->getInfoHash());
            $this->assertEquals(substr(pack("H*", $this->infoHashs[TorrentFile::PROTOCOL_V2]), 0, 20), $this->torrent->getInfoHashV2ForAnnounce());
        }
    }

    public function testGetPieceLength()
    {
        $this->assertEquals($this->pieceLength, $this->torrent->getPieceLength());
    }

    public function testGetName()
    {
        $name = $this->fileMode === TorrentFile::FILEMODE_MULTI ? 'tname' : 'file1.dat';

        $this->assertEquals($name, $this->torrent->getName());
    }

    public function testSetNameEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$name must not be empty');

        $this->torrent->setName('');
    }

    public function testSetNameWithSlash()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$name must not contain slashes and zero bytes');

        $this->torrent->setName('prefix/suffix');
    }

    public function testSetNameWithZeroBytes()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$name must not contain slashes and zero bytes');

        $this->torrent->setName("test\0");
    }

    public function testSource()
    {
        $this->assertEquals($this->source, $this->torrent->getSource());

        $source = "new source";
        $this->torrent->setSouce($source);
        $this->assertEquals($source, $this->torrent->getSource());
    }

    public function testPrivate()
    {
        $this->assertFalse($this->torrent->isPrivate());

        $this->torrent->setPrivate(true);
        $this->assertTrue($this->torrent->isPrivate());

        $this->torrent->setPrivate(false);
        $this->assertFalse($this->torrent->isPrivate());
    }

    public function testGetSize()
    {
        $size = $this->fileMode === TorrentFile::FILEMODE_MULTI ? 33554432 /* 32MiB */ : 16777216 /* 16MiB */;
        $this->assertEquals($size, $this->torrent->getSize());
    }

    public function testGetFileCount()
    {
        $fileCount = $this->fileMode === TorrentFile::FILEMODE_MULTI ? 2 : 1;
        $this->assertEquals($fileCount, $this->torrent->getFileCount());
    }

    public function testGetFileList()
    {
        $fileCount = $this->fileMode === TorrentFile::FILEMODE_MULTI
            ? [['path' => 'dict/file2.dat', 'size' => 16777216], ['path' => 'file1.dat', 'size' => 16777216]]
            : [['path' => 'file1.dat', 'size' => 16777216]];
        $this->assertEqualsCanonicalizing($fileCount, $this->torrent->getFileList());
    }

    public function testGetFileTree()
    {
        $fileCount = $this->fileMode === TorrentFile::FILEMODE_MULTI
            ? ['tname' => ['dict' => ['file2.dat' => 16777216], 'file1.dat' => 16777216]]
            : ['file1.dat' => 16777216];
        $this->assertEqualsCanonicalizing($fileCount, $this->torrent->getFileTree());
    }
}
