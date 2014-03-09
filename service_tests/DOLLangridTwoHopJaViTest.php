<?php

require_once dirname(__FILE__) . '/../helper/autoload.php';

class DOLLangridTwoHopJaViTest extends PHPUnit_Framework_TestCase implements DOLServiceTest {
    var $setting;

    // This test must complete within 3 seconds.
    var $responseTimeLimit = 3000;

    public function setUp() {
        $this->setting = json_decode(file_get_contents(dirname(__FILE__) . '/../config/langrid.json'));
    }

    private function buildService($client) {
        $client->setUserId($this->setting->user);
        $client->setPassword($this->setting->password);
        if ($this->setting->proxyHost) {
            $client->setProxy($this->setting->proxyHost, $this->setting->proxyPort);
        }
    }

    public function testTranslation() {
           $text = 'こんにちは。私は今日は百万遍に来ています。';

           $client = ClientFactory::createMultihopTranslationClient($this->setting->url . 'kyoto1.langrid:TwoHopTranslation');

           $client->addBindings(new BindingNode('FirstTranslationPL', 'KyotoUJServer', 'kyoto1.langrid'));
           $client->addBindings(new BindingNode('SecondTranslationPL', 'GoogleTranslate', 'kyoto1.langrid'));

        $this->buildService($client);
        $result = $client->multihopTranslate(Language::get('ja'), array(Language::get('en')), Language::get('vi'), $text);
        $this->assertEquals('Xin chào. Tôi đã đến một triệu lần hiện nay.', $result->target);
    }

    public function testConnection() {

    }

    public function testResponseTime() {
        $stime = microtime(true);
        $this->testTranslation();
        $etime = microtime(true);
        $this->assertTrue(($etime - $stime) / 1000 < $this->responseTimeLimit, 'Response time exceeds limit.');
    }
}
