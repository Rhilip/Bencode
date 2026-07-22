<?php

include_once 'traits/TorrentFileCommonTrait.php';
include_once 'traits/TorrentFileV2Trait.php';

use Rhilip\Bencode\Bencode;
use Rhilip\Bencode\ParseException;
use Rhilip\Bencode\TorrentFile;
use PHPUnit\Framework\TestCase;

class TorrentV2MultiTest extends TestCase
{
    use TorrentFileCommonTrait;
    use TorrentFileV2Trait;

    protected $protocol = TorrentFile::PROTOCOL_V2;
    protected $fileMode = TorrentFile::FILEMODE_MULTI;

    protected $infoHashs = [
        TorrentFile::PROTOCOL_V1 => null,
        TorrentFile::PROTOCOL_V2 => '832d96b4f8b422aa75f8d40975b1a408154bc1a2bdffccf7b689386cde125a30'
    ];

    public function testLAttrMustHasSymlinkFile() {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Checking Dictionary missing key: symlink path');

        $data = [
            'info' => [
                'name' => 'test',
                'meta version' => 2,
                'piece length' => 1024,
                'file tree' => [
                    'file1.dat' => [
                        '' => [
                            'attr' => 'l',
                            'length' => 100,
                            'pieces root' => hex2bin('832d96b4f8b422aa75f8d40975b1a408154bc1a2bdffccf7b689386cde125a30'),
                        ]
                    ],
                    'dir1' => [
                        'file2.dat' => [
                            '' => [
                                'length' => 16,
                                'pieces root' => hex2bin('832d96b4f8b422aa75f8d40975b1a408154bc1a2bdffccf7b689386cde125a30')
                            ]
                        ],
                    ]
                ],
            ],
            'piece layers' => []
        ];
        $torrent = TorrentFile::loadFromString(Bencode::encode($data));
        $torrent->parse();
    }

    public function testSymlinkFileMustZeroLength() {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Invalid symlink file, must be 0 length');

        $data = [
            'info' => [
                'name' => 'test',
                'meta version' => 2,
                'piece length' => 1024,
                'file tree' => [
                    'file1.dat' => [
                        '' => [
                            'attr' => 'l',
                            'length' => 100,
                            'pieces root' => hex2bin('832d96b4f8b422aa75f8d40975b1a408154bc1a2bdffccf7b689386cde125a30'),
                            'symlink path' => ['dir1' , 'file2.dat']
                        ]
                    ],
                    'dir1' => [
                        'file2.dat' => [
                            '' => [
                                'length' => 16,
                                'pieces root' => hex2bin('832d96b4f8b422aa75f8d40975b1a408154bc1a2bdffccf7b689386cde125a30')
                            ]
                        ],
                    ]
                ],
            ],
            'piece layers' => []
        ];

        $torrent = TorrentFile::loadFromString(Bencode::encode($data));
        $torrent->parse();
    }

    public function testSymlinkFilePass() {
        $data = [
            'info' => [
                'name' => 'test',
                'meta version' => 2,
                'piece length' => 1024,
                'file tree' => [
                    'file1.dat' => [
                        '' => [
                            'attr' => 'l',
                            'length' => 0,
                            'pieces root' => hex2bin('832d96b4f8b422aa75f8d40975b1a408154bc1a2bdffccf7b689386cde125a30'),
                            'symlink path' => ['dir1' , 'file2.dat']
                        ]
                    ],
                    'dir1' => [
                        'file2.dat' => [
                            '' => [
                                'length' => 16,
                                'pieces root' => hex2bin('832d96b4f8b422aa75f8d40975b1a408154bc1a2bdffccf7b689386cde125a30')
                            ]
                        ],
                    ]
                ],
            ],
            'piece layers' => []
        ];
        $torrent = TorrentFile::loadFromString(Bencode::encode($data));

        $torrentFileList = $torrent->getFileList();
        foreach ($torrentFileList as $file) {
            if ($file['path'] === 'file1.dat') {
                $this->assertEquals('l', $file['attr']);
                $this->assertEquals('dir1/file2.dat', $file['symlink path']);
                $this->assertEquals(0, $file['size']);
                return;
            }
        }
        $this->fail('file1.dat not found in file list');
    }

    public function testFileModeShouldBeMultiWhenTopIsDir() {
        $data = [
            'info' => [
                'name' => 'test',
                'meta version' => 2,
                'piece length' => 16,
                'file tree' => [
                    'folder' => [
                        'file1.dat' => [
                            '' => [
                                'length' => 123456,
                                'pieces root' => hex2bin('832d96b4f8b422aa75f8d40975b1a408154bc1a2bdffccf7b689386cde125a30')
                            ]
                        ],
                        'file2.dat' => [
                            '' => [
                                'length' => 123456,
                                'pieces root' => hex2bin('832d96b4f8b422aa75f8d40975b1a408154bc1a2bdffccf7b689386cde125a30')
                            ]
                        ],
                    ]
                ],
            ],
            'piece layers' => []
        ];
        $torrent = TorrentFile::loadFromString(Bencode::encode($data));

        $this->assertEquals(TorrentFile::FILEMODE_MULTI, $torrent->getFileMode());
    }

    public function testNoChildFilesInFile(): void
    {
        self::expectException(ParseException::class);
        self::expectExceptionMessage('Invalid node: file cannot contain child files');

        $data = [
            'info' => [
                'name' => 'test',
                'meta version' => 2,
                'piece length' => 16,
                'file tree' => [
                    'dir1' => [
                        'file1' => [
                            '' => [
                                'length' => 12,
                                'pieces root' => hex2bin('832d96b4f8b422aa75f8d40975b1a408154bc1a2bdffccf7b689386cde125a30')
                            ],
                            'child file' => [
                                '' => [
                                    'length' => 12,
                                    'pieces root' => hex2bin('832d96b4f8b422aa75f8d40975b1a408154bc1a2bdffccf7b689386cde125a30'),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'piece layers' => []
        ];

        $torrent = TorrentFile::loadFromString(Bencode::encode($data));

        $torrent->parse();
    }
}
