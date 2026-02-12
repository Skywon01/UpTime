<?php

namespace App\Tests\Controller;

use App\Entity\Machine;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MachineControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $machineRepository;
    private string $path = '/machine/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->machineRepository = $this->manager->getRepository(Machine::class);

        foreach ($this->machineRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Machine index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'machine[name]' => 'Testing',
            'machine[brand]' => 'Testing',
            'machine[serialNumber]' => 'Testing',
            'machine[status]' => 'Testing',
            'machine[company]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->machineRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Machine();
        $fixture->setName('My Title');
        $fixture->setBrand('My Title');
        $fixture->setSerialNumber('My Title');
        $fixture->setStatus('My Title');
        $fixture->setCompany('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Machine');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Machine();
        $fixture->setName('Value');
        $fixture->setBrand('Value');
        $fixture->setSerialNumber('Value');
        $fixture->setStatus('Value');
        $fixture->setCompany('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'machine[name]' => 'Something New',
            'machine[brand]' => 'Something New',
            'machine[serialNumber]' => 'Something New',
            'machine[status]' => 'Something New',
            'machine[company]' => 'Something New',
        ]);

        self::assertResponseRedirects('/machine/');

        $fixture = $this->machineRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getBrand());
        self::assertSame('Something New', $fixture[0]->getSerialNumber());
        self::assertSame('Something New', $fixture[0]->getStatus());
        self::assertSame('Something New', $fixture[0]->getCompany());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Machine();
        $fixture->setName('Value');
        $fixture->setBrand('Value');
        $fixture->setSerialNumber('Value');
        $fixture->setStatus('Value');
        $fixture->setCompany('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/machine/');
        self::assertSame(0, $this->machineRepository->count([]));
    }
}
