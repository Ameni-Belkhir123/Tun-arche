<?php

namespace App\Tests\Controller;

use App\Entity\Formation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FormationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $formationRepository;
    private string $path = '/formation/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->formationRepository = $this->manager->getRepository(Formation::class);

        foreach ($this->formationRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Formation index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'formation[titre]' => 'Testing',
            'formation[description]' => 'Testing',
            'formation[datedebut]' => 'Testing',
            'formation[datefin]' => 'Testing',
            'formation[nbrplaces]' => 'Testing',
            'formation[link]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->formationRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Formation();
        $fixture->setTitre('My Title');
        $fixture->setDescription('My Title');
        $fixture->setDatedebut('My Title');
        $fixture->setDatefin('My Title');
        $fixture->setNbrplaces('My Title');
        $fixture->setLink('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Formation');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Formation();
        $fixture->setTitre('Value');
        $fixture->setDescription('Value');
        $fixture->setDatedebut('Value');
        $fixture->setDatefin('Value');
        $fixture->setNbrplaces('Value');
        $fixture->setLink('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'formation[titre]' => 'Something New',
            'formation[description]' => 'Something New',
            'formation[datedebut]' => 'Something New',
            'formation[datefin]' => 'Something New',
            'formation[nbrplaces]' => 'Something New',
            'formation[link]' => 'Something New',
        ]);

        self::assertResponseRedirects('/formation/');

        $fixture = $this->formationRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitre());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getDatedebut());
        self::assertSame('Something New', $fixture[0]->getDatefin());
        self::assertSame('Something New', $fixture[0]->getNbrplaces());
        self::assertSame('Something New', $fixture[0]->getLink());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Formation();
        $fixture->setTitre('Value');
        $fixture->setDescription('Value');
        $fixture->setDatedebut('Value');
        $fixture->setDatefin('Value');
        $fixture->setNbrplaces('Value');
        $fixture->setLink('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/formation/');
        self::assertSame(0, $this->formationRepository->count([]));
    }
}
