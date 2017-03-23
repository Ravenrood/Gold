<?php

namespace Gold\Bundle\AnalysisBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertContains('Gold Exchaneg Analysis Homepage.', $client->getResponse()->getContent());
    }
}
