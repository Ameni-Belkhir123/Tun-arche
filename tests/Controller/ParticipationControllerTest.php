<?php

namespace App\Tests\Controller;

use App\Entity\Participation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ParticipationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $participationRepository;
    private string $path = '/participation/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->participationRepository = $this->manager->getRepository(Participation::class);

        foreach ($this->participationRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Participation index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'participation[nbrvotes]' => 'Testing',
            'participation[date_inscription]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->participationRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Participation();
        $fixture->setNbrvotes('My Title');
        $fixture->setDate_inscription('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Participation');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Participation();
        $fixture->setNbrvotes('Value');
        $fixture->setDate_inscription('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'participation[nbrvotes]' => 'Something New',
            'participation[date_inscription]' => 'Something New',
        ]);

        self::assertResponseRedirects('/participation/');

        $fixture = $this->participationRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNbrvotes());
        self::assertSame('Something New', $fixture[0]->getDate_inscription());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Participation();
        $fixture->setNbrvotes('Value');
        $fixture->setDate_inscription('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/participation/');
        self::assertSame(0, $this->participationRepository->count([]));
    }
}
