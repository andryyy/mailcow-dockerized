# Changelog

## [1.10.0](https://github.com/ddeboer/imap/tree/1.10.0) (2020-01-24)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.9.0...1.10.0)

**Implemented enhancements:**

- Handle comments \(CFWS\) in Date header + not-valid formats [\#439](https://github.com/ddeboer/imap/pull/439) ([pupaxxo](https://github.com/pupaxxo))
- Support PHP 7.4, require PHP 7.2 [\#433](https://github.com/ddeboer/imap/pull/433) ([Slamdunk](https://github.com/Slamdunk))

**Closed issues:**

- Invalid Date header when trying to fetching date on not RFC compliant emails [\#438](https://github.com/ddeboer/imap/issues/438)

**Merged pull requests:**

- Clear the last used mailbox cache when closing a connection [\#447](https://github.com/ddeboer/imap/pull/447) ([dhzavann](https://github.com/dhzavann))
- README: update PHP version to match composer.json [\#441](https://github.com/ddeboer/imap/pull/441) ([Slamdunk](https://github.com/Slamdunk))
- Typo [\#440](https://github.com/ddeboer/imap/pull/440) ([OskarStark](https://github.com/OskarStark))

## [1.9.0](https://github.com/ddeboer/imap/tree/1.9.0) (2019-11-25)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.8.0...1.9.0)

**Implemented enhancements:**

- Quota: let's also test the numbers [\#431](https://github.com/ddeboer/imap/pull/431) ([Slamdunk](https://github.com/Slamdunk))
- Add getQuota method using imap\_get\_quotaroot to Connection class [\#430](https://github.com/ddeboer/imap/pull/430) ([arkadiusjonczek](https://github.com/arkadiusjonczek))

**Closed issues:**

- markAsSeen\(\)  not work [\#415](https://github.com/ddeboer/imap/issues/415)

**Merged pull requests:**

- CS Fix: strip redundant php docs [\#428](https://github.com/ddeboer/imap/pull/428) ([Slamdunk](https://github.com/Slamdunk))
- Fix: Variable name [\#412](https://github.com/ddeboer/imap/pull/412) ([localheinz](https://github.com/localheinz))

## [1.8.0](https://github.com/ddeboer/imap/tree/1.8.0) (2019-04-15)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.7.2...1.8.0)

**Implemented enhancements:**

- Add phpstan-strict-rules, expose PartiInterface::getDescription\(\) [\#409](https://github.com/ddeboer/imap/pull/409) ([Slamdunk](https://github.com/Slamdunk))

## [1.7.2](https://github.com/ddeboer/imap/tree/1.7.2) (2019-04-12)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.7.1...1.7.2)

**Fixed bugs:**

- Handle message/rfc822 when content-disposition is missing [\#410](https://github.com/ddeboer/imap/pull/410) ([Daredzik](https://github.com/Daredzik))

## [1.7.1](https://github.com/ddeboer/imap/tree/1.7.1) (2019-03-18)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.7.0...1.7.1)

**Fixed bugs:**

- Encoding problem with 1.7 [\#405](https://github.com/ddeboer/imap/issues/405)
- imap\_search/imap\_sort: default params must not be passed if unspecified [\#406](https://github.com/ddeboer/imap/pull/406) ([Slamdunk](https://github.com/Slamdunk))

## [1.7.0](https://github.com/ddeboer/imap/tree/1.7.0) (2019-03-04)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.6.0...1.7.0)

**Implemented enhancements:**

- Docker and Travis differs in handling new message eols [\#404](https://github.com/ddeboer/imap/pull/404) ([Slamdunk](https://github.com/Slamdunk))
- Update PHP-CS-Fixer rules [\#403](https://github.com/ddeboer/imap/pull/403) ([Slamdunk](https://github.com/Slamdunk))
- Add charset for imap\_search or imap\_sort [\#402](https://github.com/ddeboer/imap/pull/402) ([Slamdunk](https://github.com/Slamdunk))
- PHPStan clean ups [\#400](https://github.com/ddeboer/imap/pull/400) ([Slamdunk](https://github.com/Slamdunk))
- Adding an undelete\(\) message method [\#386](https://github.com/ddeboer/imap/pull/386) ([C-Duv](https://github.com/C-Duv))

**Closed issues:**

- Convert from GBK \(X-GBK\) to UTF-8 Issue [\#395](https://github.com/ddeboer/imap/issues/395)

**Merged pull requests:**

- Add new ResourceCheckFailureException to handle imap\_check\(\) false [\#399](https://github.com/ddeboer/imap/pull/399) ([pyatnitsev](https://github.com/pyatnitsev))
- Remove GBK -\> X-GBK Alias and add X-GBK -\> GBK [\#396](https://github.com/ddeboer/imap/pull/396) ([pyatnitsev](https://github.com/pyatnitsev))
- Add Feature Requests to README.md [\#394](https://github.com/ddeboer/imap/pull/394) ([Slamdunk](https://github.com/Slamdunk))

## [1.6.0](https://github.com/ddeboer/imap/tree/1.6.0) (2018-12-04)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.5.5...1.6.0)

**Implemented enhancements:**

- Require PHP ^7.1 [\#257](https://github.com/ddeboer/imap/issues/257)
- Require PHP ^7.1 [\#383](https://github.com/ddeboer/imap/pull/383) ([Slamdunk](https://github.com/Slamdunk))
- Add ability to pass options and retries to imap\_open [\#382](https://github.com/ddeboer/imap/pull/382) ([Slamdunk](https://github.com/Slamdunk))
- Docker setup for running tests [\#374](https://github.com/ddeboer/imap/pull/374) ([LeadTechVisas](https://github.com/LeadTechVisas))
- Get messages by UID sequence [\#373](https://github.com/ddeboer/imap/pull/373) ([LeadTechVisas](https://github.com/LeadTechVisas))

**Fixed bugs:**

- Undeliverable mail: attachment parsing error [\#334](https://github.com/ddeboer/imap/issues/334)
- imap\_getmailboxes returns false; [\#134](https://github.com/ddeboer/imap/issues/134)
- Fix mailbox name as only numbers [\#381](https://github.com/ddeboer/imap/pull/381) ([Slamdunk](https://github.com/Slamdunk))
- Gracefully handle possible non-array return value of imap\_getmailboxes [\#372](https://github.com/ddeboer/imap/pull/372) ([Slamdunk](https://github.com/Slamdunk))

**Closed issues:**

- \[AUTHENTICATIONFAILED\] Authentication failed - Too many login failures [\#368](https://github.com/ddeboer/imap/issues/368)
- last folder in list [\#353](https://github.com/ddeboer/imap/issues/353)
- Caching IMAP server connections [\#88](https://github.com/ddeboer/imap/issues/88)

## [1.5.5](https://github.com/ddeboer/imap/tree/1.5.5) (2018-08-21)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.5.4...1.5.5)

**Fixed bugs:**

- Plain text attachments are not identified as Attachment parts [\#341](https://github.com/ddeboer/imap/issues/341)
- Handle plain/text attachments without Content-Type header [\#367](https://github.com/ddeboer/imap/pull/367) ([Slamdunk](https://github.com/Slamdunk))

## [1.5.4](https://github.com/ddeboer/imap/tree/1.5.4) (2018-08-19)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.5.3...1.5.4)

**Fixed bugs:**

- Very long filename, result of getFilename\(\) = NULL? [\#365](https://github.com/ddeboer/imap/issues/365)
- Support RFC2231 attachment filenames [\#366](https://github.com/ddeboer/imap/pull/366) ([Slamdunk](https://github.com/Slamdunk))

## [1.5.3](https://github.com/ddeboer/imap/tree/1.5.3) (2018-07-20)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.5.2...1.5.3)

**Fixed bugs:**

- Dates: handle UT timezone [\#361](https://github.com/ddeboer/imap/pull/361) ([Slamdunk](https://github.com/Slamdunk))

## [1.5.2](https://github.com/ddeboer/imap/tree/1.5.2) (2018-07-10)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.5.1...1.5.2)

**Fixed bugs:**

- Fails to load Message Headers [\#358](https://github.com/ddeboer/imap/issues/358)
- Handle invalid headers [\#359](https://github.com/ddeboer/imap/pull/359) ([Slamdunk](https://github.com/Slamdunk))

## [1.5.1](https://github.com/ddeboer/imap/tree/1.5.1) (2018-05-04)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.5.0...1.5.1)

**Fixed bugs:**

- getContent\(\) method returns wrong content part [\#342](https://github.com/ddeboer/imap/issues/342)
- Fix handle of attachment messages with attachments [\#343](https://github.com/ddeboer/imap/pull/343) ([Slamdunk](https://github.com/Slamdunk))

## [1.5.0](https://github.com/ddeboer/imap/tree/1.5.0) (2018-03-26)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.4.1...1.5.0)

**Implemented enhancements:**

- ImapResource: cache last opened mailbox [\#328](https://github.com/ddeboer/imap/pull/328) ([Slamdunk](https://github.com/Slamdunk))

**Fixed bugs:**

- AbstractPart::isAttachment\(\) handle unknown part type [\#302](https://github.com/ddeboer/imap/pull/302) ([Slamdunk](https://github.com/Slamdunk))

## [1.4.1](https://github.com/ddeboer/imap/tree/1.4.1) (2018-03-22)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.4.0...1.4.1)

**Fixed bugs:**

- Return value of Ddeboer\\Imap\\Message\\AbstractPart::getDecodedContent\(\) must be of the type string, boolean returned [\#284](https://github.com/ddeboer/imap/issues/284)
- base64\_decode may return false in PHP \< 7.1 [\#324](https://github.com/ddeboer/imap/pull/324) ([Slamdunk](https://github.com/Slamdunk))

**Merged pull requests:**

- Add entry in README about Mailbox::addMessage [\#325](https://github.com/ddeboer/imap/pull/325) ([soywod](https://github.com/soywod))

## [1.4.0](https://github.com/ddeboer/imap/tree/1.4.0) (2018-03-19)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.3.1...1.4.0)

**Implemented enhancements:**

- Lazy load Message [\#320](https://github.com/ddeboer/imap/pull/320) ([Slamdunk](https://github.com/Slamdunk))

**Fixed bugs:**

- Invalid argument supplied for foreach\(\) in Parameters.php line 52 [\#317](https://github.com/ddeboer/imap/issues/317)
- Message "11964" does not exist: imap\_fetchstructure\(\): Bad message number [\#310](https://github.com/ddeboer/imap/issues/310)
- imap\_mime\_header\_decode may return false [\#322](https://github.com/ddeboer/imap/pull/322) ([Slamdunk](https://github.com/Slamdunk))

## [1.3.1](https://github.com/ddeboer/imap/tree/1.3.1) (2018-03-09)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.3.0...1.3.1)

**Implemented enhancements:**

- Allow empty port [\#312](https://github.com/ddeboer/imap/pull/312) ([Slamdunk](https://github.com/Slamdunk))

**Closed issues:**

- getServerString\(\) with no port [\#311](https://github.com/ddeboer/imap/issues/311)

## [1.3.0](https://github.com/ddeboer/imap/tree/1.3.0) (2018-02-28)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.2.3...1.3.0)

**Implemented enhancements:**

- Implement bulk-move [\#306](https://github.com/ddeboer/imap/pull/306) ([particleflux](https://github.com/particleflux))

**Closed issues:**

- feature: Bulk move [\#305](https://github.com/ddeboer/imap/issues/305)

**Merged pull requests:**

- README.md: add `Unknown search criterion: OR` note [\#304](https://github.com/ddeboer/imap/pull/304) ([Slamdunk](https://github.com/Slamdunk))

## [1.2.3](https://github.com/ddeboer/imap/tree/1.2.3) (2018-02-09)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.2.2...1.2.3)

**Fixed bugs:**

- $part-\>type can be 9 [\#301](https://github.com/ddeboer/imap/issues/301)

**Merged pull requests:**

- README.md: code-coverage has higher priority than Scrutinizer [\#300](https://github.com/ddeboer/imap/pull/300) ([Slamdunk](https://github.com/Slamdunk))

## [1.2.2](https://github.com/ddeboer/imap/tree/1.2.2) (2018-02-05)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.2.1...1.2.2)

**Implemented enhancements:**

- Allow PHPUnit ^7.0 [\#296](https://github.com/ddeboer/imap/pull/296) ([Slamdunk](https://github.com/Slamdunk))

**Fixed bugs:**

- Attachment-\>getFilename return null [\#297](https://github.com/ddeboer/imap/issues/297)
- Don't handle multiplart as an attachment [\#298](https://github.com/ddeboer/imap/pull/298) ([Slamdunk](https://github.com/Slamdunk))

## [1.2.1](https://github.com/ddeboer/imap/tree/1.2.1) (2018-01-29)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.2.0...1.2.1)

**Implemented enhancements:**

- Introduce strict comparison [\#289](https://github.com/ddeboer/imap/pull/289) ([Slamdunk](https://github.com/Slamdunk))

**Fixed bugs:**

- Invalid Date header found: "Thur, 04 Jan 2018 06:44:23 +0400" [\#293](https://github.com/ddeboer/imap/issues/293)
- MessageIterator::current\(\) fails when there are no messages [\#288](https://github.com/ddeboer/imap/issues/288)
- Remove weekday while parsing date header [\#294](https://github.com/ddeboer/imap/pull/294) ([Slamdunk](https://github.com/Slamdunk))
- MessageIterator: forbid raw calls [\#290](https://github.com/ddeboer/imap/pull/290) ([Slamdunk](https://github.com/Slamdunk))

## [1.2.0](https://github.com/ddeboer/imap/tree/1.2.0) (2018-01-15)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.1.2...1.2.0)

**Implemented enhancements:**

- Make imap\_append\(\) optional arguments reachable [\#280](https://github.com/ddeboer/imap/pull/280) ([Slamdunk](https://github.com/Slamdunk))
- PHPStan: introduce static analysis [\#276](https://github.com/ddeboer/imap/pull/276) ([Slamdunk](https://github.com/Slamdunk))

**Fixed bugs:**

- getAttachments\(\) problem when mixin inline and attachment [\#281](https://github.com/ddeboer/imap/issues/281)
- UnexpectedEncodingException: Cannot decode "5" [\#278](https://github.com/ddeboer/imap/issues/278)
- Handle correctly multiple nested attachments [\#283](https://github.com/ddeboer/imap/pull/283) ([Slamdunk](https://github.com/Slamdunk))
- Manageable UnexpectedEncodingException [\#282](https://github.com/ddeboer/imap/pull/282) ([Slamdunk](https://github.com/Slamdunk))

**Closed issues:**

- Appending mail with options [\#279](https://github.com/ddeboer/imap/issues/279)

## [1.1.2](https://github.com/ddeboer/imap/tree/1.1.2) (2017-12-12)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.1.1...1.1.2)

**Fixed bugs:**

- Unsupported charset "134": mb\_convert\_encoding\(\): Illegal character encoding specified [\#270](https://github.com/ddeboer/imap/issues/270)
- Support Microsoft charset values [\#271](https://github.com/ddeboer/imap/pull/271) ([Slamdunk](https://github.com/Slamdunk))

## [1.1.1](https://github.com/ddeboer/imap/tree/1.1.1) (2017-11-10)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.1.0...1.1.1)

**Implemented enhancements:**

- Transcoder: expand charset aliases list [\#267](https://github.com/ddeboer/imap/pull/267) ([Slamdunk](https://github.com/Slamdunk))

**Fixed bugs:**

- Charset aliases: fix to lowercase search [\#266](https://github.com/ddeboer/imap/pull/266) ([Slamdunk](https://github.com/Slamdunk))

**Merged pull requests:**

- README.md: add timeout note [\#263](https://github.com/ddeboer/imap/pull/263) ([Slamdunk](https://github.com/Slamdunk))

## [1.1.0](https://github.com/ddeboer/imap/tree/1.1.0) (2017-11-06)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.0.8...1.1.0)

**Implemented enhancements:**

- Deprecate MessageInterface::maskAsSeen\(\) in favour of MessageInterface::markAsSeen\(\) [\#255](https://github.com/ddeboer/imap/pull/255) ([Slamdunk](https://github.com/Slamdunk))
- Lazy load structured Headers [\#250](https://github.com/ddeboer/imap/pull/250) ([Slamdunk](https://github.com/Slamdunk))
- Implement imap\_thread [\#249](https://github.com/ddeboer/imap/pull/249) ([Slamdunk](https://github.com/Slamdunk))
- Require ext-iconv [\#248](https://github.com/ddeboer/imap/pull/248) ([Slamdunk](https://github.com/Slamdunk))
- Message Part: expose $partNumber [\#244](https://github.com/ddeboer/imap/pull/244) ([wujku](https://github.com/wujku))
- Add Mockability helpers and documentation [\#236](https://github.com/ddeboer/imap/pull/236) ([Slamdunk](https://github.com/Slamdunk))
- Add missing interface change for \#225 [\#233](https://github.com/ddeboer/imap/pull/233) ([Slamdunk](https://github.com/Slamdunk))
- Connection: check if the connection is still active with `imap\_ping` [\#232](https://github.com/ddeboer/imap/pull/232) ([wujku](https://github.com/wujku))
- Message: add `References` and `In-Reply-To` headers shortcuts [\#230](https://github.com/ddeboer/imap/pull/230) ([wujku](https://github.com/wujku))
- Added bulk set / clear flags functionality for mailbox messages [\#225](https://github.com/ddeboer/imap/pull/225) ([wujku](https://github.com/wujku))

**Merged pull requests:**

- make docs more obvious [\#252](https://github.com/ddeboer/imap/pull/252) ([lgg](https://github.com/lgg))
- README.md: add Table of Contents with Travis checker [\#234](https://github.com/ddeboer/imap/pull/234) ([Slamdunk](https://github.com/Slamdunk))

## [1.0.8](https://github.com/ddeboer/imap/tree/1.0.8) (2017-10-27)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.0.7...1.0.8)

**Implemented enhancements:**

- Headers: no catchable exception [\#246](https://github.com/ddeboer/imap/issues/246)
- imap\_thread [\#113](https://github.com/ddeboer/imap/issues/113)

**Fixed bugs:**

- \[TypeError\] Return value of Ddeboer\Imap\Message\AbstractMessage::getId\(\) must be of the type string, null returned [\#253](https://github.com/ddeboer/imap/issues/253)
- BasicMessageInterface::getId\(\) can be null [\#254](https://github.com/ddeboer/imap/pull/254) ([Slamdunk](https://github.com/Slamdunk))

## [1.0.7](https://github.com/ddeboer/imap/tree/1.0.7) (2017-10-16)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.0.6...1.0.7)

**Fixed bugs:**

- Problem with a IMAP resource stream [\#245](https://github.com/ddeboer/imap/issues/245)
- IMAP resource must be checked at every call for mailbox context [\#247](https://github.com/ddeboer/imap/pull/247) ([Slamdunk](https://github.com/Slamdunk))

## [1.0.6](https://github.com/ddeboer/imap/tree/1.0.6) (2017-10-12)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.0.5...1.0.6)

**Fixed bugs:**

- \[TypeError\] Return value of AbstractMessage::getFrom\(\) must be an instance of EmailAddress, null returned [\#241](https://github.com/ddeboer/imap/issues/241)
- Message: Date header can be absent [\#243](https://github.com/ddeboer/imap/pull/243) ([Slamdunk](https://github.com/Slamdunk))
- Message: From header can be absent [\#242](https://github.com/ddeboer/imap/pull/242) ([Slamdunk](https://github.com/Slamdunk))

## [1.0.5](https://github.com/ddeboer/imap/tree/1.0.5) (2017-10-12)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.0.4...1.0.5)

**Fixed bugs:**

- Use set\_error\_handler with late exception [\#240](https://github.com/ddeboer/imap/pull/240) ([Slamdunk](https://github.com/Slamdunk))

## [1.0.4](https://github.com/ddeboer/imap/tree/1.0.4) (2017-10-11)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.0.3...1.0.4)

**Implemented enhancements:**

- Avoid \(set|restor\)\_error\_handler [\#239](https://github.com/ddeboer/imap/pull/239) ([Slamdunk](https://github.com/Slamdunk))

**Fixed bugs:**

- Current Transcoder class does not support all charsets.  [\#237](https://github.com/ddeboer/imap/issues/237)
- Relay also iconv during decoding [\#238](https://github.com/ddeboer/imap/pull/238) ([Slamdunk](https://github.com/Slamdunk))

## [1.0.3](https://github.com/ddeboer/imap/tree/1.0.3) (2017-10-11)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.0.2...1.0.3)

**Fixed bugs:**

- Attachment::getFilename\(\) may be null on inline-att, widen return type [\#235](https://github.com/ddeboer/imap/pull/235) ([wujku](https://github.com/wujku))

## [1.0.2](https://github.com/ddeboer/imap/tree/1.0.2) (2017-10-06)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.0.1...1.0.2)

**Fixed bugs:**

- Issue with saving XML attachments [\#228](https://github.com/ddeboer/imap/issues/228)
- Do not charset-decode attachments [\#231](https://github.com/ddeboer/imap/pull/231) ([Slamdunk](https://github.com/Slamdunk))

## [1.0.1](https://github.com/ddeboer/imap/tree/1.0.1) (2017-10-05)

[Full Changelog](https://github.com/ddeboer/imap/compare/1.0.0...1.0.1)

**Fixed bugs:**

- Error with attachment charset [\#226](https://github.com/ddeboer/imap/issues/226)
- If charset is not specified defaults to "us-ascii" [\#227](https://github.com/ddeboer/imap/pull/227) ([Slamdunk](https://github.com/Slamdunk))

## [1.0.0](https://github.com/ddeboer/imap/tree/1.0.0) (2017-10-04)

[Full Changelog](https://github.com/ddeboer/imap/compare/0.5.2...1.0.0)

**Implemented enhancements:**

- Need getAll for headers [\#200](https://github.com/ddeboer/imap/issues/200)
- Tests: implement @covers to avoid false positive on code-coverage [\#188](https://github.com/ddeboer/imap/issues/188)
- Remove commented code [\#174](https://github.com/ddeboer/imap/issues/174)
- Regex in SearchExpressions [\#157](https://github.com/ddeboer/imap/issues/157)
- How do I get unread messages count? [\#98](https://github.com/ddeboer/imap/issues/98)
- Add mocking ability through Interfaces [\#221](https://github.com/ddeboer/imap/pull/221) ([Slamdunk](https://github.com/Slamdunk))
- Wrap imap resource to periodically check its status [\#220](https://github.com/ddeboer/imap/pull/220) ([Slamdunk](https://github.com/Slamdunk))
- Add more coding-standard rules [\#218](https://github.com/ddeboer/imap/pull/218) ([Slamdunk](https://github.com/Slamdunk))
- Always keep unseen: remove keepUnseen, add markAsSeen [\#217](https://github.com/ddeboer/imap/pull/217) ([Slamdunk](https://github.com/Slamdunk))
- Embedded messages: refactor \#106 [\#216](https://github.com/ddeboer/imap/pull/216) ([Slamdunk](https://github.com/Slamdunk))
- Headers now extends \ArrayIterator [\#215](https://github.com/ddeboer/imap/pull/215) ([Slamdunk](https://github.com/Slamdunk))
- Implement imap\_mail\_copy [\#214](https://github.com/ddeboer/imap/pull/214) ([Slamdunk](https://github.com/Slamdunk))
- Imap sort [\#213](https://github.com/ddeboer/imap/pull/213) ([Slamdunk](https://github.com/Slamdunk))
- Increased code-coverage [\#211](https://github.com/ddeboer/imap/pull/211) ([Slamdunk](https://github.com/Slamdunk))
- Update to PHPUnit ^6.2 [\#209](https://github.com/ddeboer/imap/pull/209) ([Slamdunk](https://github.com/Slamdunk))
- Use specific exceptions to ease user catches [\#208](https://github.com/ddeboer/imap/pull/208) ([Slamdunk](https://github.com/Slamdunk))
- Wrap Exception on invalid Date header [\#205](https://github.com/ddeboer/imap/pull/205) ([Slamdunk](https://github.com/Slamdunk))
- Add tests for \#144 set flags functionalities [\#203](https://github.com/ddeboer/imap/pull/203) ([Slamdunk](https://github.com/Slamdunk))
- Add imap\_fetchheader\(\) functionality to get raw headers [\#202](https://github.com/ddeboer/imap/pull/202) ([Slamdunk](https://github.com/Slamdunk))
- Parse all email type headers [\#199](https://github.com/ddeboer/imap/pull/199) ([Slamdunk](https://github.com/Slamdunk))
- Test search conditions [\#198](https://github.com/ddeboer/imap/pull/198) ([Slamdunk](https://github.com/Slamdunk))
- Mailbox: get status [\#192](https://github.com/ddeboer/imap/pull/192) ([Slamdunk](https://github.com/Slamdunk))
- SearchExpression is a Search\ConditionInterface [\#191](https://github.com/ddeboer/imap/pull/191) ([Slamdunk](https://github.com/Slamdunk))
- SearchCondition: \_\_toString\(\) -\> toString\(\) [\#187](https://github.com/ddeboer/imap/pull/187) ([Slamdunk](https://github.com/Slamdunk))
- Retain imap\_getmailboxes\(\) results [\#184](https://github.com/ddeboer/imap/pull/184) ([Slamdunk](https://github.com/Slamdunk))
- Add type hints and return types [\#183](https://github.com/ddeboer/imap/pull/183) ([Slamdunk](https://github.com/Slamdunk))
- Exception: increase verbosity with imap\_alerts\(\) and imap\_errors\(\) [\#182](https://github.com/ddeboer/imap/pull/182) ([Slamdunk](https://github.com/Slamdunk))
- Add coding-standards [\#181](https://github.com/ddeboer/imap/pull/181) ([Slamdunk](https://github.com/Slamdunk))
- Travis: re-enable code-coverage on scrutinizer [\#177](https://github.com/ddeboer/imap/pull/177) ([Slamdunk](https://github.com/Slamdunk))
- Add .gitattributes to remove from releases unneded files [\#173](https://github.com/ddeboer/imap/pull/173) ([Slamdunk](https://github.com/Slamdunk))
- Travis: use local Dovecot installation [\#170](https://github.com/ddeboer/imap/pull/170) ([Slamdunk](https://github.com/Slamdunk))
- Need all Headers in string format [\#149](https://github.com/ddeboer/imap/pull/149) ([FlashWS](https://github.com/FlashWS))
- Get raw mail [\#146](https://github.com/ddeboer/imap/pull/146) ([styxit](https://github.com/styxit))
- add getBcc\(\), Set, Clear Flag\(\Seen, \Answered, \Flagged, \Deleted, and \Draft\), getHeadersRaw\(\) [\#144](https://github.com/ddeboer/imap/pull/144) ([trungpv93](https://github.com/trungpv93))

**Fixed bugs:**

- Search\Condition needs charset escaping/indication [\#190](https://github.com/ddeboer/imap/issues/190)
- imap\_utf7\_\(encode|decode\) -\> mb\_convert\_encoding [\#185](https://github.com/ddeboer/imap/issues/185)
- España [\#176](https://github.com/ddeboer/imap/issues/176)
- getHeaders\(\) decode broke information [\#171](https://github.com/ddeboer/imap/issues/171)
- Date format for date search condition [\#168](https://github.com/ddeboer/imap/issues/168)
- Error when trying fetch messages from container [\#167](https://github.com/ddeboer/imap/issues/167)
- Attachment encoding error [\#158](https://github.com/ddeboer/imap/issues/158)
- getFilename\(\) is empty and no attachment, even when there is an attachment. [\#142](https://github.com/ddeboer/imap/issues/142)
- Encoding issues [\#136](https://github.com/ddeboer/imap/issues/136)
- URGENT: The timezone could not be found in the database [\#135](https://github.com/ddeboer/imap/issues/135)
- Incorrect transcoding of text attachments [\#132](https://github.com/ddeboer/imap/issues/132)
- Undefined offset  [\#123](https://github.com/ddeboer/imap/issues/123)
- ICS file not supported as attachment [\#120](https://github.com/ddeboer/imap/issues/120)
- Should iconv be a requirement? [\#115](https://github.com/ddeboer/imap/issues/115)
- KeepUnseen doen't work [\#92](https://github.com/ddeboer/imap/issues/92)
- PHP Fatal error Failed to parse time string in ddeboer/imap/src/Message.php [\#89](https://github.com/ddeboer/imap/issues/89)
- encoding issue [\#85](https://github.com/ddeboer/imap/issues/85)
- keepUnseen not working correctly with Hotmail [\#84](https://github.com/ddeboer/imap/issues/84)
- Iconv Exception [\#78](https://github.com/ddeboer/imap/issues/78)
- $message-\>getAttachments\(\) doesn't recognize some attachments [\#74](https://github.com/ddeboer/imap/issues/74)
- Message::move\(\) doesn't work. [\#73](https://github.com/ddeboer/imap/issues/73)
- Message\Part: part number must distinguish original message [\#223](https://github.com/ddeboer/imap/pull/223) ([Slamdunk](https://github.com/Slamdunk))
- Recursive Embedded email body bug [\#222](https://github.com/ddeboer/imap/pull/222) ([Slamdunk](https://github.com/Slamdunk))
- Exclude HTML from allowed attachment subtype [\#212](https://github.com/ddeboer/imap/pull/212) ([Slamdunk](https://github.com/Slamdunk))
- Fix imap\_mail\_move behaviour and test it [\#207](https://github.com/ddeboer/imap/pull/207) ([Slamdunk](https://github.com/Slamdunk))
- Undefined encoding: throw exception [\#197](https://github.com/ddeboer/imap/pull/197) ([Slamdunk](https://github.com/Slamdunk))
- Message charset: mb\_convert\_encoding + aliases [\#196](https://github.com/ddeboer/imap/pull/196) ([Slamdunk](https://github.com/Slamdunk))
- Mailbox: only UTF-8 names [\#193](https://github.com/ddeboer/imap/pull/193) ([Slamdunk](https://github.com/Slamdunk))
- Search\Date\AbstractDate: fix format to RFC-3501 [\#189](https://github.com/ddeboer/imap/pull/189) ([Slamdunk](https://github.com/Slamdunk))
- Travis: fix failing tests [\#172](https://github.com/ddeboer/imap/pull/172) ([Slamdunk](https://github.com/Slamdunk))
- Return body of single-part HTML message as HTML, not text [\#101](https://github.com/ddeboer/imap/pull/101) ([joker806](https://github.com/joker806))
- Implement "undisclosed recipients" addresses [\#86](https://github.com/ddeboer/imap/pull/86) ([darit](https://github.com/darit))

**Closed issues:**

- Potential memory issue with attachments [\#195](https://github.com/ddeboer/imap/issues/195)
- Explain Message::delete [\#175](https://github.com/ddeboer/imap/issues/175)
- Get raw message [\#161](https://github.com/ddeboer/imap/issues/161)
- Composer install problem [\#160](https://github.com/ddeboer/imap/issues/160)
- Transcoder not exist [\#154](https://github.com/ddeboer/imap/issues/154)
- The library doesn't support using sort by [\#151](https://github.com/ddeboer/imap/issues/151)
- Office 365 - Array to string conversion error [\#131](https://github.com/ddeboer/imap/issues/131)
- Is there a method to turn a seen message into an "unseen" one ? [\#130](https://github.com/ddeboer/imap/issues/130)
- Create mailbox [\#126](https://github.com/ddeboer/imap/issues/126)
- Move and Delete Message not working [\#112](https://github.com/ddeboer/imap/issues/112)
- Problem on production server [\#111](https://github.com/ddeboer/imap/issues/111)
- Authentication failed for a Gmail account [\#109](https://github.com/ddeboer/imap/issues/109)
- A method to run IMAP commands? [\#83](https://github.com/ddeboer/imap/issues/83)

**Merged pull requests:**

- Update README.md to latest develop changes [\#224](https://github.com/ddeboer/imap/pull/224) ([Slamdunk](https://github.com/Slamdunk))
- Add Filippo Tessarotto as an author of the package [\#219](https://github.com/ddeboer/imap/pull/219) ([Slamdunk](https://github.com/Slamdunk))
- README.md: call Connection::expunge after move and delete [\#210](https://github.com/ddeboer/imap/pull/210) ([Slamdunk](https://github.com/Slamdunk))
- Remove misleading Mailbox::expunge\(\) [\#206](https://github.com/ddeboer/imap/pull/206) ([Slamdunk](https://github.com/Slamdunk))
- Add CHANGELOG.md [\#194](https://github.com/ddeboer/imap/pull/194) ([Slamdunk](https://github.com/Slamdunk))
- README.md updates [\#178](https://github.com/ddeboer/imap/pull/178) ([Slamdunk](https://github.com/Slamdunk))

## [0.5.2](https://github.com/ddeboer/imap/tree/0.5.2) (2015-12-03)

[Full Changelog](https://github.com/ddeboer/imap/compare/0.5.1...0.5.2)

**Closed issues:**

- $message-\>getAttachments\(\) returns null if message has no attachments [\#80](https://github.com/ddeboer/imap/issues/80)
- Email objects visibility [\#76](https://github.com/ddeboer/imap/issues/76)

**Merged pull requests:**

- Fixed the keepUnseen method [\#95](https://github.com/ddeboer/imap/pull/95) ([aeyoll](https://github.com/aeyoll))
- Mark Mailbox as countable, fix doc comments [\#91](https://github.com/ddeboer/imap/pull/91) ([krzysiekpiasecki](https://github.com/krzysiekpiasecki))
- Message::getAttachments confirm to signature [\#82](https://github.com/ddeboer/imap/pull/82) ([boekkooi](https://github.com/boekkooi))
- Added hasMailbox to Connection [\#81](https://github.com/ddeboer/imap/pull/81) ([boekkooi](https://github.com/boekkooi))
- Make sure imap connection are reopened [\#79](https://github.com/ddeboer/imap/pull/79) ([joserobleda](https://github.com/joserobleda))

## [0.5.1](https://github.com/ddeboer/imap/tree/0.5.1) (2015-02-01)

[Full Changelog](https://github.com/ddeboer/imap/compare/0.5.0...0.5.1)

**Closed issues:**

- imap\_open error  [\#72](https://github.com/ddeboer/imap/issues/72)
- $message-\>getAttachments\(\) does not return anything, even though a message has at least one attachment [\#71](https://github.com/ddeboer/imap/issues/71)
- Prepare docs for 1.0 [\#69](https://github.com/ddeboer/imap/issues/69)
- "date" header is not reliable [\#63](https://github.com/ddeboer/imap/issues/63)
- File Attachments don't show up [\#55](https://github.com/ddeboer/imap/issues/55)

**Merged pull requests:**

- Add support for attachments without content disposition [\#70](https://github.com/ddeboer/imap/pull/70) ([ddeboer](https://github.com/ddeboer))

## [0.5.0](https://github.com/ddeboer/imap/tree/0.5.0) (2015-01-24)

[Full Changelog](https://github.com/ddeboer/imap/compare/0.4.0...0.5.0)

**Closed issues:**

- Use utf8\_encode\(\) function to encode content [\#66](https://github.com/ddeboer/imap/issues/66)
- Please add function order by date [\#59](https://github.com/ddeboer/imap/issues/59)
- mb\_convert\_encoding breaks code [\#57](https://github.com/ddeboer/imap/issues/57)
- How get I getMessages but newest first ... [\#11](https://github.com/ddeboer/imap/issues/11)

## [0.4.0](https://github.com/ddeboer/imap/tree/0.4.0) (2015-01-04)

[Full Changelog](https://github.com/ddeboer/imap/compare/0.3.1...0.4.0)

**Closed issues:**

- Please add 6th parameter to imap\_open call [\#62](https://github.com/ddeboer/imap/issues/62)
- Should Message::delete\(\) use the Message UID? [\#46](https://github.com/ddeboer/imap/issues/46)
- mb\_convert\_encoding\(\): Illegal character encoding specified [\#35](https://github.com/ddeboer/imap/issues/35)
- Deleting a message isn't working [\#30](https://github.com/ddeboer/imap/issues/30)
- imap\_header doesn't work with message uid [\#26](https://github.com/ddeboer/imap/issues/26)

**Merged pull requests:**

- Added basic requirement [\#61](https://github.com/ddeboer/imap/pull/61) ([nikoskip](https://github.com/nikoskip))
- FIX: PHP error: "Cannot declare class Ddeboer\Imap\Search\Text\Text ..." [\#58](https://github.com/ddeboer/imap/pull/58) ([racztiborzoltan](https://github.com/racztiborzoltan))
- Message::delete sets the FT\_UID flag.  Fixes \#30 Fixes \#46 [\#54](https://github.com/ddeboer/imap/pull/54) ([ctalbot](https://github.com/ctalbot))
- Allow binary-encoded part content [\#48](https://github.com/ddeboer/imap/pull/48) ([joker806](https://github.com/joker806))
- Fix CS [\#47](https://github.com/ddeboer/imap/pull/47) ([xelan](https://github.com/xelan))
- fixed typo [\#45](https://github.com/ddeboer/imap/pull/45) ([xelan](https://github.com/xelan))

## [0.3.1](https://github.com/ddeboer/imap/tree/0.3.1) (2014-08-11)

[Full Changelog](https://github.com/ddeboer/imap/compare/0.3.0...0.3.1)

**Merged pull requests:**

- \imap\_header dosen't work with UID [\#44](https://github.com/ddeboer/imap/pull/44) ([ysramirez](https://github.com/ysramirez))

## [0.3.0](https://github.com/ddeboer/imap/tree/0.3.0) (2014-08-10)

[Full Changelog](https://github.com/ddeboer/imap/compare/0.2...0.3.0)

**Closed issues:**

- please remove useless wiki [\#42](https://github.com/ddeboer/imap/issues/42)
- Travis tests allways fail? [\#40](https://github.com/ddeboer/imap/issues/40)
- Garbled e-mail body encoding [\#27](https://github.com/ddeboer/imap/issues/27)
- Improve docs [\#25](https://github.com/ddeboer/imap/issues/25)
- "undisclosed-recipients" throws error [\#23](https://github.com/ddeboer/imap/issues/23)

**Merged pull requests:**

- correct minor typo [\#43](https://github.com/ddeboer/imap/pull/43) ([cordoval](https://github.com/cordoval))
- Utf-8 encode body content. [\#39](https://github.com/ddeboer/imap/pull/39) ([cmoralesweb](https://github.com/cmoralesweb))
- Fix regex parsing the date header \(allowing multiple brackets\) [\#38](https://github.com/ddeboer/imap/pull/38) ([joker806](https://github.com/joker806))
- Allow empty connection flags [\#34](https://github.com/ddeboer/imap/pull/34) ([joker806](https://github.com/joker806))
- Fixed typo [\#32](https://github.com/ddeboer/imap/pull/32) ([abhinavkumar940](https://github.com/abhinavkumar940))

## [0.2](https://github.com/ddeboer/imap/tree/0.2) (2013-11-24)

[Full Changelog](https://github.com/ddeboer/imap/compare/0.1...0.2)

## [0.1](https://github.com/ddeboer/imap/tree/0.1) (2013-11-22)

[Full Changelog](https://github.com/ddeboer/imap/compare/c02d49cdb9246901bb00d211a0f2aba208f6fab6...0.1)

**Closed issues:**

- Prevent setting SEEN flag [\#20](https://github.com/ddeboer/imap/issues/20)
- Add tests [\#18](https://github.com/ddeboer/imap/issues/18)
- delete messages [\#9](https://github.com/ddeboer/imap/issues/9)
- README is missing basic usage [\#7](https://github.com/ddeboer/imap/issues/7)
- Subject and other texts are decoded incorrectly  [\#3](https://github.com/ddeboer/imap/issues/3)

**Merged pull requests:**

- also fetch inline attachments [\#24](https://github.com/ddeboer/imap/pull/24) ([kaiserlos](https://github.com/kaiserlos))
- since leading slash is always needed [\#22](https://github.com/ddeboer/imap/pull/22) ([huglester](https://github.com/huglester))
- Added missed createMailbox\($name\) function [\#19](https://github.com/ddeboer/imap/pull/19) ([burci](https://github.com/burci))
- Added move and delete function to message + expunge function [\#17](https://github.com/ddeboer/imap/pull/17) ([burci](https://github.com/burci))
- Clean up some unused variable [\#16](https://github.com/ddeboer/imap/pull/16) ([burci](https://github.com/burci))
- Fixed mailbox encoding [\#15](https://github.com/ddeboer/imap/pull/15) ([burci](https://github.com/burci))
- Create new mailbox [\#14](https://github.com/ddeboer/imap/pull/14) ([burci](https://github.com/burci))
- Fixed bug in getDecodedContent with 'format=flowed' email [\#13](https://github.com/ddeboer/imap/pull/13) ([burci](https://github.com/burci))
- Fixed date parsing for some imap servers [\#12](https://github.com/ddeboer/imap/pull/12) ([thelfensdrfer](https://github.com/thelfensdrfer))
- Add support for more complex search expressions. [\#10](https://github.com/ddeboer/imap/pull/10) ([jamesiarmes](https://github.com/jamesiarmes))
- Allow user to change server connection flags [\#6](https://github.com/ddeboer/imap/pull/6) ([mvar](https://github.com/mvar))
- Improvements in EmailAddress class [\#4](https://github.com/ddeboer/imap/pull/4) ([mvar](https://github.com/mvar))



\* *This Changelog was automatically generated by [github_changelog_generator](https://github.com/github-changelog-generator/github-changelog-generator)*
