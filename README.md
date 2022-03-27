# PHP Bencode Library

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/14bb9525a5a343079e45d9501dac1b4c)](https://app.codacy.com/manual/rhilipruan/Bencode?utm_source=github.com&utm_medium=referral&utm_content=Rhilip/Bencode&utm_campaign=Badge_Grade_Dashboard)
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FRhilip%2FBencode.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2FRhilip%2FBencode?ref=badge_shield)

[Bencode](https://en.wikipedia.org/wiki/Bencode) is the encoding used by the peer-to-peer file sharing system
[BitTorrent](https://opensource.org/licenses/MIT) for storing and transmitting loosely structured data.

This is a pure PHP library that allows you to encode and decode Bencode data, with torrent file parse and vaildate.

This library is fork from [OPSnet/bencode-torrent](https://github.com/OPSnet/bencode-torrent),
with same method like [sandfoxme/bencode](https://github.com/arokettu/bencode), [sandfoxme/torrent-file](https://github.com/arokettu/torrent-file)

## Installation

```shell script
composer require rhilip/bencode
```

if you don't use `Rhilip\Bencode\TorrentFile` class, you can specific version to `1.x.x`

```shell script
composer require rhilip/bencode:1.2.0
```

**The only Break Change is `ParseErrorException` in `1.x.x` rename to `ParseException` in `2.x.x`.**

## Usage

### Class `Rhilip\Bencode\Bencode`

A pure PHP class to encode and decode Bencode data from file path and string.

```php
<?php

require '/path/to/vendor/autoload.php';

use Rhilip\Bencode\Bencode;
use Rhilip\Bencode\ParseException;

// Decodes a BEncoded string
Bencode::decode($string);

// Encodes string/array/int to a BEncoded string
Bencode::encode($data);

// Decodes a BEncoded file From path
Bencode::load($path);

// Encodes string/array/int to a BEncoded file
Bencode::dump($path, $data);

// With Error Catch
try {
    Bencode::decode('wrong_string');
} catch (ParseException $e) {
    // do something
}
```

### Class `Rhilip\Bencode\TorrentFile`

A pure PHP class to work with torrent files

note: Add in version 2

```php
<?php

require '/path/to/vendor/autoload.php';

use Rhilip\Bencode\TorrentFile;
use Rhilip\Bencode\ParseException;

// Defined Const
print(TorrentFile::PROTOCOL_V1); // v1
print(TorrentFile::PROTOCOL_V2); // v2
print(TorrentFile::PROTOCOL_HYBRID); // hybrid
print(TorrentFile::FILEMODE_SINGLE); // single
print(TorrentFile::FILEMODE_MULTI); // multi

// Load Torrent and get instance
try {
    $torrent = TorrentFile::load($path);
    $torrent = TorrentFile::loadFromString($string);
} catch (ParseException $e) {
    // do something
}

// Save Torrent to path or string (for echo)
$dumpStatus = $torrent->dump($path);
print($torrent->dumpToString());

// Work with Root Fields
$torrent->getRootData();   // $root;
$rootField = $torrent->getRootField($field, ?$default);   // $root[$field] ?? $default;
$torrent->setRootField($field, $value);  // $root[$field] = $value;
$torrent->unsetRootField($field);  // unset($root[$field]);
$torrent->cleanRootFields(?$allowedKeys);  // remove fields which is not allowed in root

$torrent->setAnnounce('udp://example.com/announce');
$announce = $torrent->getAnnounce();

$torrent->setAnnounceList([['https://example1.com/announce'], ['https://example2.com/announce'], 'https://example3.com/announce']);
$announceList = $torrent->getAnnounceList();

$torrent->setComment('Rhilip\'s Torrent');
$comment = $torrent->getComment();

$torrent->setCreatedBy('Rhilip');
$createdBy = $torrent->getCreatedBy();

$torrent->setCreationDate(time());
$creationDate = $torrent->getCreationDate();

$torrent->setHttpSeeds(['udp://example.com/seed']);
$httpSeeds = $torrent->getHttpSeeds();

$torrent->setNodes(['udp://example.com/seed']);
$nodes = $torrent->getNodes();

$torrent->setUrlList(['udp://example.com/seed']);
$urlList = $torrent->getUrlList();

// Work with Info Field
$torrent->getInfoData();   // $root['info'];
$infoField = $torrent->getInfoField($field, ?$default);  // $info[$field] ?? $default;
$torrent->setInfoField($field, $value); // $info[$field] = $value;
$torrent->unsetInfoField($field);  // unset($info[$field]);
$torrent->cleanInfoFields(?$allowedKeys);  // remove fields which is not allowed in info

$protocol = $torrent->getProtocol();  // TorrentFile::PROTOCOL_{V1,V2,HYBRID}
$fileMode = $torrent->getFileMode();  // TorrentFile::FILEMODE_{SINGLE,MULTI}

$torrent->getInfoHashV1(?$binary);  // If $binary is true return 20-bytes string, otherwise 40-character hexadecimal number
$torrent->getInfoHashV2(?$binary);  // If $binary is true return 32-bytes string, otherwise 64-character hexadecimal number
$torrent->getInfoHash(?$binary);   // return v2-infohash if there is one, otherwise return v1-infohash
$torrent->getInfoHashs(?$binary);  // return [TorrentFile::PROTOCOL_V1 => v1-infohash, TorrentFile::PROTOCOL_V2 => v2-infohash]
$torrent->getInfoHashV1ForAnnounce();  // return the v1 info-hash in announce ( 20-bytes string )
$torrent->getInfoHashV2ForAnnounce();  // return the v2 (truncated) info-hash in announce
$torrent->getInfoHashsForAnnnounce();  // same as getInfoHashs() but in announce

$torrent->getPieceLength();  // int

try {
    $torrent->setName($name);
} catch(\InvalidArgumentException $e) {
    // Do something
}
$name = $torrent->getName();

$torrent->setSouce('Rhilip\'s blog');
$source = $torrent->getSource();

$private = $torrent->isPrivate();  // true or false
$torrent->setPrivate(true);

// Work with torrent, it will try to parse torrent ( cost time )
$torrent->setParseValidator(function ($filename, $path) {
    /**
     * Before parse torrent ( call getSize, getFileCount, getFileList, getFileTree method ),
     * you can set a validator to test if filename or path is valid,
     * And break parse process by any throw Exception.
     *
     * Note: Since we get $filename by use end($path),
     * you should be care about the the internal pointer of $path,
     * It means `current($path) === $filename` and `next($path) === false`
     */
    print_r([$filename, $path]);
    if (str_contains($filename, 'F**k')) {
        throw new ParseException('Not allowed filename in torrent');
    }
});

/**
 * Note: Since we prefer to parse `file tree` in info dict in v2 or hybrid torrent,
 * The padding file will not count in size, fileCount, fileList and fileTree.
 */
$size = $torrent->getSize();
$count = $torrent->getFileCount();
$fileList = $torrent->getFileList();
$fileTree = $torrent->getFileTree();

// Other method
$torrent->cleanCache();
```

## License

The library is available as open source under the terms of the [MIT License](https://opensource.org/licenses/MIT).

[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FRhilip%2FBencode.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2FRhilip%2FBencode?ref=badge_large)
