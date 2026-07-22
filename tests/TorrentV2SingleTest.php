<?php

include_once 'traits/TorrentFileCommonTrait.php';
include_once 'traits/TorrentFileV2Trait.php';

use Rhilip\Bencode\Bencode;
use Rhilip\Bencode\TorrentFile;
use PHPUnit\Framework\TestCase;

class TorrentV2SingleTest extends TestCase
{
    use TorrentFileCommonTrait;
    use TorrentFileV2Trait;

    protected $protocol = TorrentFile::PROTOCOL_V2;
    protected $fileMode = TorrentFile::FILEMODE_SINGLE;

    protected $infoHashs = [
        TorrentFile::PROTOCOL_V1 => null,
        TorrentFile::PROTOCOL_V2 => 'a58e747f0ce2c2073c6fd635d4afdd5c6162574d6c9184318f884f553c3ed65b'
    ];

    public function testExtendFileAttr() {
        $data = [
            'info' => [
                'name' => 'test',
                'meta version' => 2,
                'piece length' => 1024,
                'file tree' => [
                    'file1.dat' => [
                        '' => [
                            'attr' => 'hx',
                            'length' => 100,
                            'pieces root' => hex2bin('832d96b4f8b422aa75f8d40975b1a408154bc1a2bdffccf7b689386cde125a30'),
                        ]
                    ]
                ],
            ],
            'piece layers' => []
        ];

        $torrent = TorrentFile::loadFromString(Bencode::encode($data));
        $torrent->parse();

        $torrentFileList = $torrent->getFileList();
        foreach ($torrentFileList as $file) {
            if ($file['path'] === 'file1.dat') {
                $this->assertEquals('hx', $file['attr']);
                return;
            }
        }
        $this->fail('file1.dat not found in file list');
    }
}
