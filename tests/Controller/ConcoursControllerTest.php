<?php

namespace App\Tests\Controller;

use App\Entity\Concours;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ConcoursControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $concourRepository;
    private string $path = '/concours/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->concourRepository = $this->manager->getRepository(Concours::class);

        foreach ($this->concourRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Concour index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'concour[titre]' => 'Testing',
            'concour[description]' => 'Testing',
            'concour[datedebut]' => 'Testing',
            'concour[datefin]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->concourRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Concours();
        $fixture->setTitre('My Title');
        $fixture->setDescription('My Title');
        $fixture->setDatedebut('My Title');
        $fixture->setDatefin('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Concour');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Concours();
        $fixture->setTitre('Value');
        $fixture->setDescription('Value');
        $fixture->setDatedebut('Value');
        $fixture->setDatefin('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'concour[titre]' => 'Something New',
            'concour[description]' => 'Something New',
            'concour[datedebut]' => 'Something New',
            'concour[datefin]' => 'Something New',
        ]);

        self::assertResponseRedirects('/concours/');

        $fixture = $this->concourRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitre());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getDatedebut());
        self::assertSame('Something New', $fixture[0]->getDatefin());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Concours();
        $fixture->setTitre('Value');
        $fixture->setDescription('Value');
        $fixture->setDatedebut('Value');
        $fixture->setDatefin('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/concours/');
        self::assertSame(0, $this->concourRepository->count([]));
    }
}
