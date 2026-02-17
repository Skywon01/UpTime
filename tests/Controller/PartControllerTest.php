<?php

namespace App\Tests\Controller;

use App\Entity\Part;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PartControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $partRepository;
    private string $path = '/part/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->partRepository = $this->manager->getRepository(Part::class);

        foreach ($this->partRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Part index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'part[reference]' => 'Testing',
            'part[designation]' => 'Testing',
            'part[price]' => 'Testing',
            'part[stockQuantity]' => 'Testing',
            'part[supplier]' => 'Testing',
            'part[machines]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->partRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Part();
        $fixture->setReference('My Title');
        $fixture->setDesignation('My Title');
        $fixture->setPrice('My Title');
        $fixture->setStockQuantity('My Title');
        $fixture->setSupplier('My Title');
        $fixture->setMachines('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Part');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Part();
        $fixture->setReference('Value');
        $fixture->setDesignation('Value');
        $fixture->setPrice('Value');
        $fixture->setStockQuantity('Value');
        $fixture->setSupplier('Value');
        $fixture->setMachines('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'part[reference]' => 'Something New',
            'part[designation]' => 'Something New',
            'part[price]' => 'Something New',
            'part[stockQuantity]' => 'Something New',
            'part[supplier]' => 'Something New',
            'part[machines]' => 'Something New',
        ]);

        self::assertResponseRedirects('/part/');

        $fixture = $this->partRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getReference());
        self::assertSame('Something New', $fixture[0]->getDesignation());
        self::assertSame('Something New', $fixture[0]->getPrice());
        self::assertSame('Something New', $fixture[0]->getStockQuantity());
        self::assertSame('Something New', $fixture[0]->getSupplier());
        self::assertSame('Something New', $fixture[0]->getMachines());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Part();
        $fixture->setReference('Value');
        $fixture->setDesignation('Value');
        $fixture->setPrice('Value');
        $fixture->setStockQuantity('Value');
        $fixture->setSupplier('Value');
        $fixture->setMachines('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/part/');
        self::assertSame(0, $this->partRepository->count([]));
    }
}
