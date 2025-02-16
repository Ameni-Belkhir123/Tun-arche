<?php

namespace App\Tests\Controller;

use App\Entity\Evaluation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EvaluationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $evaluationRepository;
    private string $path = '/evaluation/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->evaluationRepository = $this->manager->getRepository(Evaluation::class);

        foreach ($this->evaluationRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Evaluation index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'evaluation[commentaire]' => 'Testing',
            'evaluation[note]' => 'Testing',
            'evaluation[formation]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->evaluationRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Evaluation();
        $fixture->setCommentaire('My Title');
        $fixture->setNote('My Title');
        $fixture->setFormation('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Evaluation');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Evaluation();
        $fixture->setCommentaire('Value');
        $fixture->setNote('Value');
        $fixture->setFormation('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'evaluation[commentaire]' => 'Something New',
            'evaluation[note]' => 'Something New',
            'evaluation[formation]' => 'Something New',
        ]);

        self::assertResponseRedirects('/evaluation/');

        $fixture = $this->evaluationRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getCommentaire());
        self::assertSame('Something New', $fixture[0]->getNote());
        self::assertSame('Something New', $fixture[0]->getFormation());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Evaluation();
        $fixture->setCommentaire('Value');
        $fixture->setNote('Value');
        $fixture->setFormation('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/evaluation/');
        self::assertSame(0, $this->evaluationRepository->count([]));
    }
}
